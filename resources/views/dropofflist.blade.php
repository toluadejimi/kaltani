@extends('layouts.main')
@section('content')
    <div class="main-content">
        <div class="card mt-4">
            @if (session()->has('message'))
                <div class="alert alert-success">
                    {{ session()->get('message') }}
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session()->get('error') }}
                </div>
            @endif
            <div class="row">
                <div class="card-header">
                    <h4> Drop Off List</h4>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-duotone fa-truck-fast"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Pending Drop Off</h4>
                            </div>
                            <div class="card-body">
                                {{ $pending_drop_off }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-success">
                            <i class="fas fa-sharp fa-pallet-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Weight Collected</h4>
                            </div>
                            <div class="card-body">
                                {{ $total_weight }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary">
                            <i class="fas fa-sharp fa-pallet-boxes"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Unpaid</h4>
                            </div>
                            <div class="card-body">
                                {{ $total_unpaid }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-solid fa-money-bill-transfer"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Amount Out</h4>
                            </div>
                            <div class="card-body">
                                {{ number_format($money_out, 2) }}
                            </div>
                        </div>
                    </div>
                </div>


















            </div>

        </div>




        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">

                    </div>
                    <div class="modal-body">
                        <form class="mr-2" action="/dropoffreject" method="get">
                            @csrf
                            <div class="form-group">
                                <label for="recipient-name" class="col-form-label">Order ID</label>
                                <input type="text" readonly class="form-control" name="id" id="recipient-name">
                            </div>

                            <div class="form-group">
                                <label for="message-text" class="col-form-label">Reasons for Decline:</label>
                                <textarea type="text" class="form-control" name="reason" id="message-text"></textarea>
                            </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Continue</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12 shadow-sm">
                <table id="table" class="table table-striped mb-0">
                    <input type="text" id="mytable" class="form-control col-4 mb-5" data-table="table"
                        placeholder="Search" />
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Weight</th>
                            <th>Amount</th>
                            <th>Collection Center</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="geeks">
                        @forelse ($dropofflist as $item)
                            <tr>
                                <td><a href="/drop_off_details/{{ $item->id }}">{{ $item->order_id }}</a></td>
                                <td>{{ $item->weight }}</td>
                                <td>NGN {{ number_format($item->amount, 2) }}</td>
                                <td>{{ $item->collection_center }}</td>
                                <td>{{ $item->customer }}</td>
                                @if ($item->status == '0')
                                    <td><span class="badge rounded-pill bg-primary text-white">Agent Pending</span></td>
                                @elseif ($item->status == '2')
                                    <td><span class="badge rounded-pill bg-warning text-white">Payment Pending</span></td>
                                @elseif ($item->status == '4')
                                    <td><span class="badge rounded-pill bg-danger text-white">Admin Rejected</span></td>
                                @else
                                    <td><span class="badge rounded-pill bg-success">Completed</span></td>
                                @endif

                                <td>{{ date('F d, Y', strtotime($item->created_at)) }}</td>
                                <td>{{ date('h:i:s A', strtotime($item->created_at)) }}</td>

                                <td>
                                    <div class="row mr-6">
                                        <form class="mr-2" action="/dropoffDelete/{{ $item->id }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger"><i
                                                    class="fa-light fa-trash-can"></i></button>
                                        </form>

                                        {{-- <form class="mr-2" action="/dropoffreject/{{ $item->id }}" method="get">
                                            @csrf
                                            @method('GET')
                                            <button type="submit" class="btn btn-warning"><i
                                                    class="fa-light fa-ban"></i></button>
                                        </form> --}}


                                        <button type="button" class="btn btn-warning mr-2"><i class="fa-light fa-ban"
                                                data-toggle="modal" data-target="#exampleModal"
                                                data-whatever="{{ $item->id }}"></button></i>




                                        <form action="/dropoffupdate/{{ $item->id }}" method="get">
                                            @csrf
                                            @method('GET')
                                            <button type="submit" class="btn btn-success"><i
                                                    class="fa-light fa-check-circle"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr colspan="20" class="text-center">No Agent Found</tr>
                        @endforelse


                    </tbody>
                </table>
                {!! $dropofflist->appends(Request::all())->links() !!}

            </div>
        </div>
    </div>
    </div>


    <script>
        $('#exampleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var recipient = button.data('whatever') // Extract info from data-* attributes
            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text('New message to ' + recipient)
            modal.find('.modal-body input').val(recipient)
        })
    </script>
@endsection
