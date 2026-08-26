<div class="col-lg-3 col-6" data-testid="summary-card-{{ $key }}">
    <!-- small box -->
    <div class="small-box item-box bg-{{ $colors[$loop->index % count($colors)] }}">
        <div class="inner kategori-item">
            <h3 class="jumlah-{{ $key }}-elm" data-testid="summary-value-{{ $key }}">{{ $value }}</h3>

            <p data-testid="summary-label-{{ $key }}">{{ $text }} </p>
        </div>
        <div class="icon">
            <i class="ion ion-stats-bars"></i>
        </div>
        <a href="#" data-url="{{ $url }}" class="small-box-footer btn-detail" data-testid="summary-link-{{ $key }}">Selengkapnya <i
                class="fas fa-arrow-circle-right"></i></a>
    </div>
</div>
