<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="{{ ltrim(elixir('/css/pdf.css'), '/') }}" />
</head>
<body>
<div class="container">
    @yield('content')
</div>
</body>
</html>