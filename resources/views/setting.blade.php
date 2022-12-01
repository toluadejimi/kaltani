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
                    <h4> Settings</h4>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-warning">
                            <i class="fas fa-duotone fa-truck-fast"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Transfer Fee</h4>
                            </div>

                            <div class="card-body">
                                {{$transfer_fee}}
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
                                <h4>Agent Price Per KG</h4>
                            </div>
                            <div class="card-body">
                                {{$agent_price_per_kg}}
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
                                <h4>Customer Price Per KG</h4>
                            </div>
                            <div class="card-body">
                                {{$customer_price_per_kg}}
                            </div>
                        </div>
                    </div>
                </div>


            </div>

        </div>


        <div class="card mt-4">

            <div class="row">
                <div class="card-header">
                    <h4>Update Settings</h4>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-5 ml-4">

                    <form action="/customer-charge-per-kg" method="GET">
                        @csrf

                        <label> Customer Price Per KG </label>
                        <input type="number" name="price_per_kg" class="form-control" autofocus value="{{ $customer_price_per_kg }}" />

                        <button type="submit" class="btn btn-primary mt-3">Update</button>
                    </form>

                </div>


                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-5 ml-4">

                    <form action="/agent-charge-per-kg" method="GET">
                        @csrf

                        <label> Agent Price Per KG </label>
                        <input type="number" name="price_per_kg" class="form-control" autofocus value="{{ $agent_price_per_kg }}" />

                        <button type="submit" class="btn btn-primary mt-3">Update</button>
                    </form>

                </div>


                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-5">

                    <form action="/transfer-fee" method="GET">
                        @csrf

                        <label> Transfer Fee</label>
                        <input type="number" name="transfer_fee" class="form-control" autofocus value="{{ $transfer_fee }}" />

                        <button type="submit" class="btn btn-primary mt-3 mb-6">Update</button>
                    </form>

                </div>




            </div>

        </div>





    </div>
    </div>
@endsection
