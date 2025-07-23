function GetSummary(){
    const urlSummary = new URL(
                    "{{ config('app.databaseGabunganUrl') . '/api/v1/data-summary' }}");
    var searchParam = {'search' : {'luas_wilayah' : 1, 'luas_pertanian' : 1, 'luas_perkebunan' : 1, 'luas_hutan' : 1, 'luas_peternakan' : 1},'filter' : {'kecamatan' :  $("#filter_kecamatan").val(), 'desa' : $("#filter_desa").val()}}
    $.get(urlSummary, searchParam, function(result){
        for(let i in result.data){
            $(`#summary-${i}`).text(result.data[i])
        }
    }, 'json')
    {{-- $.get('{{ url('api/v1/data-summary') }}', searchParam, function(result){
        for(let i in result.data){
            $(`#summary-${i}`).text(result.data[i])
        }
    }, 'json') --}}
}