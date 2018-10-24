@extends('layouts.appfactu')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-offset-1 col-md-10 col-sm-10 col-xs-12">
            <div class="card">
                <div class="card-header">
                    <h1> {{$companyName}}</h1>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="col-md-12 col-sm-12 col-xs-12">
                        <img class="col-md-10 col-sm-10 col-xs-10" src="{{asset('img/435982-PEA2M1-533.jpg')}}">                        
                    </div>

                    <br />
                    <a class="col-md-offset-6 alignleft" href="https://www.freepik.es/fotos-vectores-gratis/negocios" target="blank">Foto de negocios creado por freepik - www.freepik.es</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
