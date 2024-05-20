<div class="row over-flow g-4">
    @foreach ($categoriesItems as $item)
        @include('presisi.partials.category_item', $item)
    @endforeach
</div>
