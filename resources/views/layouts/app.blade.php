<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/orgchart/5.0.0/css/jquery.orgchart.min.css" integrity="sha512-9A2BSSUL5eXVMWwrB8aDX8GeOOSMMVCk3fvqOplnswmo4IN4s6DW2ywpb3VCDcGCVwDc3g6S1k9T72NsCkgw5A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    @yield('ext_css')
    <style>
    .page-header {
        margin-top: 0px;
    }
    </style>
</head>
<body>
    <div id="app">
        @include('layouts.partials.nav')

        <div class="container">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
        
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
        @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/orgchart/5.0.0/js/jquery.orgchart.min.js" integrity="sha512-IUNqrYw8R7mj0iBzb0FOTGTgEFrxZCHVCHnePUEmcjJ/XQE/0sqRhBmGpp20N2lVzAkIBs0Sz+ibRN8/W9YFnQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @yield('ext_js')
    @yield('script')
    <script>
        var header = $('h2.page-header').contents();
        str = '';
        mainText = header.filter(function () {
                // return type of text
                return this.nodeType === 3;
            })[0];
        str += mainText.data.trim();

        if (mainText.nextSibling) {
            // next siblings should be a small tag text
            str += " - "+mainText.nextSibling.innerText;
        }
        $('title').prepend(str+" - ");
    </script>
</body>
</html>
