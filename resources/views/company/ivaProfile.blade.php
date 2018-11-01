@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        @if (isset($iva->id) && $iva->id > 0)
            <div class="card-header"><h1>Modificación de IVA</h1></div>
        @else
            <div class="card-header"><h1>Creación de nuevo IVA</h1></div>
        @endif


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
                @if (isset($iva->id) && $iva->id > 0)
                <p>En este formulario se puede editar/modificar/eliminar los datos del tipo de IVA.</p>
                <p>No se podrá eliminar el tipo de IVA si ha sido utilizado en operaciones de albarán o 
                de facturación. Tampoco podrá modificarse el porcentaje de IVA si ha sido empleado en 
                operaciones de facturación.</p>
                @else
                <p>Para crear un nuevo tipo de IVA rellene los datos y pulse en crear</p>
                @endif
              <br>
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf

                <input type="hidden" name="ivaid" value="{{$iva->id}}">
                <input type="hidden" name="companyid" value="{{$company}}">

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Concepto</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='ivaname' class="form-control" type="text" value="{{$iva->iva_name}}" 
                             required minlength="3" maxlength="100" pattern="[\ a-zA-z0-9ñÑ&.,-]{3,100}" 
                             title="Longitud entre 3 y 100. Admite solamente letras y números, símbolos (&, . - ,)">
                  </div>
                </div>

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Porcentaje</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='ivarate' class="form-control" type="text" value="{{$iva->rate}}" 
                             required minlength="1" maxlength="5" pattern="[0-9]{1-2}.[0-9]{2}" placeholder="0.00"
                             title="Entre 0.00 y 99.99 Admite solamente números">
                  </div>
                </div>

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Tipo</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                  <select name="ivatype" class="form-control">                      
                    @if ($iva->type == 3)
                      <option value="0">Exento</option>
                      <option value="1">Superreducido</option>
                      <option value="2">Reducido</option>
                      <option value="3" selected>General</option>
                    @elseif ($iva->type == 2)
                      <option value="0">Exento</option>
                      <option value="1">Superreducido</option>
                      <option value="2" selected>Reducido</option>
                      <option value="3">General</option>
                    @elseif ($iva->type == 1)
                      <option value="0">Exento</option>
                      <option value="1" selected>Superreducido</option>
                      <option value="2">Reducido</option>
                      <option value="3">General</option>
                    @else
                      <option value="0" selected>Exento</option>
                      <option value="1">Superreducido</option>
                      <option value="2">Reducido</option>
                      <option value="3">General</option>
                    @endif
                  </select>
                  </div>
                </div>                
                     
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Vigente</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      @if ($iva->active==1)
                        <input type="checkbox" name="ivaactive" value="ON" checked="checked" />
                      @else
                        <input type="checkbox" name="ivaactive" value="OFF" />
                      @endif

                  </div>
                </div>                
                
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Última actualizacion</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input name='ivaupdate' class="form-control" readonly="readonly" type="text" value="{{$iva->updated_at}}">
                  </div>
                </div>
                
               
                <div class="form-group">
                  <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">

                    <button class="btn btn-primary" type="reset" 
                        title="Pulse para borrar los nuevos datos introducidos en formulario"><i class="fa fa-eraser"></i> Borrar</button>
                    @if (isset($iva->id) && $iva->id > 0)
                    <button type="submit" class="btn btn-success"  formaction="{{url('changeIva')}}"
                        title="Pulse para modificar el tipo de IVA con los datos del formulario"
                        onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Modificar IVA</button>
                    <button type="submit" class="btn btn-danger"  formaction="{{url('deleteIva')}}"
                        title="Pulse para eliminar el tipo de IVA"
                        onclick="return confirm('¿Seguro que desea eliminar este tipo de IVA?')"><i class="fa fa-remove"></i> Eliminar IVA</button>                        
                    @else
                    <button type="submit" class="btn btn-success"  formaction="{{url('recordNewIva')}}"
                        title="Pulse para crear un nuevo tipo de IVA con los datos del formulario"
                        onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Crear IVA</button>
                    @endif
                  </div>
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
