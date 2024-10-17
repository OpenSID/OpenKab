<div class="btn-group bg-c2">
    @php
        $colors = ['primary', 'warning', 'success', 'info']; // Array warna
    @endphp
    
    @foreach ($categoriesItems as $item)
        @include('presisi.partials.category_item', $item)
    @endforeach
</div>
