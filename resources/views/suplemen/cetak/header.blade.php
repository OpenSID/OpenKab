@if ($aksi != 'unduh')
    <img class="logo" src="{{ asset('web/img/logo.png') }}" alt="logo-desa">
@endif
<h1 class="judul">
    @if($nama_kabupaten)
    PEMERINTAH  {!! strtoupper('Kabupaten' . ' ' . $nama_kabupaten . ' <br>Kecamatan ' . $nama_kecamatan . ' <br>Desa ' . $nama_desa) !!}
    @else
        @if(session('kabupaten.nama_kabupaten'))
            PEMERINTAH 
            {!! session('kabupaten.nama_kabupaten') ? 'KABUPATEN ' . strtoupper(session('kabupaten.nama_kabupaten')) : '' !!} 
            {!! session('kecamatan.nama_kecamatan') ? '<br>KECAMATAN ' . strtoupper(session('kecamatan.nama_kecamatan')) : '' !!} 
            {!! session('desa.nama_desa') ? '<br>DESA ' . strtoupper(session('desa.nama_desa')) : '' !!}
        @else
            {{ strtoupper($identitasAplikasi['nama_aplikasi']) }}
        @endif
    @endif
</h1>