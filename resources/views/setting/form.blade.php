<div class="col">
    <div class="mb-4">
        <label for="name">Warna Tema<span class="text-danger">*</span></label>
        <input type="text" class="form-control my-colorpicker1" name="warna_tema" value="{{$warna_tema->value}}">
        @error('name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
