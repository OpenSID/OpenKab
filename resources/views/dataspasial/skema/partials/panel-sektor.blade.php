{{--
    Panel kiri: daftar sektor (FR-007) + tombol tambah sektor (FR-007c).

    Kode sektor ditampilkan sebagai BACA-SAJA (FR-007d-3) karena kode dibangkitkan
    oleh API dan dijamin unik global (research.md R4) — admin tidak mengeditnya.
--}}
<div class="card card-outline card-primary mb-3">
    <div class="card-header py-2 px-3">
        <h3 class="card-title text-sm mb-0"><i class="fas fa-layer-group mr-1"></i>Sektor</h3>
    </div>

    <div class="card-body p-0">
        @if (count($daftarSektor) === 0)
            <p class="text-muted text-center my-3 px-2 mb-0">
                Belum ada sektor. Tambahkan sektor pertama untuk mulai menyusun skema.
            </p>
        @else
            <ul class="list-group list-group-flush" data-testid="daftar-sektor">
                @foreach ($daftarSektor as $sektor)
                    @php $terpilih = $sektorTerpilih !== null && $sektorTerpilih['id'] === $sektor['id']; @endphp
                    <li class="list-group-item list-group-item-action py-2 px-3 d-flex align-items-start {{ $terpilih ? 'active' : '' }}"
                        data-testid="item-sektor"
                        data-sektor-id="{{ $sektor['id'] }}"
                        data-sektor-kode="{{ $sektor['kode'] }}"
                        data-sektor-nama="{{ $sektor['nama'] }}"
                        data-sektor-ikon="{{ $sektor['ikon'] ?? '' }}"
                        data-ada-draf="{{ $sektor['adaDraf'] ? '1' : '0' }}">
                        <a href="{{ route('dataspasial.skema', array_filter(['sektor' => $sektor['kode']])) }}"
                           class="flex-grow-1 {{ $terpilih ? 'text-white' : 'text-dark' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="font-weight-bold">{{ $sektor['nama'] }}</div>
                                    <small class="{{ $terpilih ? 'text-white-50' : 'text-muted' }}">
                                        Kode sektor: <code>{{ $sektor['kode'] }}</code>
                                    </small>
                                </div>
                                <span class="badge badge-light text-uppercase ml-1">
                                    {{ $sektor['status'] }}
                                </span>
                            </div>

                            {{--
                                FR-007 — label **versi terakhir + statusnya**.
                                ⚠️ Jumlah versi tidak lagi ditampilkan; yang
                                Useful bagi admin adalah versi berapa yang berlaku
                                dan apakah masih berupa draf.
                            --}}
                            <div class="mt-1">
                                @php
                                    $statusTerakhir = $sektor['statusTerakhir'] ?? 'kosong';
                                    $nomorVersiTerakhir = (int) ($sektor['nomorVersiTerakhir'] ?? 0);
                                @endphp

                                @if ($statusTerakhir === 'draf')
                                    <span class="badge badge-warning"
                                          data-testid="label-status-versi-sektor">Draf</span>
                                @elseif ($statusTerakhir === 'terbit')
                                    <span class="badge badge-success"
                                          data-testid="label-status-versi-sektor">Terbit</span>
                                @else
                                    <span class="badge badge-secondary"
                                          data-testid="label-status-versi-sektor">Belum ada skema</span>
                                @endif

                                @if ($nomorVersiTerakhir > 0)
                                    <small class="ml-1 badge badge-info {{ $terpilih ? 'text-white-50' : 'text-muted' }}"
                                           data-testid="label-nomor-versi-sektor">
                                        v{{ $nomorVersiTerakhir }}
                                    </small>
                                @endif
                            </div>
                        </a>

                        {{-- US7 (FR-007d-4): ubah nama & ikon sektor sekarang dilakukan
                        melalui editor skema (nama sektor dikirim sebagai sektor_nama
                        pada setiap simpan), bukan tombol tersendiri di sini. --}}
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Tombol tambah sektor: selalu terlihat (FR-007c). --}}
    <div class="card-footer p-2">
        <button type="button" class="btn btn-outline-primary btn-block btn-sm"
                id="btn-tambah-sektor" data-testid="btn-tambah-sektor">
            <i class="fas fa-plus mr-1"></i>Tambah Sektor Baru
        </button>
    </div>
</div>
