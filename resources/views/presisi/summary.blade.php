<div class="row">
    @foreach ($categoriesItems as $item)
        @include('presisi.partials.category_item', $item)
    @endforeach
</div>
