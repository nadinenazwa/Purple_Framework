<!DOCTYPE html>
<html lang="en">
  
  @include('layouts.head')

  <body>
    <div class="container-scroller">
      
      @include('layouts.navbar')

      <div class="container-fluid page-body-wrapper">
        
        @include('layouts.sidebar')

        <div class="main-panel">
          <div class="content-wrapper">
            
            @yield('content')
            
          </div>
          
          @include('layouts.footer')
          
        </div>
      </div>
    </div>

    @include('layouts.script')
    
  </body>
</html>