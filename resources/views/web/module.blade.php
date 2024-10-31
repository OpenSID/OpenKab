@extends('layouts.statistik')

@section('content')
    @includeWhen($moduleName == 'org' , 'web.partials.org', ['tree' => $content])
    @includeWhen($moduleName == 'unduhan' , 'web.partials.unduhan', ['unduhans' => $content])
    @includeWhen($moduleName == 'statistik' , 'web.partials.statistik-web')
@endsection
