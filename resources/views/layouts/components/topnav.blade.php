<!-- top navigation -->
    <div class="top_nav">
        <div class="nav_menu">
                <nav>
                    <div class="nav toggle">
                            <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>
                  
                    <ul class="nav navbar-nav navbar-right">
                        <li class="">
                            @guest
                            @else
                            <a href="javascript:void(0);" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }} <span class="fa fa-angle-down"></span>
                            </a>
                            @endguest
                            <ul class="dropdown-menu dropdown-usermenu pull-right">

                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                      <i class="fa fa-sign-out"></i>  {{ __('Desconectarse') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
        </div>
</div>
<!-- /top navigation -->
