@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        <div class="card-header"><h1>Listado de Tipos de IVA</h1></div>

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
              <input type="hidden" name="companyid" value="{{$company}}">
              
            @if (isset($ivas) && count($ivas)>0)               
                <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap dataTable no-footer dtr-inline" 
                       role="grid" aria-describedby="datatable-responsive_info" cellspacing="0">
                        <thead>
                            <tr role="row">
                                <th class="text-left" style="width: 35%;" >Denominación</th>
                                <th class="text-left" style="width: 10%;" >Clase</th>
                                <th class="text-center" style="width: 10%;" >Tipo</th>
                                <th class="text-center" style="width: 10%;" >Vigente</th>
                                <th style="width: 15%;" >Última actualización</th>
                                <th ></th>                                
                            </tr>
                        </thead>              
                        @foreach ($ivas as $iva)                        
                        <tr>
                              <td>{{$iva->iva_name}}</td>
                              
                              @if ($iva->type == 1)
                              <td class="text-left">Superreducido</td>
                              @elseif ($iva->type == 2)
                              <td class="text-left">Reducido</td>
                              @else
                              <td class="text-left">General</td>
                              @endif

                              <td class="text-center">{{$iva->rate}} %</td>
                              
                              @if ($iva->active == 1)
                              <td class="text-center">Si</td>
                              @else
                              <td class="text-center">No</td>
                              @endif
                              
                              <td>{{$iva->updated_at}}</td>
                              
                              <td><button type="submit" class="btn btn-info" formaction="{{url('editIva').'/'.$iva->id}}"
                                title="Pulse para editar este tipo de IVA"><i class="fa fa-calendar"></i> Ver Iva</button></td>
                              <td><button type="submit" class="btn btn-danger" formaction="{{url('deleteIva').'/'.$iva->id}}"
                                onclick="return confirm('¿Seguro que desea eliminar este tipo de IVA?')"
                                title="Pulse para eliminar este tipo de IVA"><i class="fa fa-remove"></i> Eliminar </button></td>                              
                        </tr>               
                        @endforeach                        
                </table>

          @endif
            <div class="justify-content-center">
                <button name="createIva" class="btn btn-info" title="Crear un nuevo tipo de IVA en la base de datos" 
                       formaction="{{url('createIva')}}" > <i class="fa fa-save"></i> Nuevo IVA</button>
            </div>                        
                                  
          </form>
        </div>
    </div>
    @endguest

    {{-- mensajes --}}
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
