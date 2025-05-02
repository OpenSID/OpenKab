<!-- Name Field -->
<div class="form-group row">
    <div class="col-12">
        {!! Form::select('menu_type', ['1' => 'Default', '2' => 'Presisi'], '1', ['class' => 'form-control']) !!}<br>
        {!! Form::text('text', null, [
            'class' => 'form-control item-menu',
            'maxlength' => 255,
            'placeholder' => 'Nama Menu',
        ]) !!}<br>

        <!-- Icon Field as Dropdown -->
        {!! Form::select(
            'icon',
            [
                '' => 'Select Icon', // Pilihan kosong di awal
                'fas fa-list' => 'List',
                'fas fa-home' => 'Home',
                'fas fa-user' => 'User',
                'far fa-circle' => 'Circle',
                'fas fa-cog' => 'Settings',
                'fas fa-envelope' => 'Mail',
                'fas fa-car' => 'Car',
                'fas fa-tree' => 'Tree',
                'fas fa-star' => 'Star',
                'fas fa-bell' => 'Bell',
                'fas fa-chart-line' => 'Chart Line',
                'fas fa-chart-pie' => 'Chart Pie',
                'fas fa-chart-bar' => 'Chart Bar',
                'fas fa-table' => 'Table',
                'fas fa-tags' => 'Tags',
                'fas fa-comments' => 'Comments',
                'fas fa-file-alt' => 'File Alt',
                'fas fa-fingerprint' => 'Fingerprint',
                // Additional valid icons
                'fas fa-edit' => 'Edit',
                'fas fa-trash' => 'Trash',
                'fas fa-save' => 'Save',
                'fas fa-search' => 'Search',
                'fas fa-plus' => 'Plus',
                'fas fa-minus' => 'Minus',
                'fas fa-arrow-up' => 'Arrow Up',
                'fas fa-arrow-down' => 'Arrow Down',
                'fas fa-arrow-left' => 'Arrow Left',
                'fas fa-arrow-right' => 'Arrow Right',
                'fas fa-check' => 'Check',
                'fas fa-times' => 'Times (Close)',
                'fas fa-lock' => 'Lock',
                'fas fa-unlock' => 'Unlock',
                'fas fa-calendar' => 'Calendar',
                'fas fa-map' => 'Map',
                'fas fa-clock' => 'Clock',
                'fas fa-camera' => 'Camera',
                'fas fa-globe' => 'Globe',
                'fas fa-heart' => 'Heart',
                'fas fa-shopping-cart' => 'Shopping Cart',
            ],
            '',
            ['class' => 'form-control item-menu'],
        ) !!}
    </div>
</div>

<!-- Asal url -->
<div class="form-group row">
    <div class="col-12">
        <label class="form-check-inline">
            {!! Form::radio('source', 'link', 'link', ['class' => 'form-check-input']) !!} Link
        </label>
        <label class="form-check-inline">
            {!! Form::radio('source', 'Halaman', null, ['class' => 'form-check-input']) !!} Halaman
        </label>
        <label class="form-check-inline">
            {!! Form::radio('source', 'Kategori', null, ['class' => 'form-check-input']) !!} Kategori
        </label>
        <label class="form-check-inline">
            {!! Form::radio('source', 'Modul', null, ['class' => 'form-check-input']) !!} Modul
        </label>
    </div>
</div>

<!-- Url Field -->
<div class="form-group row">
    <div class="col-12">
        {!! Form::select('sourcelist', $sourceItem, null, ['class' => 'form-control']) !!}
        {!! Form::text('href', null, [
            'class' => 'form-control item-menu',
            'maxlength' => 255,
            'placeholder' => 'http://contoh.com',
        ]) !!}
    </div>
</div>

<div class="form-group row">
    <div class="col-12">
        {!! Form::select('penduduk', $sourceItem['penduduk'], null, [
            'class' => 'form-control',
            'style' => 'display:none;',
        ]) !!}
        {!! Form::select('keluarga', $sourceItem['keluarga'], null, [
            'class' => 'form-control',
            'style' => 'display:none;',
        ]) !!}
        {!! Form::select('bantuan', $sourceItem['bantuan'], null, [
            'class' => 'form-control',
            'style' => 'display:none;',
        ]) !!}
        {!! Form::select('rtm', $sourceItem['rtm'], null, ['class' => 'form-control', 'style' => 'display:none;']) !!}
        {!! Form::select('kesehatan', $sourceItem['kesehatan'], null, [
            'class' => 'form-control',
            'style' => 'display:none;',
        ]) !!}
    </div>
</div>

@push('js')
    <script nonce="{{ csp_nonce() }}">
        document.addEventListener("DOMContentLoaded", function(event) {
            $('select[name=sourcelist]').hide()
            $(':radio[name=source]').change(function() {
                let _val = $(this).val()
                $('input[name=href]').val('')
                $('input[name=href]').hide()
                $('select[name=sourcelist]').hide()
                switch (_val) {
                    case 'Kategori':
                    case 'Halaman':
                    case 'Modul':
                        $('select[name=sourcelist]').show()
                        $('select[name=sourcelist]').find('optgroup').hide()
                        $('select[name=sourcelist]').find('optgroup[label="' + _val + '"]').show()
                        $('select[name=sourcelist]').find('optgroup[label="' + _val + '"]').find(
                            'option:first').prop('selected', 1)
                        $('select[name=sourcelist]').trigger('change')
                        break;
                    default:
                        $('input[name=href]').show()
                }
            })

            $('select[name=sourcelist]').on('change', function() {
                let val = $(this).val();
                $('input[name=href]').val(val);
                $('select[name=penduduk], select[name=keluarga], select[name=bantuan], select[name=rtm], select[name=kesehatan]')
                    .hide();

                switch ($(this).val()) {
                    case 'statistik-penduduk':
                        val = $('select[name=penduduk]').val();
                        $('select[name=penduduk]').show();
                        break;
                    case 'statistik-keluarga':
                        val = $('select[name=keluarga]').val();
                        $('select[name=keluarga]').show();
                        break;
                    case 'statistik-bantuan':
                        val = $('select[name=bantuan]').val();
                        $('select[name=bantuan]').show();
                        break;
                    case 'statistik-rtm':
                        val = $('select[name=rtm]').val();
                        $('select[name=rtm]').show();
                        break;
                    case 'statistik-kesehatan':
                        val = $('select[name=kesehatan]').val();
                        $('select[name=kesehatan]').show();
                        break;
                    default:
                        break;
                }
            })

            $('select[name=menu_type]').on('change', function() {
                window.location.href = "{{ url('cms/menus') }}?type=" + this.value;
            })

            const queryString = window.location.search;
            const urlParams = new URLSearchParams(queryString);
            const menutype = urlParams.get('type')
            $('select[name=menu_type]').val((menutype == null ? 1 : menutype));
            $('select[name=penduduk], select[name=keluarga], select[name=bantuan], select[name=rtm], select[name=kesehatan]')
                .on('change', function() {
                    updateHref();
                });

            function updateHref() {
                const visibleSelect = $('select[name=sourcelist]').val().replace('statistik-', '');
                $('input[name=href]').val($('select[name=' + visibleSelect + ']').val());
            }
            const formatText = function(icon) {
                if (!icon.id) {
                    return icon.text;
                }
                return $('<span><i class="' + icon.id + '"></i> ' + icon.text + '</span>')
            }
            const _optionIcon = {
                theme: 'bootstrap4',
                placeholder: 'Pilih Icon',
                allowClear: true,
                templateResult: formatText,
                templateSelection: formatText,
                width: '100%',
            };
            $('select[name=icon]').select2(_optionIcon);
            $(':radio[name=source]').trigger('change')
            $(':radio[name=source]:first').trigger('change')
        })
    </script>
@endpush
