<!DOCTYPE html>
<html lang="en">
  @include('layouts.head')

  <body>
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
      <div class="w-100" style="max-width:560px;">
        @yield('content')
      </div>
    </div>

    @include('layouts.script')
  </body>
</html>
