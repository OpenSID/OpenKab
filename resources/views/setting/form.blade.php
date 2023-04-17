<div class="col">
    <div class="mb-4">
        <label for="name">Warna Tema Admin<span class="text-danger">*</span></label>
        <select class="form-control" name="warna_tema_admin">
            @foreach($warna_tema_admin_option as $key => $data)
                <option value="{{$key}}" {{ ( $warna_tema_admin->value == $key) ? 'selected' : '' }}>{{$data}}</option>
            @endforeach
        </select>
        @error('name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
