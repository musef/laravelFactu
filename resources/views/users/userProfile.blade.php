@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        @if (isset($user->id) && $user->id > 0)
            <div class="card-header"><h1>Perfil del usuario</h1></div>
        @else
            <div class="card-header"><h1>Creación de nuevo usuario</h1></div>
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
                @if (isset($user->id) && $user->id > 0)
                <p>En este formulario se puede editar/modificar el perfil del usuario</p>
                @else
                <p>Para crear un nuevo usuario rellene los datos y pulse en crear</p>
                @endif
              <br>
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf

                <input type="hidden" name="userid" value="{{$user->id}}">

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Nombre</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='username' class="form-control" type="text" value="{{$user->name}}" 
                             required minlength="3" maxlength="100" pattern="[\ a-zA-z0-9ñÑ]{3,100}" 
                             title="Longitud entre 3 y 100. Admite solamente letras y números">
                  </div>
                </div>
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Email </label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='useremail' class="form-control" type="email" value="{{$user->email}}" 
                             required maxlength="255" title="Introduzca un email con formato correcto">
                  </div>
                </div>
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Password </label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='userpass' class="form-control" type="password" value="{{$user->password}}" 
                             required minlength="8" maxlength="15"
                             title="Introduzca una contraseña entre 8 y 15. Admite solamente letras y números">
                  </div>
                </div>                      
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Última actualizacion
                  </label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input name='userupdate' class="date-picker form-control col-md-7 col-xs-12" readonly="readonly" type="text" value="{{$user->updated_at}}">
                  </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">

                        <button class="btn btn-primary" type="reset"><i class="fa fa-eraser"></i> Borrar</button>
                        @if (isset($user->id) && $user->id > 0)
                        <button type="submit" class="btn btn-success"  formaction="{{url('changeUserProfile')}}" 
                                onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Modificar</button>
                        @else
                        <button type="submit" class="btn btn-success"  formaction="{{url('recordNewUser')}}" 
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
