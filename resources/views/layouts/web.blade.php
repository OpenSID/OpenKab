<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>OpenKab - Website Kabupaten</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <base href="{{ route('web.index') }}" />
    <!-- Favicon -->
    <link href="{{ asset('web/img/logo.png') }}" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Inter:wght@700;800&display=swap" rel="stylesheet">

    @vite('resources/sass/web.scss', 'build-web')

    <!-- Template Stylesheet -->
    <link href="{{ asset('web/css/openkab.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body>
    <div class="container-fluid bg-white p-0">
        @include('web.partials.nav')

        @yield('content')

        @include('web.partials.footer')

        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>

    <!-- JavaScript Libraries -->
    @vite('resources/js/web.js', 'build-web')
    <script defer src="{{ asset('web/lib/wow/wow.min.js') }}"></script>
    <script defer src="{{ asset('web/lib/owlcarousel/owl.carousel.min.js') }}"></script>
    <!-- Template Javascript -->
    <script defer src="{{ asset('assets/costume/js/admin.js') }}"></script>
    <script defer src="{{ asset('web/js/main.js') }}"></script>
    @stack('scripts')
</body>

</html>
