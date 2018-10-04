<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <script src="{{ asset('js/jquery.min.js') }}"></script>
        <script src="{{ asset('js/intro.js') }}"></script>        
        
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .full-height {
                height: 100vh;
            }

            .flex-center {
                align-items: center;
                display: flex;
                justify-content: center;
            }

            .position-ref {
                position: relative;
            }

            .top-right {
                position: absolute;
                right: 10px;
                top: 18px;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

            .links > a {
                color: #636b6f;
                padding: 0 25px;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: .1rem;
                text-decoration: none;
                text-transform: uppercase;
            }

            .m-b-md {
                margin-bottom: 30px;
            }
            a{
                 text-decoration: none;
                 color: #636b6f;
            }
        </style>
    </head>
    <body onload="initiate()">
        <div class="flex-center position-ref full-height" style="border: 2px solid black;margin: 4px 4px 4px 4px">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
                    @else
                        <a href="{{ url('factulogin') }}">Login</a>
                    @endauth
                </div>
            @else
            <div class="top-right links">
                <a href="{{ url('factulogin') }}">Login</a> 
            </div>
               
            @endif

                <div class="content">
                    <a href="{{ url('factulogin') }}" >
                    <div class="title m-b-md">
                        <div id="F"></div>
                        <div id="M"></div>
                        <div id="S"></div>
                        <div id="factu"></div>
                    </div>
                    </a>
                </div>                

        </div>
        
    </body>
</html>
