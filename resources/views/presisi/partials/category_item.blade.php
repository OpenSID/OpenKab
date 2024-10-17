<button type="button" class="btn bg-white cbg-white {{ $loop->last ? '' : 'mr-1' }} rounded-0">
        <div class="info-box-content text-center kategori-item m-3">
            <h5 class="text-dark text-capitalize"><i class="fa-solid fa-square text-{{ $colors[$loop->index % count($colors)] }}"></i> {{ $text }} <i class="fa-solid fa-circle-info text-muted text-sm"></i></h5>
            <span class="text-danger jumlah-{{ $key }}-elm">{{ $value }}</span>
        </div>
</button>