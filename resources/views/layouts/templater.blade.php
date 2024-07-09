<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Kashmore') }}</title>

    <!-- Styles  -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> 


    <link href="{{ asset('vendor/components/font-awesome/css/all.min.css') }}" rel="stylesheet">

    
    <link rel="stylesheet" href="{{ url('https://fonts.googleapis.com/css?family=Lato') }}">
    
    <link rel="stylesheet" href="{{ asset('css/styler.css') }}">
    <!-- Scrollbar Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/jquery.mCustomScrollbar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/offline-theme-chrome.css') }}">
    <link rel="stylesheet" href="{{ asset('css/offline-language-english.css') }}">

    <style>
       .my-slides {display:none;}
    </style>
    
  <link rel="stylesheet" href="{{ asset('assets/tether/tether.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap-grid.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap-reboot.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/theme/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/mobirise/css/mbr-additional.css') }}" type="text/css">

    <script src="{{ asset('js/offline.min.js') }}"></script>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
     
    <!-- Font Awesome JS -->
    <script defer src="{{ asset('js/solid.js') }}"></script>
    <script defer src="{{ asset('js/fontawesome.js') }}"></script>

</head>
<body style="font-family:Lato,sans-serif;";>

  <div id="app">
    
  </div>

</body>
</html>
