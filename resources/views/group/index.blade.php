@extends('layouts.index')

@section('title', 'Identitas OpenKAB')

@section('content_header')
    <h1>Group OpenKAB</h1>
@stop

@section('content')

    <div class="row" x-data="group()" x-init="retrieveData()">

        <div class="col-sm-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <a x-bind:href="'{{ url('pengaturan/groups/tambah') }}'">
                        <button type="button" class="btn btn-primary btn-sm"><i class="far fa-plus"></i>  Tambah </button>
                    </a>
                </div>

                <div class="card-body">

                    <div class="row">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="user">
                                <thead>
                                    <tr>
                                        <th width="50">No</th>
                                        <th width="150">Aksi</th>
                                        <th>Group</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(value, index) in dataGroups">
                                        <tr>
                                            <td class="text-center"><span x-text="index+1"></span></td>
                                            <td class="text-center">  </td>
                                            <td  x-text="value.attributes.name"></td>
                                        </tr>
                                    </template>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    <script>
        function group() {
            return {
                id: 1,

                dataGroups: {},
                retrieveData() {
                    fetch('{{ url('api/v1/pengaturan/group/') }}')
                        .then(res => res.json())
                        .then(response => {
                            this.dataGroups = response.data;
                            console.log( this.dataGroups);
                        });
                },
            }
        }
    </script>
@endsection
