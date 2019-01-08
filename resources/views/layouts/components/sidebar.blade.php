<div class="col-md-3 left_col">
	<div class="left_col scroll-view">
		<div class="navbar nav_title" style="border: 0">
                    <a class="navbar-brand" href="{{ url('/factulogin') }}" style="font-size: 17px;">                        
                        <img src="{{ asset('/img/logo.png') }}" style='float: left;' class="img-responsive" width="30%" alt="logo Fmsfactu">
                    <span class='gda_title'>{{ config('app.name', 'Fmsfactu') }}</span>
                </a>
		</div>

		<div class="clearfix"></div>
        @guest
            @else
  
		<!-- menu profile quick info -->
                <br />
                <br />
		<div class="profile clearfix">
			<div class="profile_info">
				<span>{{ __('common.welcome') }},</span>                               
				<h2>{{Auth::guard('')->user()->name }}</h2>
			</div>
		</div>

		<br />
                <!-- sidebar menu -->
                {{-- Algunas opciones de menú solo están disponibles para usuarios administradores --}}

                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                  <div class="menu_section">
                    <h3>Menú de opciones</h3>
                    <ul class="nav side-menu">
                      <li><a><i class="fa fa-user"></i> Perfil <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/userProfile')}}">Datos de usuario</a></li>
                        </ul>                      
                      </li>
                      <li><a><i class="fa fa-industry"></i> Empresa <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/companyProfile')}}">Ver datos</a></li>
                          <li><a href="{{url('/paymentMethods')}}">Formas pago</a></li>
                          <li><a href="{{url('/ivaTypes')}}">Tipos de IVA</a></li>
                          <li><a href="{{url('/companySettings')}}">Configuración</a></li>                          
                        </ul>
                      </li>
                      <li><a><i class="fa fa-users"></i> Clientes<span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/showCustomers')}}">Ficha clientes</a></li>    
                          <li><a href="{{url('/customersList')}}">Listado clientes</a></li>                           
                        </ul>
                      </li> 
                      <li><a><i class="fa fa-wrench"></i> Albaranes<span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/work')}}">Generar albarán</a></li>    
                          <li><a href="{{url('/worksList')}}">Listado albaranes</a></li>                           
                        </ul>
                      </li>                           
                      <li><a><i class="fa fa-euro"></i> Facturación <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/invoicesMenu')}}">Emisión facturas</a></li>
                          <li><a href="{{url('/invoicesList')}}">Listado facturas</a></li>
                          <li><a href="{{url('/customerInvoicesList')}}">Facturación clientes</a></li>                           
                        </ul>
                      </li>

                    </ul>
                  </div>
                  <div class="menu_section">
                  </div>

                </div>
                <!-- /sidebar menu -->

        @endguest
	</div>
</div>

