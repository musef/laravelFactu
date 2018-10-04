<div class="col-md-3 left_col">
	<div class="left_col scroll-view">
		<div class="navbar nav_title" style="border: 0">
                    <a class="navbar-brand" href="{{ url('/home') }}" style="font-size: 17px;">
                        
                    <img src="{{ asset('assets/img/layout/logos/logo-scroll.png') }}" style='float: left;' class="img-responsive" alt="logo Fmsfactu">
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
                          <li><a href="{{url('/menuUploadFile')}}">Ver datos</a></li>
                          <li><a href="{{url('/menuUploadFile')}}">Formas pago</a></li>
                        </ul>
                      </li>
                      <li><a><i class="fa fa-users"></i> Clientes<span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/checkingUploadedVoFiles')}}">Menú clientes</a></li>    
                          <li><a href="{{url('/checkPublishedCars/vo')}}">Listados</a></li>                           
                        </ul>
                      </li> 
                      <li><a><i class="fa fa-wrench"></i> Trabajos<span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/checkingUploadedVnFiles')}}">Albarán</a></li>    
                          <li><a href="{{url('/checkPublishedCars/vn')}}">Listados</a></li>                           
                        </ul>
                      </li>                           
                      <li><a><i class="fa fa-euro"></i> Facturación <span class="fa fa-chevron-down"></span></a>
                        <ul class="nav child_menu">
                          <li><a href="{{url('/pendingMessages')}}">Facturar</a></li>
                          <li><a href="{{url('/answeredMessages')}}">Listados</a></li>                      
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

