@php
    if (empty($ekstensi)) {
        $ekstensi = 'xls';
    }

    if ($aksi == 'unduh') {
        header('Content-type: application/' . $ekstensi);
        header('Content-Disposition: attachment; filename=' . date('y-m-d').'_'.$file . '.' . $ekstensi);
        header('Pragma: no-cache');
        header('Expires: 0');
    }
@endphp

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
@if ($aksi == 'cetak' && !isset($headjs))
    @include('layouts.components.headjs')
@elseif ($aksi == 'cetak' && $headjs)
    @include('layouts.components.headjs')
@endif

<head>
    <title>{{ ucwords($file) }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="{{ url('assets/css/report.css') }}" rel="stylesheet">
    @stack('css')
    @stack('scripts')
</head>

<body>
    <div id="container">
        <div id="body" @include($isi) </div>
            
        </div>
</body>

</html>
