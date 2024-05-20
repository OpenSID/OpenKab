@extends('adminlte::page')

@section('footer')
    <strong>Hak cipta © <?= date('Y') ?> <a href="https://opendesa.id">OpenDesa</a>.</strong>
    Seluruh hak cipta dilindungi.
    <div class="float-right d-none d-sm-inline-block">
        <b>Versi</b> {{ openkab_versi() }}
    </div>
@endsection
@stack('scripts')
    <script nonce="{{ csp_nonce() }}" type="application/javascript">
    document.addEventListener("DOMContentLoaded", function(event) {
        // remove menu, ganti dengan yang baru. Pakai cara ini dulu
        $('div.sidebar nav>ul').find('li').remove()
        $('div.sidebar nav>ul').append(`<li class="nav-item">
                                            <a class="nav-link  " href="presisi">                                                
                                                <p>
                                                    Demografi
                                                </p>
                                            </a>
                                            </li>`)
                                            $('div.sidebar nav>ul').append(`<li class="nav-item">
                                            <a class="nav-link  " href="presisi/sosial">                                                
                                                <p>
                                                    Sosial
                                                </p>
                                            </a>
                                            </li>`)
                                            $('div.sidebar nav>ul').append(`<li class="nav-item">
                                            <a class="nav-link  " href="presisi/ekonomi">                                                
                                                <p>
                                                    Ekonomi
                                                </p>
                                            </a>
                                            </li>`)
                                            $('div.sidebar nav>ul').append(`<li class="nav-item">
                                            <a class="nav-link  " href="bantuan">                                                
                                                <p>
                                                    Kesehatan
                                                </p>
                                            </a>
                                            </li>`)         
                                            $('div.sidebar nav>ul').append(`<li class="nav-item">
                                            <a class="nav-link  " href="bantuan">                                                
                                                <p>
                                                    Kependudukan
                                                </p>
                                            </a>
                                            </li>`)                                            
    })
    </script>



