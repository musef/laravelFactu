@extends('layouts.app')

@section('content')
<div ng-view class="view-frame">
        <div class="col-md-12">
          <div class="col-middle">
            <div class="text-center text-center">
              <h1 class="error-number">Error 404</h1>
              <h2>Lo sentimos, pero no podemos encontrar esa página</h2>
              <p>La página que está buscando no existe en nuestro directorio</p>
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