@extends('layouts.appfactu')

@section('content')

        <div class="col-md-8 col-md-offset-2">
            <div class="card">
                <div class="card-header"><h1>{{ __('Identificación') }}</h1></div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
                        @csrf

                        
                    <div class="x_panel">
                        <div class="x_content" style="display: block;">
                          <br>
                          <form class="form-horizontal form-label-left input_mask" method="post">
                              @csrf

                            <div class="form-group col-md-10 col-sm-10 col-xs-12">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">{{ __('Dirección de email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" 
                                           name="email" value="{{ old('email') }}" title="Por favor, rellene este campo con una dirección de email válida" required autofocus>

                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group col-md-10 col-sm-10 col-xs-12">
                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Contraseña') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" 
                                           title="Por favor, rellene este campo con su contraseña" required>

                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>              
                            
                            
                            <div class="form-group col-md-10 col-sm-10 col-xs-12">
                              <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-4">

                                <div class="col-md-8 ">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                        <label class="form-check-label" for="remember">
                                            {{ __('Recordarme') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-user"></i> 
                                        {{ __('Identificarse') }}
                                    </button>

                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('¿Olvidó la contraseña?') }}
                                    </a>
                                </div>                                    
                                            
                              </div>
                            </div>

                          </form>
                        </div>
                    </div>                        
                        

                    </form>
                </div>
            </div>
        </div>

@endsection
