@extends('layouts.app_backoffice')

@section('content')
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h1>Listado de usuarios</h1></div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

 
    @guest
    <div>
        <h1> Usuario no logueado. ACCESO NO AUTORIZADO</h1>
    </div>
    @else
    <div class="x_panel">
        <div class="x_content" style="display: block;">
          <br>
          <form class="form-horizontal form-label-left input_mask" method="post">
              @csrf
              
            @if (isset($users) && !is_null($users))  
              @foreach ($users as $user)              
                <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap dataTable no-footer dtr-inline" role="grid" aria-describedby="datatable-responsive_info" style="width: 100%;" width="100%" cellspacing="0">
                    <input type="hidden" name="userid" value="{{$user->id}}">
                        <thead>
                            <tr role="row">
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" rowspan="1" colspan="1" style="width:25%" >Name</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" rowspan="1" colspan="1" style="width: 30%;" >Email</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" rowspan="1" colspan="1" style="width: 10%;" >Role</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" rowspan="1" colspan="1" style="width: 25%;" >Última actualización</th>
                                <th class="sorting" tabindex="0" aria-controls="datatable-responsive" rowspan="1" colspan="1" ></th>                                
                            </tr>
                        </thead>              
                        <tr>
                              <td>{{$user->name}}</td>
                              <td>{{$user->email}}</td>
                              <td>{{$user->user_role}}</td>
                              <td>{{converterDateTime($user->updated_at)}}</td>
                              <td><button type="submit" class="btn btn-info" formaction="{{url('editUser').'/'.$user->id}}"><i class="fa fa-user"></i> Ver </button></td>                                          
                        </tr>               
                </table>
            @endforeach
          @endif
              <div class="justify-content-center">


               <button name="createUser" class="btn btn-info" title="Crear un nuevo usuario en la base de datos" 
                      formaction="{{url('createUser')}}" > <i class="fa fa-save"></i> Crear nuevo usuario</button>
                      
              </div>                        
                                  
          </form>
        </div>
    </div>
    @endguest
                    
                </div>
            </div>
        </div>
@endsection
