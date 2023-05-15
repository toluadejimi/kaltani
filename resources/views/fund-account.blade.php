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
                <h4>Fund Pool Account</h4>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="card card-statistic-1">
                    <div class="card-wrap ml-4">

                        <form action="/fund-account-now" method="post">
                            @csrf

                            <div class="row">

                                <div class="col-lg-12">
                                <label>Amount to Fund</label>
                                <input type="number"  name="amount"  class="form-control" required placeholder="Enter Amount">
                                </div>
    
                                <div class="col-lg-12">
                                    <button type="submit" class="btn btn-primary mt-4 mb-5"> Pay Now</i></button>
                                </div>


                            </div>
                           





                        </form>

                    </div>
                </div>

            </div>

            <div class="col-lg-4 col-md-6 col-sm-6 col-12">

            </div>


            <div class="col-lg-4 col-md-6 col-sm-6 col-12 ">


            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 shadow-sm">
            <table id="myTable" class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accountfunds as $item)
                    <tr>
                        <td>{{$item->trans_id}}</td>
                        <td>{{$item->reference}}</td>
                        <td>{{$item->user->first_name}} {{$item->user->last_name}} </td>
                        <td>NGN {{number_format($item->amount, 2)}}</td>
                        @if($item->type =='Debit')
                        <td><span class="badge rounded-pill bg-warning text-dark">Debit</span></td>
                        @else
                        <td><span class="badge rounded-pill bg-success">Credit</span></td>
                        @endif
                        <td>{{date('F d, Y', strtotime($item->created_at))}}</td>
                        <td>{{date('h:i:s A', strtotime($item->created_at))}}</td>

                    </tr>
                    @empty
                    <tr colspan="20" class="text-center">No Record Found</tr>
                    @endforelse


                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection