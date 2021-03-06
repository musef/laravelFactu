@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        @if (isset($company->id) && $company->id > 0)
            <div class="card-header"><h1>Datos de la empresa</h1></div>
        @else
            <div class="card-header"><h1>Creación de nueva empresa</h1></div>
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
                @if (isset($company->id) && $company->id > 0)
                <p>En este formulario se puede editar/modificar los datos de la empresa</p>
                @else
                <p>Para crear una nueva empresa rellene los datos y pulse en crear</p>
                @endif
              <br>
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf

                <input type="hidden" name="companyid" value="{{$company->id}}">

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Nombre</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='companyname' class="form-control" type="text" value="{{$company->company_name}}" 
                             required minlength="3" maxlength="100" pattern="[\ a-zA-z0-9ñÑ&.,-]{3,100}" 
                             title="Longitud entre 3 y 100. Admite solamente letras y números, símbolos (&, . - ,)">
                  </div>
                </div>

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">N.I.F.</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='companynif' class="form-control" type="text" value="{{$company->company_nif}}" 
                             required minlength="9" maxlength="9" pattern="[\ a-zA-z0-9ñÑ]{9}" 
                             title="Longitud de 9. Admite solamente letras y números">
                  </div>
                </div>

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Dirección</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='companyaddress' class="form-control" type="text" value="{{$company->company_address}}" 
                             required minlength="3" maxlength="255" pattern="[\ a-zA-z0-9ñÑ&.,-ªº]{3,255}" 
                             title="Longitud entre 3 y 100. Admite solamente letras y números, símbolos (&, . - , º ª)">
                  </div>
                </div>                
                     
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Localidad</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='companycity' class="form-control" type="text" value="{{$company->company_city}}" 
                             required minlength="3" maxlength="100" pattern="[\ a-zA-z0-9ñÑ]{3,100}" 
                             title="Longitud entre 3 y 100. Admite solamente letras y números">
                  </div>
                </div>

                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Cód.Postal</label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                      <input name='companyzip' class="form-control" type="text" value="{{$company->company_zip}}" 
                             required minlength="5" maxlength="5" pattern="[0-9]{5}" 
                             title="Longitud entre 3 y 100. Admite solamente números">
                  </div>
                </div>                  
                
                <div class="form-group col-md-10 col-sm-10 col-xs-12">
                  <label class="control-label col-md-3 col-sm-3 col-xs-12">Última actualizacion
                  </label>
                  <div class="col-md-9 col-sm-9 col-xs-12">
                    <input name='companyupdate' class="date-picker form-control col-md-7 col-xs-12" readonly="readonly" type="text" value="{{$company->updated_at}}">
                  </div>
                </div>
                <div class="ln_solid"></div>
                <div class="form-group">
                    <div class="col-md-8 col-sm-8 col-xs-12 text-center">

                        <button class="btn btn-primary" type="reset"><i class="fa fa-eraser"></i> Borrar</button>
                        @if (isset($company->id) && $company->id > 0)
                        <button type="submit" class="btn btn-success"  formaction="{{url('changeCompanyProfile')}}" 
                                onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Modificar</button>
                        @else
                        <button type="submit" class="btn btn-success"  formaction="{{url('recordNewCompany')}}" 
                                onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Crear</button>
                        @endif

                    </div>
                    <div class="col-md-4 col-sm-4 col-xs-12 text-left">

                        @if (isset($config->createCompanies) && $config->createCompanies == 'Si')
                        <button type="submit" class="btn btn-success"  formaction="{{url('createCompanyMenu')}}" 
                            title="Crear una nueva empresa"><i class="fa fa-user"></i> Crear empresa</button>
                        @else
                        <button type="submit" class="btn btn-success" title="Deshabilitado en configuración"
                           disabled ><i class="fa fa-exclamation-circle"></i> Crear empresa</button>
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
