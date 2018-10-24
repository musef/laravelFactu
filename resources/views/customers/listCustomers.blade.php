@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header"><h1>Listado de clientes</h1></div>

        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif
        </div>
 
    @guest
    <div>
        <h1> Usuario no logueado. ACCESO NO AUTORIZADO</h1>
    </div>
    @else
    <div class="x_panel">
        <div class="x_content" style="display: block;">
          <br>
          <form class="form-horizontal form-label-left input_mask" method="post">
              @csrf
              
            @if (isset($customers) && !is_null($customers))  
              @foreach ($customers as $customer)              
                <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap dataTable no-footer dtr-inline" role="grid" aria-describedby="datatable-responsive_info" style="width: 100%;" width="100%" cellspacing="0">
                    <input type="hidden" name="userid" value="{{$customer->id}}">
                        <thead>
                            <tr role="row">
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width:40%" >Nombre</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Nif</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 15%;" >Localidad</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 25%;" >Última actualización</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" ></th>                                
                            </tr>
                        </thead>              
                        <tr>
                              <td>{{$customer->customer_name}}</td>
                              <td>{{$customer->customer_nif}}</td>
                              <td>{{$customer->customer_city}}</td>
                              <td>{{$customer->updated_at}}</td>
                              <td><button type="submit" class="btn btn-info" formaction="{{url('editCustomer').'/'.$customer->id}}"
                                title="Pulse para editar este cliente"><i class="fa fa-calendar"></i> Ver cliente</button></td>
                              <td><button type="submit" class="btn btn-danger" formaction="{{url('deleteCustomer').'/'.$customer->id}}"
                                onclick="return confirm('¿Seguro que desea eliminar este cliente?')"
                                title="Pulse para eliminar este cliente"><i class="fa fa-remove"></i> Eliminar </button></td>                              
                        </tr>               
                </table>
            @endforeach
          @endif
            <div class="justify-content-center">
                <button name="createCustomer" class="btn btn-info" title="Crear un nuevo cliente en la base de datos" 
                       formaction="{{url('createCustomer')}}" > <i class="fa fa-save"></i> Nuevo cliente</button>                      
            </div>                        
                                  
          </form>
        </div>
    </div>
    @endguest

    <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-2">
        @if (isset($messageOK) && !is_null($messageOK))
            <p class="alert alert-success">{{$messageOK}}</p>
        @elseif (isset($messageWrong) && !is_null($messageWrong))
            <p class="alert alert-danger">{{$messageWrong}}</p>
        @endif        
    </div>      

    </div>
</div>
@endsection
