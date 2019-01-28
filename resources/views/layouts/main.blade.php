<!doctype html>
<html>
<head>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-KLSH6R6');</script>
<!-- End Google Tag Manager -->

    @php
        $schoolTheme = app('SchoolTheme');
        $icons = $schoolTheme->getSchoolIcons();
    @endphp
    <!-- Metadata -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Cache-Control" content="no-cache">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ Auth::user() ? Auth::user()->id : "" }}">
    <title>{{$header['pageTitle'] ?? $schoolTheme->getSchoolName()}}</title>
    <meta name="viewport" content="width=device-width">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="SHORTCUT ICON" href="{{$icons['ico32x32']}}" type="image/x-icon">
    <link rel="icon" sizes="16x16 32x32" href="{{$icons['ico32x32']}}">
    <link rel="icon" sizes="96x96 128x128" href="{{$icons['ico128x128']}}">
    <link rel="icon" sizes="192x192" href="{{$icons['ico192x192']}}">
    <link rel="apple-touch-icon" sizes="128x128" href="{{$icons['ico128x128']}}">

    <meta property="og:image" content="{{$schoolTheme->getSchoolFacebookImage()}}" />
    
    <!--
    <link href="//fonts.googleapis.com/css?family=Montserrat:300,400,600,700&subset=cyrillic" rel="stylesheet">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.min.css" rel="stylesheet">
    -->

    <link href="{{ URL::asset('css/vendor/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('css/vendor.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('css/app.css') }}" rel="stylesheet" type="text/css"/>
    <link href="{{ URL::asset('css/ql.css') }}" rel="stylesheet" type="text/css"/>

    {{-- склеиваем в app.css
    <link rel="stylesheet" href="/css/bootstrap-popover-x.css">
    <link rel="stylesheet" href="/css/bootstrap-datepicker3.css">
    --}}

    <link href="/style.css" rel="stylesheet" type="text/css" />

    <!--
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    -->
    
    @routes
    
    <link rel="manifest" href="/sp-push-manifest.json" />
    <script charset="UTF-8" src="/sp-push-worker.js" async></script>
    <script charset="UTF-8" src="//cdn.sendpulse.com/js/push/d741439bcc80f1cc598d64ec3a72416e_1.js" async></script>
</head>
<body class="@stack('bodyClass') @if(isset($adaptive) && $adaptive) full-adapt @endif">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-KLSH6R6"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

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

    <script src="/js/jquery.min.js"></script>
    <script src="/js/popper.min.js"></script>
    <script src="/js/bootstrap.min.js"></script>

    @if(config('messenger.enable'))
        <script src="/js/socket.io.js"></script>
    @endif

    <script type="text/javascript" src="{{ URL::asset('js/app.js') }}?t=102"></script>

    <script src="/js/jquery.jscrollpane.min.js"></script>
    <script src="/js/jquery.mousewheel.js"></script>

    <script src="/js/bootstrap-popover-x.js"></script>

    <link href="/css/fix.css" rel="stylesheet" type="text/css" />
    <link href="/css/hot_fix.css" rel="stylesheet" type="text/css" />

    <script src="/js/init.js"></script>
    @if (env('APP_ENV')=='local' && Auth::check())
        <div>Test passed</div>
    @endif
    
</body>
</html>
