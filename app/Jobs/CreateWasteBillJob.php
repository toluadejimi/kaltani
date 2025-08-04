<?php

namespace App\Jobs;

use App\Models\UserProperty;
use App\Models\WasteBill;
use App\Models\User;
use App\Notifications\WasteBillCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * @method static dispatch(\Illuminate\Database\Eloquent\HigherOrderBuilderProxy|mixed $id, $id1)
 */
class CreateWasteBillJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $userId;
    public int $propertyId;

    public $tries = 3;
    public $backoff = 30;

    public function __construct(int $userId, int $propertyId)
    {
        $this->userId = $userId;
        $this->propertyId = $propertyId;
    }

    public function handle()
    {
        $property = UserProperty::find($this->propertyId);
        if (! $property) {
            Log::warning("Property {$this->propertyId} not found for user {$this->userId}; skipping.");
            return;
        }

        if (! isset($property->amount)) {
            Log::warning("Property {$this->propertyId} has no amount; skipping for user {$this->userId}.");
            return;
        }

        $existing = WasteBill::where('user_id', $this->userId)
            ->where('property_id', $this->propertyId)
            ->whereYear('due_date', now()->year)
            ->whereMonth('due_date', now()->month)
            ->first();

        if ($existing) {
            Log::info("Bill already exists for user {$this->userId}, property {$this->propertyId} this month; skipping.");
            return;
        }

        // Generate unique ref
        $ref = null;
        for ($i = 0; $i < 5; $i++) {
            $candidate = 'BILL' . str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
            if (! WasteBill::where('ref', $candidate)->exists()) {
                $ref = $candidate;
                break;
            }
        }

        if (! $ref) {
            Log::error("Unable to generate unique ref for user {$this->userId}, property {$this->propertyId}.");
            return;
        }

        try {
            $bill = WasteBill::create([
                'user_id' => $this->userId,
                'property_id' => $this->propertyId,
                'amount' => $property->amount,
                'ref' => $ref,
                'due_date' => now()->endOfMonth(),
            ]);

            $user = User::find($this->userId);
            if (! $user) {
                Log::warning("User {$this->userId} not found after bill creation.");
                return;
            }

            // Notify user (email + SMS)
            $user->notify(new WasteBillCreated($bill, $property));


        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'duplicate')) {
                Log::warning("Ref collision for user {$this->userId}, property {$this->propertyId}: " . $e->getMessage());
            } else {
                throw $e; // allow retry
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("CreateWasteBillJob failed for user {$this->userId}, property {$this->propertyId}: " . $exception->getMessage());
    }
}
