
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.namaAplikasi') }}</title>

    @include('layouts.presisi.partials.stylesheet')
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed bg-light bg-c1">
    
    <div class="wrapper">

        <!-- Preloader -->
        {{-- <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div> --}}

  @include('layouts.presisi.partials.header')


        <!-- Main Sidebar Container -->
        {{-- <aside class="main-sidebar sidebar-dark-primary mt-4 rounded-xl">
            <!-- Brand Logo -->
            <div class="  text-center">
                <a href="{{ url('presisi') }}" class="brand-link">
                    <img src="{{ asset('assets/img/opensid_logo.png') }}" alt="AdminLTE Logo" style="height: 150px">
                </a>
            </div>
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside> --}}

        <!-- Content Wrapper. Contains page content -->
        <div class="container">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">

                    @yield('content_header')

                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content rounded-xl mr-3">
                <div class="container-fluid">
                    @yield('content')
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer ml-0">
            <strong>Hak cipta © <?= date('Y') ?> <a href="https://opendesa.id">OpenDesa</a>.</strong>
            Seluruh hak cipta dilindungi.
            <div class="float-right d-none d-sm-inline-block">
                <b>Versi</b> {{ openkab_versi() }}
            </div>
        </footer>


    </div>
    <!-- ./wrapper -->
    @include('layouts.presisi.partials.javascript')
    @stack('js')
</body>

</html>
