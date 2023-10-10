<div class="container">
    <div class="row g-0 gx-5 align-items-end">
        <div class="col-lg-6">
            <div class="text-start mx-auto mb-5 wow slideInUp" data-wow-delay="0.1s">
                <h1 class="mb-3">Kelurahan Paling Aktif</h1>
                <p>Kelurahan yang sudah mengisi data & pengujung terbanyak</p>
            </div>
        </div>
    </div>
    <div class="tab-content">
        <div id="tab-1" class="tab-pane fade show p-0 active">
            <div class="row g-4 replace-content-property">
                @for ($i = 0; $i < 6; $i++)
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="kelurahan-item rounded overflow-hidden">
                        <div class="position-relative overflow-hidden">
                            <a href=""><img class="img-fluid" src="{{ asset('web/img/keluarahan-1.jpg') }}" alt=""></a>
                        </div>
                        <div class="p-4 pb-0">
                            <a class="d-block h5 mb-2 nama-desa-elm" href="">Nama desa</a> <a class="website-elm" href="#"><i class="fas fa-globe"></i>
                                Website</a>
                            <p class="alamat-elm"><i class="fa fa-map-marker-alt text-primary me-2"></i>alamat</p>
                        </div>
                        <div class="d-flex border-top">
                            <small class="flex-fill text-center border-end py-2 penduduk-elm"><i
                                    class="fa fa-users text-primary me-2"></i>0 Penduduk</small>
                            <small class="flex-fill text-center border-end py-2 keluarga-elm"><i
                                    class="fa fa-venus-mars text-primary me-2"></i>0 Keluarga</small>
                            <small class="flex-fill text-center py-2 rtm-elm"><i class="fa fa-home text-primary me-2"></i>0
                                RTM</small>
                        </div>
                    </div>
                </div>
                @endfor
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script nonce="{{ csp_nonce() }}" type="text/javascript">
document.addEventListener("DOMContentLoaded", function (event) {
    "use strict";
    $.get('{{ url('index.php/api/v1/desa-aktif') }}', {}, function(result){
        if (result.data.length > 0){
            let _elm
            result.data.forEach((item, index) => {
                _elm = $('.replace-content-property .kelurahan-item').eq(index)
                _elm.find('.nama-desa-elm').text(item.attributes.nama_desa)
                _elm.find('.website-elm').attr('href', item.attributes.website ?? '#')
                _elm.find('.penduduk-elm').html('<i class="fa fa-users text-primary me-2"></i>'+item.attributes.penduduk+ ' Penduduk')
                _elm.find('.alamat-elm').html('<i class="fa fa-map-marker-alt text-primary me-2"></i>'+(item.attributes.alamat ?? 'alamat belum ditentukan'))
                _elm.find('.keluarga-elm').html('<i class="fa fa-venus-mars text-primary me-2"></i>'+item.attributes.keluarga+ ' Keluarga')
                _elm.find('.rtm-elm').html('<i class="fa fa-home text-primary me-2"></i>'+item.attributes.rtm+ ' RTM')
            })
        }
    }, 'json')
});
</script>
@endpush
