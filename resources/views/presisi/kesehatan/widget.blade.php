<div class="row">
    @php
        $listIcon = ['fa-female', 'fa-child', 'fa-female', 'fa-child', 'fa-child', 'fa-child', 'fa-child'];
    @endphp
    @foreach ($data['widgets'] as $index => $item)
        <div class="col-lg-4 col-sm-6 col-xs-12">
            <div class="small-box bordered p-1 {{ $item['bg-color'] == 'bg-gray' ? 'bg-danger' : $item['bg-color'] }}">
                <div class="row p-2">
                    <div class="col-md-3 col-sm-4">
                        <div class="icon icon-custom">
                            <i class="fa fa-4x {{ $listIcon[$index] ?? 'fa-female' }}"></i>
                        </div>
                    </div>
                    <div class="col-md-9 col-sm-8">
                        <p>{{ $item['title'] }}</p>
                        <p class="label-widget-kesehatan">{{ $item['total'] }}</p>
                    </div>
                </div>

            </div>
        </div>
    @endforeach
</div>
