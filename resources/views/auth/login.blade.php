@extends('layouts.app')

@section('content')
<div ng-view class="view-frame">
        <div class="col-md-12">
          <div class="col-middle">
            <div class="text-center text-center">
              <h1 class="error-number">Error</h1>
              <h2>Lo sentimos, ha habido algún problema con su identificación</h2>
              <p>Debe identificarse de nuevo, ya que no podemos acceder a la página buscada</p>
              <div class="mid_center">
                <h3>Volver</h3>
                
                <form action="/">
                  <div>
                        <input type="submit" class="btn btn-default" value="Ir a inicio" title="Volver a la página de inicio de la web">
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
</div>
@endsection