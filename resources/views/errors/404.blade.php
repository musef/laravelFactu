@extends('layouts.appfactu')

@section('content')

        <div class="col-md-8 col-md-offset-2">
            <div class="card">
                <div class="card-header"><h1>¡ Error !</h1></div>

                <div class="card-body">


                        
                    <div class="x_panel">
                        <div class="x_content" style="display: block;">
                          <br>
                          <form class="form-horizontal form-label-left input_mask" method="post" action="{{url('login')}}">
                              @csrf
                      
                            <div class="form-group col-md-10 col-sm-10 col-xs-12">
                                <h1>Upss... algo no ha funcionado </h1>
                                <h1>La página solicitada no existe  :( </h1>
                            </div>

                          </form>
                        </div>
                    </div>                        
                        

                </div>
            </div>
        </div>

@endsection
