<div class="row over-flow g-4">
    @foreach ($categoriesItems as $item)
        @include('web.partials.category_item', $item)
    @endforeach
</div>
