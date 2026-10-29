(function () {
    'use strict';

    function boot() {
        const $ = window.jQuery;

        if (!$) {
            return;
        }

        const config = window.lokasiConfig || {};
        const header = @include('layouts.components.header_bearer_api_gabungan');

        /* ------------------------------------------------
         * Tabel Data Lokasi
         * ---------------------------------------------- */
        const lokasiTable = $('#plan').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ordering: true,
            searchPanes: {
                viewTotal: false,
                columns: [0],
            },
            ajax: {
                url: new URL("{{ config('app.databaseGabunganUrl').'/api/v1/plan' }}"),
                headers: header,
                method: 'get',
                data: function (row) {
                    return {
                        'page[size]': row.length,
                        'page[number]': (row.start / row.length) + 1,
                        'filter[search]': row.search.value,
                        'sort': (row.order[0]?.dir === 'asc' ? '' : '-') + row.columns[row.order[0]?.column]?.name,
                        'filter[status]': $('#status').val(),
                        'filter[point]': $('#point').val(),
                        'filter[subpoint]': $('#subpoint').val(),
                        'filter[kode_kabupaten]': $('#filter_kabupaten').val(),
                        'filter[kode_kecamatan]': $('#filter_kecamatan').val(),
                        'filter[kode_desa]': $('#filter_desa').val(),
                    };
                },
                dataSrc: function (json) {
                    json.recordsTotal = json.meta.pagination.total;
                    json.recordsFiltered = json.meta.pagination.total;

                    return json.data;
                },
            },
            columnDefs: [
                { targets: '_all', className: 'text-nowrap' },
                { targets: [0, 1, 2, 4], orderable: false, searchable: false },
            ],
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="row-check" value="${row.id}" data-testid="row-check">`;
                    },
                },
                { data: null },
                {
                    data: null,
                    name: 'aksi',
                    searchable: false,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <a href="{{ url('plan/ajax_lokasi_maps') }}/${row.parent ?? 0}/${row.id}" target="_blank" class="btn btn-info btn-sm" title="Rincian" data-testid="bt-rincian"><i class="fas fa-search"></i></a>
                            <button type="button" class="btn btn-warning btn-sm btn-edit" title="Ubah" data-id="${row.id}" data-testid="bt-edit"><i class="fas fa-edit"></i></button>                            
                        `;
                    },
                },
                { data: 'nama', name: 'nama', searchable: true, orderable: true },
                { data: 'nama_desa', name: 'nama_desa', searchable: false, orderable: false },
                { data: 'enabled', name: 'enabled', searchable: false, orderable: true },
                { data: 'jenis', name: 'jenis', searchable: false, orderable: false },
                { data: 'kategori', name: 'kategori', searchable: false, orderable: false },
            ],
            order: [[3, 'asc']],
        });

        lokasiTable.on('draw.dt', function () {
            const pageInfo = $('#plan').DataTable().page.info();
            lokasiTable.column(1, { page: 'current' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1 + pageInfo.start;
            });

            $('#check-all').prop('checked', false);
        });

        /* ---------- Filter (mengikuti pola halaman /penduduk) ---------- */
        $('#point').on('change', function () {
            const _label = $(this).find('option:selected').text();
            $('#subpoint').val('');
            $('#subpoint').find('optgroup').prop('disabled', 1);

            if ($(this).val()) {
                $('#subpoint').find(`optgroup[label="${_label}"]`).prop('disabled', 0);
            }
        });

        $('#filter').on('click', function () {
            lokasiTable.draw();
        });

        $('#filter_kabupaten, #filter_kecamatan, #filter_desa').on('change', function () {
            lokasiTable.draw();
        });

        $(document).on('click', '#reset', function (e) {
            e.preventDefault();
            $('#status').val('').trigger('change');
            $('#point').val('').trigger('change');
            $('#subpoint').val('').trigger('change');
            $('#filter_kabupaten').val('').trigger('change');
            $('#filter_kecamatan').val('').trigger('change');
            $('#filter_desa').val('').trigger('change');
            lokasiTable.draw();
        });

        /* ---------- Pilihan Kategori dinamis ---------- */
        function setupKategoriSelect(jenisSelector, kategoriSelector) {
            const jenis = $(jenisSelector);
            const kategori = $(kategoriSelector);
            const daftar = config.jenisKategori || [];

            const refresh = function () {
                const selected = (jenis.val() || '').toString();

                kategori.empty().append(new Option('Pilih Kategori', ''));

                const match = daftar.find(function (item) {
                    return String(item.id) === selected;
                });

                if (match && Array.isArray(match.children) && match.children.length > 0) {
                    match.children.forEach(function (child) {
                        kategori.append(new Option(child.nama, child.id));
                    });
                    kategori.prop('disabled', false);
                } else {
                    kategori.prop('disabled', true);
                }
            };

            jenis.on('change', refresh);
            refresh();
        }

        setupKategoriSelect('#import-jenis', '#import-kategori');
        setupKategoriSelect('#lokasi-jenis', '#lokasi-kategori');

        /* ---------- Import ---------- */
        $('#bt-download-template').on('click', function () {
            const jenis = $('#import-jenis').val();
            const kategori = $('#import-kategori').val();

            if (!jenis || !kategori) {
                Swal.fire('Perhatian', 'Pilih Jenis dan Kategori terlebih dahulu.', 'warning');

                return;
            }

            const params = new URLSearchParams({ jenis: jenis, kategori: kategori });
            window.location.href = `${config.urls.template}?${params.toString()}`;
        });

        $('#form-import').on('submit', function (e) {
            const jenis = $('#import-jenis').val();
            const kategori = $('#import-kategori').val();
            const file = $('#import-file').val();

            if (!jenis || !kategori || !file) {
                e.preventDefault();
                Swal.fire('Perhatian', 'Lengkapi Jenis, Kategori, dan File template.', 'warning');

                return;
            }

            Swal.fire({
                title: 'Mengimpor',
                text: 'Mohon tunggu, data sedang diproses.',
                allowOutsideClick: false,
                didOpen: function () {
                    Swal.showLoading();
                },
            });
        });

        /* ---------- CRUD manual ---------- */
        function resolveJenisKategoriIds(data) {
            const daftar = config.jenisKategori || [];
            const jenisRaw = String(data.jenis_id ?? data.jenis ?? '');
            const kategoriRaw = String(data.kategori_id ?? data.kategori ?? data.ref_point ?? '');

            let jenisItem = daftar.find(function (item) {
                return String(item.id) === jenisRaw;
            }) || daftar.find(function (item) {
                return String(item.nama).toLowerCase() === jenisRaw.toLowerCase();
            });

            if (!jenisItem && kategoriRaw !== '') {
                jenisItem = daftar.find(function (item) {
                    return (item.children || []).some(function (child) {
                        return String(child.id) === kategoriRaw;
                    });
                });
            }

            if (!jenisItem) {
                return { jenis: jenisRaw, kategori: kategoriRaw };
            }

            const child = (jenisItem.children || []).find(function (item) {
                return String(item.id) === kategoriRaw
                    || String(item.nama).toLowerCase() === kategoriRaw.toLowerCase();
            });

            return {
                jenis: String(jenisItem.id),
                kategori: child ? String(child.id) : '',
            };
        }

        function resolveKodeDesa(data) {
            if (data.kode_desa) {
                return String(data.kode_desa);
            }

            if (data.config_id) {
                const byId = (config.desaList || []).find(function (item) {
                    return String(item.id) === String(data.config_id);
                });

                if (byId) {
                    return String(byId.kode_desa);
                }
            }

            const nama = String(data.nama_desa || '').toLowerCase();
            const match = (config.desaList || []).find(function (item) {
                return String(item.nama_desa).toLowerCase() === nama;
            });

            return match ? String(match.kode_desa) : '';
        }

        function resetLokasiForm() {
            const desaDefault = (config.desa && config.desa.kode_desa) || '';

            $('#modal-form-label').text('Tambah Data Lokasi');
            $('#form-lokasi').attr('action', config.urls.store);
            $('#lokasi-method').val('POST');
            $('#lokasi-id').val('');
            $('#lokasi-nama').val('');
            $('#lokasi-lat').val('');
            $('#lokasi-lng').val('');
            $('#lokasi-keterangan').val('');
            $('#lokasi-kode-desa').val(desaDefault);
            $('#lokasi-jenis').val('').trigger('change');
        }

        $('#bt-tambah').on('click', resetLokasiForm);

        $(document).on('click', '.btn-edit', function () {
            const id = $(this).data('id');

            $.ajax({
                url: `${config.urls.detail}/${id}`,
                method: 'GET',
                headers: { Accept: 'application/json' },
                success: function (response) {
                    const data = (response && response.data) ? response.data : {};

                    $('#modal-form-label').text('Ubah Data Lokasi');
                    $('#form-lokasi').attr('action', `${config.urls.update}/${id}`);
                    $('#lokasi-method').val('PUT');
                    $('#lokasi-id').val(id);
                    $('#lokasi-nama').val(data.nama || '');
                    $('#lokasi-lat').val(data.lat || '');
                    $('#lokasi-lng').val(data.lng || '');
                    $('#lokasi-keterangan').val(data.keterangan ?? data.desk ?? '');
                    $('#lokasi-kode-desa').val(resolveKodeDesa(data));

                    const jenisKategori = resolveJenisKategoriIds(data);

                    $('#lokasi-jenis').val(jenisKategori.jenis).trigger('change');
                    $('#lokasi-kategori').val(jenisKategori.kategori).trigger('change');

                    $('#modal-form').modal('show');
                },
                error: function () {
                    Swal.fire('Error', 'Gagal mengambil data lokasi.', 'error');
                },
            });
        });

        $(document).on('click', 'button.hapus', function () {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Hapus',
                text: 'Apakah anda yakin menghapus data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                const form = $('<form>', { method: 'POST', action: `${config.urls.destroy}/${id}` });
                form.append($('<input>', { type: 'hidden', name: '_token', value: config.csrf }));
                form.append($('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }));
                $('body').append(form);
                form.trigger('submit');
            });
        });

        /* ---------- Hapus massal (halaman aktif) ---------- */
        $('#check-all').on('change', function () {
            $('.row-check').prop('checked', $(this).prop('checked'));
        });

        $('#bt-bulk-delete').on('click', function () {
            const ids = $('.row-check:checked')
                .map(function () {
                    return $(this).val();
                })
                .get()
                .filter(Boolean);

            if (ids.length === 0) {
                Swal.fire('Perhatian', 'Pilih minimal satu data lokasi.', 'warning');

                return;
            }

            Swal.fire({
                title: 'Hapus',
                text: `Hapus ${ids.length} data lokasi terpilih?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal',
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                const form = $('<form>', { method: 'POST', action: config.urls.bulkDestroy });
                form.append($('<input>', { type: 'hidden', name: '_token', value: config.csrf }));
                form.append($('<input>', { type: 'hidden', name: '_method', value: 'DELETE' }));
                ids.forEach(function (id) {
                    form.append($('<input>', { type: 'hidden', name: 'ids[]', value: id }));
                });
                $('body').append(form);
                form.trigger('submit');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
