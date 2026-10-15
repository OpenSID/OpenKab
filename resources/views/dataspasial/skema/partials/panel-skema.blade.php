{{--
    Panel kiri (bagian bawah): daftar skema untuk sektor yang dipilih.

    ⚠️ FR-007a — daftar skema HANYA dirender bila admin SUDAH memilih sektor.
    Sebelum itu, panel menampilkan penjelasan, bukan daftar kosong yang membingungkan.
--}}
<div class="card card-outline card-info" data-testid="{{ $skemaSektor ? 'panel-skema' : 'panel-skema-belum-dipilih' }}">
    <div class="card-header py-2 px-3">
        <h3 class="card-title text-sm mb-0">
            <i class="fas fa-file-alt mr-1"></i>
            Skema
            @if ($sektorTerpilih)
                <span class="text-muted font-weight-normal">· {{ $sektorTerpilih['nama'] }}</span>
            @endif
        </h3>
    </div>

    <div class="card-body p-0">
        @if ($sektorTerpilih === null)
            {{-- Belum ada sektor dipilih — daftar skema belum boleh tampil (FR-007a). --}}
            <p class="text-muted text-center my-3 px-2 mb-0">
                Pilih salah satu sektor di atas untuk melihat daftar skema versi dan drafnya.
            </p>
        @elseif (count($skemaSektor) === 0)
            <p class="text-muted text-center my-3 px-2 mb-0">
                Sektor ini belum memiliki skema. Buat draf pertama untuk mulai menyusun isian.
            </p>
        @else
            <ul class="list-group list-group-flush">
                @foreach ($skemaSektor as $skema)
                    @php $terpilih = $skemaTerpilih !== null && $skemaTerpilih['id'] === $skema['id']; @endphp
                    <li class="list-group-item list-group-item-action py-2 px-3 {{ $terpilih ? 'active' : '' }}"
                        data-testid="item-skema"
                        data-skema-id="{{ $skema['id'] }}"
                        data-nomor-versi="{{ $skema['nomorVersi'] }}"
                        data-status="{{ $skema['status'] }}">
                        <a href="{{ route('dataspasial.skema', array_filter([
                            'sektor' => $sektorTerpilih['kode'] ?? null,
                            'skema' => $skema['id'],
                        ])) }}" class="{{ $terpilih ? 'text-white' : 'text-dark' }}">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>Versi {{ $skema['nomorVersi'] }}</strong>
                                @if ($skema['status'] === 'draf')
                                    <span class="badge badge-warning">Draf berjalan</span>
                                @else
                                    <span class="badge badge-success">Terbit</span>
                                @endif
                            </div>
                            <small class="{{ $terpilih ? 'text-white-50' : 'text-muted' }}">
                                {{ $skema['jumlahIsian'] }} isian
                                @if ($skema['diterbitkanPada'])
                                    · terbit {{ \Illuminate\Support\Carbon::parse($skema['diterbitkanPada'])->translatedFormat('d M Y') }}
                                @endif
                            </small>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($sektorTerpilih !== null)
        {{--
            Tombol "+ Tambah Skema Baru" — DI BAWAH daftar skema (FR-007c).

            Sifatnya `type="button"`: sekali diklik, JS memeriksa apakah draf sudah
            berjalan. Bila ada, tampil konfirmasi "Buka draf / Batal" (FR-007g/Q6);
            bila belum, POST draf kosong ke `POST /dataspasial/skema`.
        --}}
        <div class="card-footer p-2">
            <button type="button" class="btn btn-outline-info btn-block btn-sm"
                    id="btn-tambah-skema" data-testid="btn-tambah-skema"
                    {{ $sektorTerpilih ? '' : 'disabled' }}>
                <i class="fas fa-plus mr-1"></i>Tambah Skema Baru
            </button>
        </div>
    @endif
</div>