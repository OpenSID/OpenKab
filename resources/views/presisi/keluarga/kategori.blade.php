<div class="card-body p-0 ">
    <ul class="nav nav-pills flex-column" id="nav-statistik">
        @foreach ($statistik as $key => $sub)
            <li class="nav-item active">
                <a href="javascript:;" class="nav-link rounded-0"
                    data-key="{{ $key }}" data-name="{{ $sub }}">
                    <i class="fas fa-inbox"></i> {{ $sub }}
                </a>
            </li>
        @endforeach



    </ul>
</div>