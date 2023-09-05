<div class="col">
    <div class="mb-4">
        <label for="name">Nama<span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name ?? '') }}">
        @error('name')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
@isset($groups)
<div class="col">
    <div class="mb-4">
        <label for="username">Username<span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $user->username ?? '') }}">
        @error('username')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="col">
    <div class="mb-4">
        <label for="group">Group<span class="text-danger">*</span></label>
        <select class="form-control @error('group') is-invalid @enderror" name="group" required>
            @foreach ($groups as $group)
                <option value="{{ $group->id }}" @selected($group->id == old('group', $user->group ?? $team)  )>{{ $group->name }}</option>
            @endforeach
        </select>
        @error('group')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
@endisset

<div class="col">
    <div class="mb-4">
        <label for="email">Surel<span class="text-danger">*</span></label>
        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email ?? '') }}">
        @error('email')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

@if (!$user)
    <div class="col">
        <div class="mb-4">
            <label for="password">Kata Sandi<span class="text-danger">*</span></label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password', $user->password ?? '') }}">
            @error('password')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
@endif

<div class="col">
    <div class="mb-4">
        <label for="company">Instansi</label>
        <input type="text" class="form-control @error('company') is-invalid @enderror" name="company" value="{{ old('company', $user->company ?? '') }}">
        @error('company')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="col">
    <div class="mb-4">
        <label for="phone">Nomor HP</label>
        <input type="number" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
        @error('phone')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
</div>
