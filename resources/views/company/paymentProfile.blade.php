@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        @if (isset($method->id) && $method->id > 0)
            <div class="card-header"><h1>Datos de la forma de pago</h1></div>
        @else
            <div class="card-header"><h1>Creación de nueva forma de pago</h1></div>
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
                @if (isset($method->id) && $method->id > 0)
                <p>En este formulario se puede editar/modificar la forma de pago mostrada.</p>
                @else
                <p>Para crear una nueva forma de pago rellene los datos y pulse en crear</p>
                @endif
              <br>
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf

                @if (isset($method->id) && $method->id > 0)
                <input type="hidden" name="methodid" value="{{$method->id}}">
                @else
                <input type="hidden" name="methodid" value="0">                
                @endif
                <input type="hidden" name="companyid" value="{{Auth::guard('')->user()->idcompany}}">
                
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Denominación del pago</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='methodname' class="form-control" type="text" value="{{$method->payment_method}}" 
                             required minlength="3" maxlength="200" pattern="[\ a-zA-z0-9ñÑ&.,-]{3,200}" 
                             title="Longitud entre 3 y 200. Admite solamente letras y números, símbolos (&, . - ,)">
                  </div>
                </div>

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Días de aplazamiento</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='methoddiff' class="form-control" type="text" value="{{$method->diff}}" 
                             required minlength="1" maxlength="3" pattern="[\ 0-9]{1-3}" 
                             title="Admite solamente números (cero para pagos contado). De 0 a 999 días">
                  </div>
                </div>

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Día de pago</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='methodday' class="form-control" type="text" value="{{$method->payment_day}}" 
                             required minlength="1" maxlength="2" pattern="[0-9]{1-2}" 
                             title="Longitud máx. 2. Admite solamente números. Cero si no hay día de pago">
                  </div>
                </div>                                                     
                
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Última actualizacion
                  </label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input name='methodupdate' class="date-picker form-control col-md-7 col-xs-12" readonly="readonly" type="text" value="{{$method->updated_at}}">
                  </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">

                        <button class="btn btn-primary" type="reset"><i class="fa fa-eraser"></i> Borrar</button>
                        @if (isset($method->id) && $method->id > 0)
                        <button type="submit" class="btn btn-success"  formaction="{{url('changePaymentMethod')}}" 
                                onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Modificar</button>
                        @else
                        <button type="submit" class="btn btn-success"  formaction="{{url('recordNewPaymentMethod')}}" 
                                onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Crear</button>
                        @endif


                  </div>
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
