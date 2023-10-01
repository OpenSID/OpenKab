<div class="col-md-12 animated fadeIn">
    <div class="owl-carousel header-carousel">
        @forelse ((new App\Http\Repository\CMS\SlideRepository)->activeSlide(5) as $slide)
            <div class="owl-carousel-item">
                <img class="img-fluid" src="{{ Storage::url($slide->thumbnail) }}" alt="">
            </div>
        @empty
        @endforelse
    </div>
</div>
