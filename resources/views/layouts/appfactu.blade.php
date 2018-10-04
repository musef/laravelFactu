<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/font-awesome.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/animate.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/gentelella.min.css') }}" rel="stylesheet">      
        
        <script src="{{ asset('js/jquery.min.js') }}"></script>
    </head>
    <body class="nav-md">
        <div class="container body">
            <div class="main_container">
                @include('layouts.components.sidebar')

                @include('layouts.components.topnav')

                <!-- page content -->
                <div class="right_col" role="main">
                    <div class="">

                        <div class="clearfix"></div>
                        
                        <div class="row">
                            @yield('content')
                        </div>

                    </div>
                </div>
                @include('layouts.components.footer')
            </div>
        </div>

        <!-- Scripts -->
        
        <script src="{{ asset('js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('js/gentelella.js') }}"></script>
        
    </body>
</html>