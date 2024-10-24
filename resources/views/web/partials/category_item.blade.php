<div class="col-lg-2 col-md-4 col-sm-6 wow fadeInUp mb-4" data-wow-delay="0.3s">
    <a class="kategori-item d-block bg-light text-center rounded p-3" href="" style="height: 200px;">
        <div class="rounded p-1 d-flex flex-column justify-content-center align-items-center h-100">
            <div class="icon mb-3">
                <img class="img-fluid icons" src="{{ asset($item['icon']) }}" alt="Icon">
            </div>
            <h6 class="mb-3">{{ $item['text'] }}</h6>
            <span class="jumlah-{{ $item['key'] }}-elm">{{ $item['value'] }}</span>
        </div>
    </a>
</div>