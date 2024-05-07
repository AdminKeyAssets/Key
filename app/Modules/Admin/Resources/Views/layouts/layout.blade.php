<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="robots" content="noindex, nofollow">
    <link rel="shortcut icon" href="img/favicon.png">
    <base href="{{ URL::to('/') }}/">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('admin.project_name') }}</title>

    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};

    </script>

    @yield('header_before_scripts')

    @include('admin::partials.layout.header_scripts')

    @yield('header_after_scripts')

</head>
<body>

@yield('content')

<script>
    $(function () {
        $('.logout-link').click(function () {
            $('#logout-form').submit();
        });
    });
</script>
@yield('footer_scripts')

<script src="{{ mix('js/admin.js') }}"></script>

</body>
</html>

