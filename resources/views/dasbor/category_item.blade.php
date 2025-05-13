<button type="button" class="btn bg-white cbg-white {{ $loop->last ? '' : 'mr-1' }} rounded-0">
        <div class="info-box-content text-center kategori-item m-3">
            <table class="text-left">
                <tr>
                    <td><h5 class="text-dark text-capitalize mr-1"><i class="fa-solid fa-square text-{{ $colors[$loop->index % count($colors)] }}"></i> </h5>
                    </td>
                    <td><h5 class="text-dark text-capitalize">{{ $text }} <i class="fa-solid fa-circle-info text-muted text-sm"></i></h5>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><span class="text-danger jumlah-{{ $key }}-elm">{{ $value }}</span></td>
                </tr>
            </table>
        </div>
</button>