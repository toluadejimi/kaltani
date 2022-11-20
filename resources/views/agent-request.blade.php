@extends('layouts.main')
@section('content')

    <!-- Main Content -->
    <div class="main-content">
        <section class="section">
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
          <div class="section-header">
            <h1>Agent Request</h1>
          </div>
          <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
              <div class="card card-statistic-1">
                <div class="card-icon bg-danger">
                  <i class="fas fa-solid fa-user-tie"></i>
                </div>
                <div class="card-wrap">
                  <div class="card-header">
                    <h4>Pending Request</h4>
                  </div>
                  <div class="card-body">
                    {{$pending_request}}
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-12">
              <div class="card card-statistic-1">
                <div class="card-icon bg-primary">
                  <i class="fas fa-solid fa-user-tie"></i>
                </div>
                <div class="card-wrap">
                  <div class="card-header">
                    <h4>Approved Agent</h4>
                  </div>
                  <div class="card-body">
                    {{$approved_agent}}
                  </div>
                </div>
              </div>
            </div>






          <div class="row">
            <div class="col-lg-12 col-md-12 col-12 col-sm-12">
              <div class="card">
                <div class="card-header">
                  <h4>Latest Request</h4>

                </div>
                <div class="card-body p-0">
                    <input type="text" id="mytable" class="form-control col-4 mb-5" data-table="table" placeholder="Search" />

                  <div class="table-responsive">
                    <table id="mytable" class="table table-striped mb-0">
                      <thead>
                        <tr>
                          <th>Customer Name</th>
                          <th>Agent</th>
                          <th>Address</th>
                          <th>State</th>
                          <th>Lga</th>
                          <th>City</th>
                           <th>Phone</th>
                           <th>Image</th>
                          <th>Status</th>
                          <th>Date</th>
                          <th>Time</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="geeks">
                        @forelse ($agent_list as $item)
                            <td>{{$item->customer_name}}</td>
                            <td>{{$item->org_name}}</td>
                            <td>{{$item->address}}</td>
                            <td>{{$item->state}}</td>
                            <td>{{$item->lga }}</td>
                            <td>{{$item->city}}</td>
                            <td>{{$item->phone}}</td>
                            <td>
                            <img src="{{ url('public/upload/agent/'.$item->image)}}"  width="100px" height="100px"/>
                            </td>
                            @if($item->status =='0')
                             <td><span class="badge rounded-pill bg-warning text-dark">Pending</span></td>
                             @else
                             <td><span class="badge rounded-pill bg-success">Completed</span></td>
                             @endif
                            <td>{{date('F d, Y', strtotime($item->created_at))}}</td>
                            <td>{{date('h:i:s A', strtotime($item->created_at))}}</td>
                            <td>
                                <div class="row mr-6">
                                <form class="mr-3" action="/agent-delete/{{$item->id}}" method="post>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"><i
                                            class="fa-light fa-trash-can"></i></button>
                                </form>

                                <form action="/agent_request_update?id={{$item->id}}" method="post">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn btn-success"><i
                                            class="fa-light fa-check-circle"></i></button>
                                </form>

                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr colspan="20" class="text-center">
                              <td colspan="20">No Record Found</td>
                            </tr>
                        @endforelse


                    </tbody>
                    </table>
                    {!! $agent_list->appends(Request::all())->links() !!}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>


    <!-- Main Container end -->


@endsection
