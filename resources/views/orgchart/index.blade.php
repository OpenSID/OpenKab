@extends('layouts.index')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div id="chart-container" class="col-12"></div>
        </div>
    </div>
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/orgchart/jquery.orgchart.css') }}">
    <style nonce="{{ csp_nonce() }}" type="text/css">
        #chart-container {
            height: 550px;
            position: relative;
            border: 1px solid #aaa;
            margin: 0.5rem;
            overflow: auto;
            text-align: center;
        }
        .orgchart .node .title {
            height: unset;
            text-align: left;
            line-height: 40px;
            width: 150px;
        }

        .orgchart .node .content {
            text-align: left;
            padding: 5px;
        }

        .orgchart .node .content .symbol {
            color: #aaa;
            margin-right: 20px;
        }

        .oci-leader::before,
        .oci-leader::after {
            background-color: rgba(217, 83, 79, 0.8);
        }

        .orgchart .node .avatar {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            /*float: left;
            margin: 5px;
            */
        }
        .textlinebox {
            line-height: 12px;
            white-space: pre-line;
        }
      </style>
@endsection

@section('js')
    <script defer src="{{ asset('vendor/orgchart/jquery.orgchart.js') }}"></script>
    <script src="{{ asset('vendor/orgchart/html2canvas.min.js') }}"></script>
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            var nodeTemplate = function(data) {
                let _defaultPhoto = "{{ asset('assets/img/avatar.png') }}"
                let _storageUrl = "{{ Storage::url('') }}"
                let _imageName = []
                if (data.employees.length) {
                    data.employees.forEach(function(item){
                        _imageName.push(`
                        <div class="textlinebox border-bottom border-danger">
                            <img class="avatar" src="${ item.foto ? _storageUrl+item.foto : _defaultPhoto }" />
                            <div class="">
                                ${item.name}
                                ${item.identity_number}
                                ${item?.position?.name}
                                ${data.name}
                            </div>
                        </div>
                        `)
                    })
                } else {
                    _imageName.push(`
                        <div class="textlinebox border-bottom border-danger">
                            <img class="avatar" src="${_defaultPhoto}" />
                            ${data.name}
                        </div>`)
                }
                return `
                    <div class="title text-center">
                        <div class="ml-3 bg-white border border-danger">${_imageName.join('')}</div>
                    </div>
                `;
            };
            var datasource = {!! $tree[0] ?? '' !!}

            $('#chart-container').orgchart({
                'exportButton': true,
                'exportButtonName': 'Cetak',
                'exportFilename': 'MyOrgChart',
                'data': datasource,
                'nodeTemplate': nodeTemplate,
                'nodeID': 'id'
            });
        });

    </script>
@endsection
