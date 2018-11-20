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
              
              @if (isset($settings) && count($settings)>0 )
              <form class="form-horizontal form-label-left input_mask" method="post">
                  @csrf

                <input type="hidden" name="companyid" value="{{Auth::guard('')->user()->idcompany}}">

                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <hr>
                <h2>Settings de perfil</h2>
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Crear usuarios</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <select class="form-control" name="createUsers" disabled>
                              @if ($settings['createUsers']=='No')
                              <option value="No" selected>Deshabilitado</option>                              
                              <option value="Si">Habilitado</option>
                              @else
                              <option value="No">Deshabilitado</option>                              
                              <option value="Si" selected>Habilitado</option>                              
                              @endif
                          </select>
                      </div>
                    </div>
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Roles</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <select class="form-control" name="usingRoles" disabled>
                              @if ($settings['usingRoles']=='No')
                              <option value="No" selected>Deshabilitado</option>                              
                              <option value="Si">Habilitado</option>
                              @else
                              <option value="No">Deshabilitado</option>                              
                              <option value="Si" selected>Habilitado</option>                              
                              @endif
                          </select>
                      </div>
                    </div>
                </div>
 
                <div class="form-group col-md-12 col-sm-12 col-xs-12">
                <hr>
                <h2>Settings de empresa</h2> 
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Modo multiempresa</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <select class="form-control" name="createCompanies" disabled>
                              @if ($settings['createCompanies']=='No')
                              <option value="No" selected>Deshabilitado</option>                              
                              <option value="Si">Habilitado</option>
                              @else
                              <option value="No">Deshabilitado</option>                              
                              <option value="Si" selected>Habilitado</option>                              
                              @endif
                          </select>
                      </div>
                    </div>                
                
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
                          <select class="form-control" name="worksmode" disabled>
                              @if ($settings['worksmode']=='1')
                              <option value="1" selected>Un albarán por cada artículo o trabajo</option>                              
                              <option value="2">Albarán multiartículo</option>                            
                              @else
                              <option value="1">Un albarán por cada artículo o trabajo</option>                              
                              <option value="2" selected>Albarán multiartículo</option>
                              @endif                              
                          </select>
                      </div>
                    </div>
                    
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Prefijo de albaranes</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='workPrefix' class="form-control" type="text" value="{{$settings['workPrefix']}}"
                            maxlength="3" title="El número del albarán comenzará por este valor introducido. Máximo valor es longitud 3" readonly>
                      </div>
                    </div>
                    
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Prefijo núm. albarán</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <select class="form-control" name="worksprefix2" disabled>
                              @if ($settings['worknumPrefix']==1)
                              <option value="1" title="El número del albarán comenzará por el año con 4 cifras, seguido del mes con 2 cifras"
                                    selected  >aaaamm</option>                              
                              <option value="2" title="El número del albarán comenzará por ceros" selected>Ninguno</option>                              
                              @else
                              <option value="1" title="El número del albarán comenzará por el año con 4 cifras, seguido del mes con 2 cifras"
                                      >aaaamm</option>                              
                              <option value="2" title="El número del albarán comenzará por ceros" selected>Ninguno</option>
                              @endif
                          </select>
                      </div>
                    </div>                  
                                     
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Longitud núm. de albaran</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='workslength' class="form-control" type="text" maxlength="2" readonly
                            title='Longitud total del número del albarán, incluyendo prefijos' value="{{$settings['worknumLength']}}" >
                      </div>
                    </div>
                </div>
                
                <div class="form-group col-md-12 col-sm-12 col-xs-12">                
                    <hr>
                    <h2>Settings de facturación</h2>

                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Serie de facturas</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='invoicesSerial' class="form-control" type="text" maxlength="3" 
                            title='Serie alfanúmerica para las facturas (máx longitud 3)' 
                            value="{{$settings['invoiceSerial']}}" readonly>
                      </div>
                    </div> 
                    
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Prefijo núm. factura</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">                          
                          <select class="form-control" name="invoicePrefix" >
                              @if ($settings['invoicePrefix']==1)
                              <option value="1" title="El número de factura comenzará por el año con 4 cifras, seguido del mes con 2 cifras"
                                      selected >aaaamm</option>                              
                              <option value="2" title="El número de factura comenzará por ceros">Ninguno</option>
                              @else
                              <option value="1" title="El número de factura comenzará por el año con 4 cifras, seguido del mes con 2 cifras"
                                      >aaaamm</option>                              
                              <option value="2" title="El número de factura comenzará por ceros" selected >Ninguno</option>
                              @endif
                          </select>
                      </div>
                    </div>                      
                    
                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Longitud núm. de factura</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='invoicesLength' class="form-control" type="text" maxlength="2" 
                            title='Longitud total del número de factura, incluyendo prefijos (entre 12 y 15, por defecto 15)' 
                            value="{{$settings['invoicenumLength']}}" readonly>
                      </div>
                    </div>                    

                    <div class="form-group col-md-10 col-sm-10 col-xs-12">
                      <label class="control-label col-md-3 col-sm-3 col-xs-12">Nota de texto al pie de la factura</label>
                      <div class="col-md-7 col-sm-7 col-xs-12">
                          <input name='invoiceNote' class="form-control" type="text" maxlength="255" value="{{$settings['invoiceNote']}}"
                            title='Nota al pie de la factura, habitualmente los datos registrales de la empresa'>
                      </div>
                    </div> 
                    
                </div>
                
                <div class="form-group col-md-12 col-sm-12 col-xs-12"> 
                    <hr class="col-md-12" style="border-top: 2px solid #999">
                </div>
               
                
                <div class="form-group">
                  <div class="col-md-8 col-sm-8 col-xs-12 col-md-offset-2 text-left">
                        <button type="submit" class="btn btn-success"  formaction="{{url('changeSettings')}}"
                                onclick="return confirm('¿Seguro que desea grabar los datos del formulario?')">
                            <i class="fa fa-save"></i> Modificar</button>
                  </div>
                </div>

              </form>
              @else
              <h2>NO existe configuración para esta empresa.</h2>
              <h2>Por favor, contacte con el soporte técnico para solucionar esta incidencia.</h2>
              @endif
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
