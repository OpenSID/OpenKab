    @vite('resources/sass/web.scss', 'build-web')
    <!-- Template Stylesheet -->
    <link href="{{ asset('web/css/openkab.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/plugins/select2/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}" rel="stylesheet" />

    <div class="container-fluid bg-white p-0">
        @include('layouts.presisi.partials.nav')
    </div>
