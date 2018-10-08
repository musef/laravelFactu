@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header"><h1>Listado de formas de pago</h1></div>

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
              
            @if (isset($pmethods) && !is_null($pmethods))  
              @foreach ($pmethods as $pmethod)              
                <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap dataTable no-footer dtr-inline" role="grid" aria-describedby="datatable-responsive_info" style="width: 100%;" width="100%" cellspacing="0">
                    <input type="hidden" name="userid" value="{{$pmethod->id}}">
                        <thead>
                            <tr role="row">
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width:40%" >Nombre</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Aplazamiento</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 10%;" >Día de pago</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" style="width: 25%;" >Última actualización</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" ></th>                                
                            </tr>
                        </thead>              
                        <tr>
                              <td>{{$pmethod->payment_method}}</td>
                              <td>{{$pmethod->diff}}</td>
                              <td>{{$pmethod->payment_day}}</td>
                              <td>{{$pmethod->updated_at}}</td>
                              <td><button type="submit" class="btn btn-info" formaction="{{url('editPaymentMethod').'/'.$pmethod->id}}"
                                title="Pulse para editar esta forma de pago"><i class="fa fa-calendar"></i> Ver </button></td>
                              <td><button type="submit" class="btn btn-danger" formaction="{{url('deletePaymentMethod').'/'.$pmethod->id}}"
                                onclick="return confirm('¿Seguro que desea eliminar este método de pago?')"                                          
                                title="Pulse para eliminar esta forma de pago"><i class="fa fa-remove"></i> Eliminar </button></td>                              
                        </tr>               
                </table>
            @endforeach
          @endif
            <div class="justify-content-center">
                <button name="createMethod" class="btn btn-info" title="Crear un nuevo usuario en la base de datos" 
                       formaction="{{url('createPaymentMethod')}}" > <i class="fa fa-save"></i> Nueva forma pago</button>                      
            </div>                        
                                  
          </form>
        </div>
    </div>
    @endguest

    <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-2">
        @if (isset($messageOK) && !is_null($messageOK))
            <p class="alert alert-success">{{$messageOK}}</p>
        @elseif (isset($messageWrong) && !is_null($messageWrong))
            <p class="alert alert-alert">{{$messageWrong}}</p>
        @endif        
    </div>      

    </div>
</div>
@endsection
