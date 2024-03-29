<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="{{asset("css/fontawesome/all.min.css")}}" rel="stylesheet">
    <link type="text/css" href="{{asset("css/argon.min.css")}}" rel="stylesheet">
    <link type="text/css" href="{{asset("css/app.css")}}" rel="stylesheet">
    @stack('custom-css')
</head>
<body class="position-relative">
@yield("content")
@yield("modal")
<script src="{{asset("js/popper.min.js")}}"></script>
<script src="{{asset("js/jquery-3.2.1.min.js")}}"></script>
<script src="{{asset("js/bootstrap.min.js")}}"></script>
<script src="{{asset("js/argon.js")}}"></script>
<script src="{{asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset("js/app.js")}}"></script>
<script src="{{asset("js/request-util.js")}}"></script>
@stack('custom-js')
<div class="custom-spinnerPage" id="loadingScreen">
    <div class="lds-dual-ring"></div>
</div>
<script>
    function loadingAnimation(isOn) {
        let domLoad = $("#loadingScreen");
        // Prefix display
        domLoad.css("display", "flex").hide();
        if (isOn) {
            domLoad.fadeIn();
        } else {
            domLoad.fadeOut();
        }
    }
</script>
</body>
</html>
