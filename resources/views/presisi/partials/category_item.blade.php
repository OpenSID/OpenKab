<div class="col-lg-3 col-sm-12 wow fadeInUp" data-wow-delay="0.3s">
    <a class="kategori-item d-block bg-light text-center rounded p-3" href="">
        <div class="rounded p-4">
            <div class="icon mb-3">
                <img class="img-fluid icons" src="{{ asset($icon) }}" alt="Icon">
            </div>
            <h6>{{ $text }}</h6>
            <span class="jumlah-{{ $key }}-elm">{{ $value }}</span>
        </div>
    </a>
</div>
