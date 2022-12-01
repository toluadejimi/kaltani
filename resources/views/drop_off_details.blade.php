@extends('layouts.main')
@section('content')
    <div class="main-content">
        <div class="card mt-4">
            <div class="row">
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Order ID</h4>
                                <p>{{ $order_id }}</p>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Weight (KG)</h4>
                                <p>{{ $weight }}</p>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">

                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Amount (NGN)</h4>
                                <p>{{ $amount }}</p>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Collection Center</h4>
                                <p>{{ $collection_center }}</p>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Recycler</h4>
                                <p>{{ $customer }}</p>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">

                        <div class="card-wrap mb-3">
                            <div class="card-header">
                                <h4>Status</h4>

                            @if ($status == '0')
                                <td><span class="badge rounded-pill bg-primary text-white">Drop Off Pending</span></td>
                            @elseif ($status == '2')
                                <td><span class="badge rounded-pill bg-warning text-white">Payment Pending</span></td>
                            @elseif ($status == '4')
                                <td><span class="badge rounded-pill bg-danger text-white">Drop Off Rejected</span></td>
                            @else
                                <td><span class="badge rounded-pill bg-success">Drop Off Completed</span></td>
                            @endif
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Agent Image</h4>
                                <div><img src="{{ url('public/upload/agent/' . $agent_image) }}" width="300px"
                                        height="300px" /></div>
                            </div>
                            <div class="card-body">


                            </div>
                        </div>
                    </div>
                </div>

                @if ($reason == null )

                @else

                <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-wrap">
                            <div class="card-header">
                                <h4>Reasons for Rejection</h4>
                                <p>{{ $reason }}</p>
                            </div>
                            <div class="card-body">

                            </div>
                        </div>
                    </div>
                </div>

                @endif



            </div>





            <div class="row">



                @if ($status == '4' )
                <div class="col-2 ml-4">
                    <form action="/dropoffDelete/{{ $id }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mb-4"><i
                                class="fa-light fa-trash-can">Delete</i></button>
                    </form>


                </div>
                @else

                <div class="col-1 ml-4 mr-4">
                    <form action="/dropoffupdate/{{ $id }}" method="get">
                        @csrf
                        @method('get')
                        <button type="submit" class="btn btn-success mb-4">Approve Drop Off</button>
                    </form>
                </div>

                <div class="col-1 mr-4">
                    <button type="button" class="btn btn-warning mr-2"
                        data-toggle="modal" data-target="#exampleModal"
                        data-whatever="{{$id}}"> Reject Drop Off </button>
                </div>

                <div class="col-1">
                    <form action="/dropoffDelete/{{ $id }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger mb-4">Delete Drop off</button>
                    </form>
                </div>






                @endif



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
