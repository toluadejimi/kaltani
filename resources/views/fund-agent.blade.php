@extends('layouts.main')
@section('content')
    <div class="main-content">
        <div class="card mt-4 p-4">
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
                <div class="col-md-12 p-2">
                    <div class="card-header">
                        <h4>Fund Agent</h4>
                    </div>

                    <div class="card card-statistic-1">
                        <div class="card-icon bg-danger">
                            <i class="fas fa-solid fa-money-bill-transfer"></i>
                        </div>
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Total Money Out</h4>
                            </div>
                            <div class="card-body">
                                {{ number_format($agentfunds, 2) }}
                            </div>
                        </div>
                    </div>







                    <form action="/fund-agent-now" method="post" class="mb-4 p-2">
                        @csrf
                        <div class="row d-flex">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="">Select Agent</label>


                                    <select class="form-control selectpicker" name="user_id"
                                        id=""data-live-search="true" class="form-control">
                                        @foreach ($agents as $agent)
                                            <option value="{{ $agent->user_id }}">{{ $agent->org_name }}  -  Total Weight ({{ $agent->total_weight }} KG)
                                                </option>
                                        @endforeach
                                    </select>



                                </div>
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Weight (KG)</label>
                                    <input type="number" id="weight" name="weight" class="form-control" required>
                                </div>
                            </div>


                            <div class="mb-3">
                                <input type="number" hidden class="form-control" name="rate" id="rate" value="{{$rate}}" />
                            </div>


                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Amount (NGN)</label>
                                    <input type="number" name="amount" id="result" class="form-control" value="result">
                                </div>
                            </div>


                        </div>




                        <div class="col-md-2">
                            <input type="submit" value="Send Money" class="btn btn-primary">
                        </div>
                    </form>
                </div>
            </div>
        </div>






        <div class="row">
            <div class="col-md-12 shadow-sm table-responsive">
                <table id="myTable" class="table table-striped mb-0">
                    <input type="text" id="mytable" class="form-control col-4 mb-5" data-table="table" placeholder="Search" />

                    <thead>
                        <tr>
                            <th>Agent Name</th>
                            <th>Amount</th>
                            <th>Weight Deducted (KG)</th>
                            <th>Staff</th>
                            <th>Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="geeks">
                        @forelse ($fund_transaction as $item)
                            <tr>
                                <td>{{ $item->org_name }}</td>
                                <td>{{ $item->amount }}</td>
                                <td>{{ $item->weight }}</td>
                                <td>{{ $item->staff_id }}</td>
                                <td>{{ date('F d, Y', strtotime($item->created_at)) }}</td>
                                <td>{{ date('h:i:s A', strtotime($item->created_at)) }}</td>


                            </tr>
                        @empty
                            <tr colspan="20" class="text-center">
                                <td colspan="20">No Record Found</td>
                            </tr>
                        @endforelse


                    </tbody>
                </table>
            </div>

        </div>





        <script>
            $(function() {
                $('.selectpicker').selectpicker();
            });
        </script>


<script>
    $('input').keyup(function() { // run anytime the value changes
        var rate = Number($('#rate').val()); // get value of field
        var weight = Number($('#weight').val()); // convert it to a float

        document.getElementById('result').value = rate * weight;
        // add them and output it
    });
</script>
    @endsection
