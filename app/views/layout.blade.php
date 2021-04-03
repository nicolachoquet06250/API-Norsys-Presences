<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8" />
        <title>@yield('title', $title ?? 'Titre Blade par default')</title>

        @foreach ($scripts as $script)
            <script src="{{ $script }}"></script>
        @endforeach

        @foreach ($styles as $style)
            <link rel="stylesheet" href="{{ $style }}" />
        @endforeach
    </head>

    <body>
        @yield('content')
    </body>
</html>
