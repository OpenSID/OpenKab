$('#filter_kabupaten').select2({
placeholder: "{{ config('app.sebutanKab') }}"
});
GetListKabupaten();
$('#filter_kabupaten').on("select2:select", function(e) {
GetListKecamatan(this.value);
});
function GetListKabupaten() {
$('#filter_kabupaten').empty().trigger("change");
var optionEmpty = new Option("", "");
$("#filter_kabupaten").append(optionEmpty);
$.ajax({
type: 'GET',
url: "{{ config('app.databaseGabunganUrl') . '/api/v1/statistik-web/get-list-kabupaten' }}",
dataType: 'json',
success: function(data) {
for (var i = 0; i < data.length; i++) { var newOption=new Option(data[i].nama_kabupaten, data[i].kode_kabupaten, true,
    true); $("#filter_kabupaten").append(newOption); } $("#filter_kabupaten").val("").trigger("change"); }, error:
    function(jqXHR, textStatus, errorThrown) { if (textStatus==="timeout" ) { alert("Permintaan data kabupaten gagal
    karena waktu koneksi habis (timeout). Silakan coba lagi."); } else { try { var
    responseJSON=JSON.parse(jqXHR.responseText); alert("Terjadi kesalahan: " + responseJSON.message);
                } catch (e) {
                    alert(" Terjadi kesalahan tidak terduga: " + errorThrown);
                }
            }
        }
    });
}
