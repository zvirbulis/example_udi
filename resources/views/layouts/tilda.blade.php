<!doctype html>
<html>
<head>
    <!-- Metadata -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- @TODO временное решение, нуужно как-то на клиенте хранить данные пользователя-->
    <meta name="user-id" content="{{ Auth::user() ? Auth::user()->id : "" }}">
    <title>@lang('views/main.title')</title>

    <!--
    <link href="//fonts.googleapis.com/css?family=Montserrat:300,400,600,700&subset=cyrillic" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.min.css" rel="stylesheet">
    -->
    @yield('assets')
    <link href="{{ URL::asset('css/vendor.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('css/app.css') }}" rel="stylesheet" type="text/css"/>

    {{-- склеиваем в app.css
    <link rel="stylesheet" href="/css/bootstrap-popover-x.css">
    <link rel="stylesheet" href="/css/bootstrap-datepicker3.css">
    --}}

    <link href="/style.css" rel="stylesheet" type="text/css" />


    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>


    @routes

</head>
<body class="@if(isset($bodyClass)) {{$bodyClass}}  @endif @if(isset($adaptive) && $adaptive) full-adapt @endif">
    <div id="app" class="wrapper">
        <div class="bg"></div>

        @auth
            @include('layouts.main.include.header')
            @yield('content')
            @include('layouts.main.include.footer')
        @else
            @yield('auth')
        @endauth
    </div>
    @yield('pagescripts')
    <script src="/js/popper.min.js"></script>

    <script src="/js/bootstrap.min.js"></script>

    <script src="/js/socket.io.js"></script>

    <script type="text/javascript" src="{{ URL::asset('js/app.js') }}"></script>

    <script src="/js/jquery.jscrollpane.min.js"></script>
    <script src="/js/jquery.mousewheel.js"></script>

    <script src="/js/bootstrap-popover-x.js"></script>

    <link href="/css/fix.css" rel="stylesheet" type="text/css" />
    <link href="/css/hot_fix.css" rel="stylesheet" type="text/css" />

    <script src="/js/init.js"></script>
</body>
</html>
