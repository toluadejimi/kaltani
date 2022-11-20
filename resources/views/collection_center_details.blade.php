@extends('layouts.main')
@section('content')
<div class="main-content">
    <div class="card mt-4">
      <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
          <div class="card card-statistic-1">
            <div class="card-icon bg-primary">
              <i class="fas fa-solid fa-cart-flatbed-boxes"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4>Materials Available</h4>
              </div>
              <div class="card-body">
                @if($collected =='0')         
                {{number_format($total_weight)}}                    
                @else
                {{number_format($collected) ?? 0 }}                                                                        
                @endif
                
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
          <div class="card card-statistic-1">
            <div class="card-icon bg-danger">
              <i class="fas fa-solid fa-money-bill-transfer"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4>Total Sorted</h4>
              </div>
              <div class="card-body">
                {{number_format($sorted) ?? 0}}
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 col-12">
          <div class="card card-statistic-1">
            <div class="card-icon bg-warning">
              <i class="fas fa-solid fa-users"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4>Total Bailed</h4>
              </div>
              <div class="card-body">
                {{number_format($bailed) ?? 0}}
              </div>
            </div>
          </div>
        </div>
      </div>
      
        
        <div class="row">
            <div class="col-md-12 shadow-sm table-responsive">
                
                    <div class="row p-2">
                        <div class="col-md-8">
                            <p>Latest Collection</p>
                        </div>
                        <div class="col align-self-end">
                            <p class="text-end">Available Stock: {{$collected ?? 0}}KG</p>
                        </div>
                    </div>

                <table id="myTable" class="table table-striped mb-0">
                    <thead>
                        <tr>
                          <th >Weight(Kg)</th>
                            <th >Amount</th>
                            <th >Price Per Kg</th>
                            <th >Transport</th>
                            <th >Loader</th>
                            <th>Others</th>
                            <th >Date</th>
                        </tr>
                    </thead>
                    <tbody>
                      @forelse ($collect as $item)
                        <tr>
                            <td>{{$item->item_weight ?? 0}}</td>
                            <td>{{$item->amount ?? 0}}</td>
                            <td>{{$item->price_per_kg ?? 0}}</td>
                            <td>{{$item->transport ?? 0}}</td>
                            <td>{{$item->loader ?? 0}}</td>
                            <td>{{$item->others ?? 0}}</td>
                            
                            <td>
                              {{$item->created_at->format('D, d M Y ') ?? 0}}
                            </td>
                        </tr>
                        @empty
                        <tr colspan="20" class="text-center">
                            <td colspan="20">
                                No Record Found
                            </td>
                        </tr>
                    @endforelse

                        
                    </tbody>
                </table>
            </div>
        
        </div>
        
          
      
      </div>
    </div>
        
</div>
@endsection
