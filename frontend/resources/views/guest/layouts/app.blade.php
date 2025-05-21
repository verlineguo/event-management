<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Eventalk - Free Bootstrap 4 Template by Colorlib</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    
    <link href="https://fonts.googleapis.com/css?family=Work+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('guest/css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/animate.css') }}">
    
    <link rel="stylesheet" href="{{ asset('guest/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/magnific-popup.css') }}">

    <link rel="stylesheet" href="{{ asset('guest/css/aos.css') }}">

    <link rel="stylesheet" href="{{ asset('guest/css/ionicons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('guest/css/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/jquery.timepicker.css') }}">

    
    <link rel="stylesheet" href="{{ asset('guest/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('guest/css/style.css') }}">
  </head>
  <body>
    
	  
    @include('guest.layouts.navbar')
    
    @yield('content')
    

    @include('guest.layouts.footer')
    
  

  <!-- loader -->
  <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px"><circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee"/><circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10" stroke="#F96D00"/></svg></div>


  <script src="{{ asset('guest/js/jquery.min.js') }}"></script>
  <script src="{{ asset('guest/js/jquery-migrate-3.0.1.min.js') }}"></script>
  <script src="{{ asset('guest/js/popper.min.js') }}"></script>
  <script src="{{ asset('guest/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('guest/js/jquery.easing.1.3.js') }}"></script>
  <script src="{{ asset('guest/js/jquery.waypoints.min.js') }}"></script>
  <script src="{{ asset('guest/js/jquery.stellar.min.js') }}"></script>
  <script src="{{ asset('guest/js/owl.carousel.min.js') }}"></script>
  <script src="{{ asset('guest/js/jquery.magnific-popup.min.js') }}"></script>
  <script src="{{ asset('guest/js/aos.js') }}"></script>
  <script src="{{ asset('guest/js/jquery.animateNumber.min.js') }}"></script>
  <script src="{{ asset('guest/js/bootstrap-datepicker.js') }}"></script>
  <script src="{{ asset('guest/js/jquery.timepicker.min.js') }}"></script>
  <script src="{{ asset('guest/js/scrollax.min.js') }}"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false') }}"></script>
  <script src="{{ asset('guest/js/google-map.js') }}"></script>
  <script src="{{ asset('guest/js/main.js') }}"></script>
    
  </body>
</html>