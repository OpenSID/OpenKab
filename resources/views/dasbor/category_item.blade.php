<div class="col-lg-3 col-6">
    <!-- small box -->
    <div class="small-box item-box bg-{{ $colors[$loop->index % count($colors)] }}">
        <div class="inner kategori-item">
            <h3 class="jumlah-{{ $key }}-elm">{{ $value }}</h3>

            <p>{{ $text }} </p>
        </div>
        <div class="icon">
            <i class="ion ion-stats-bars"></i>
        </div>
        <a href="#" data-url="{{ $url }}" class="small-box-footer btn-detail">Selengkapnya <i
                class="fas fa-arrow-circle-right"></i></a>
    </div>
</div>
