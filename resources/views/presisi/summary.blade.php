<div class="btn-group bg-c2">
<button type="button" class="btn bg-white cbg-white mr-1 rounded-0">
        <div class="info-box-content text-center kategori-item m-3 text-primary rounded-circle c-badge">
        <h4><i class="fa-solid fa-chart-column"></i></h4>
        </div>
</button>
    @php
        $colors = ['primary', 'warning', 'success', 'info']; // Array warna
    @endphp
    
    @foreach ($categoriesItems as $item)
        @include('presisi.partials.category_item', $item)
    @endforeach
</div>
