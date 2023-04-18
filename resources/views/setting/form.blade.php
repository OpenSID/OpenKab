<div class="col">
    <div class="mb-4">
        <label for="name">Warna Tema<span class="text-danger">*</span></label>
        @if(!empty($warna_tema))
            {{-- Append slot and data-* config --}}
            <x-adminlte-input-color name="warna_tema" data-color="{{$warna_tema->value}}" data-format='hex'
                                    data-horizontal=true>
                <x-slot name="appendSlot">
                    <div class="input-group-text">
                        <i class="fas fa-lg fa-square"></i>
                    </div>
                </x-slot>
            </x-adminlte-input-color>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        @else
            <div class="alert alert-danger" role="alert">
                Key pengaturan warna tema tidak ditemukan.
            </div>
        @endif
    </div>
</div>

