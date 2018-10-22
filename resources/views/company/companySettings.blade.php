@extends('layouts.appfactu')

@section('content')
<div class="col-md-12">
    <div class="card">
        
        <div class="card-header"><h1>Settings de la empresa</h1></div>

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
                <p>En este formulario se puede editar/modificar algunas configuraciones de la aplicación para la empresa</p>
              <br>
              
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf

                <input type="hidden" name="companyid" value="{{$company->id}}">

                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <hr>
                <h2>Settings de perfil</h2>
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Modificar perfil</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='profilmodify' class="form-control" type="text" value="Si" readonly>
                      </div>
                    </div>
                </div>
 
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <hr>
                <h2>Settings de empresa</h2> 
                </div>                
                
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <hr>
                <h2>Settings de clientes</h2> 
                </div>
                               
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                    <hr>
                    <h2>Settings de albaranes</h2>
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Modo albarán</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='worksmode' class="form-control" type="text" value="Un albarán por cada artículo o trabajo" readonly>
                      </div>
                    </div>                

                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Prefijo de albaranes</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='worksprefix' class="form-control" type="text" value="ALB" readonly>
                      </div>
                    </div>
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Longitud del número de albaran</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='workslength' class="form-control" type="text" value="15" readonly>
                      </div>
                    </div>
                </div>
                
                <div class="form-group col-md-12 col-sm-12 col-xs-12">                
                    <hr>
                    <h2>Settings de facturación</h2>
                    <hr class="col-md-12" style="border-top: 2px solid #999">              
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Última actualizacion
                      </label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                        <input name='companyupdate' class="date-picker form-control col-md-7 col-xs-12" readonly="readonly" 
                               type="text" value="{{$company->updated_at}}">
                      </div>
                    </div>
                </div>
                
                <div class="ln_solid"></div>
                <div class="form-group">
                  <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                        <button type="submit" class="btn btn-success"  formaction="{{url('changeCompanyProfile')}}" disabled
                                onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')"><i class="fa fa-save"></i> Modificar</button>
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
