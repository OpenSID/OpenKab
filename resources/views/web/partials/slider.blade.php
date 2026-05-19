<div class="col-md-12 animated fadeIn">
    <div class="owl-carousel header-carousel">
        @php
            $placeholderImage = asset('assets/img/no-image.png');
        @endphp
        @forelse ((new App\Http\Repository\CMS\SlideRepository)->activeSlide(5) as $slide)
            <div class="owl-carousel-item">
                <img class="img-fluid" src="{{ Storage::url($slide->thumbnail) }}" alt="" onerror="this.onerror=null;this.src='{{ $placeholderImage }}';">
            </div>
        @empty
            <div class="owl-carousel-item">
                <img class="img-fluid" src="{{ $placeholderImage }}" alt="No Slide Available">
            </div>
        @endforelse
    </div>
</div>
