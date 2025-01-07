<!DOCTYPE html>
<html>
    <head>
        <title>{{ config('app.name', 'Kubectyl') }}</title>

        @section('meta')
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <meta name="robots" content="noindex">
            <link rel="apple-touch-icon" sizes="180x180" href="">
            <link rel="icon" type="image/png" href="" sizes="32x32">
            <link rel="icon" type="image/png" href="" sizes="16x16">
            <link rel="manifest" href="/favicons/manifest.json">
            <link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#bc6e3c">
            <link rel="shortcut icon" href="/favicons/favicon.ico">
            <meta name="msapplication-config" content="/favicons/browserconfig.xml">
            <meta name="theme-color" content="#0e4688">

            <script>
                // Set icons based on user's preferred color scheme
                const apple = document.querySelector('link[rel="apple-touch-icon"]');
                apple.href = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches
                    ? '/favicons/apple-touch-icon-dark.png'
                    : '/favicons/apple-touch-icon-light.png';

                const favicon32x32 = document.querySelector('link[rel="icon"][sizes="32x32"]');
                favicon32x32.href = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches
                    ? '/favicons/favicon-dark-32x32.png'
                    : '/favicons/favicon-light-32x32.png';

                const favicon16x16 = document.querySelector('link[rel="icon"][sizes="16x16"]');
                favicon16x16.href = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches
                    ? '/favicons/favicon-dark-32x32.png'
                    : '/favicons/favicon-light-32x32.png';
            </script>
        @show

        @section('user-data')
            @if(!is_null(Auth::user()))
                <script>
                    window.KubectylUser = {!! json_encode(Auth::user()->toVueObject()) !!};
                </script>
            @endif
            @if(!empty($siteConfiguration))
                <script>
                    window.SiteConfiguration = {!! json_encode($siteConfiguration) !!};
                </script>
            @endif
        @show
        <style>
            @import url('//fonts.googleapis.com/css?family=Rubik:300,400,500&display=swap');
            @import url('//fonts.googleapis.com/css?family=IBM+Plex+Mono|IBM+Plex+Sans:500&display=swap');
        </style>

        @yield('assets')

        @include('layouts.scripts')
    </head>
    <body class="{{ $css['body'] ?? 'bg-neutral-50' }}">
        @section('content')
            @yield('above-container')
            @yield('container')
            @yield('below-container')
        @show
        @section('scripts')
            {!! $asset->js('main.js') !!}
        @show
    </body>
</html>
