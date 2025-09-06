<!-- Name Field -->
<div class="form-group row">
    <div class="col-12">
        {!! Html::text('text')->class('form-control item-menu')->attribute('maxlength', 255)->attribute('placeholder', 'Nama Menu') !!}<br>
    </div>
</div>
<div class="form-group row">
    <div class="col-12">
        <!-- Icon Field as Dropdown -->
        {!! Html::select(
            'icon',
            [
                '' => 'Pilih Ikon', // Pilihan kosong di awal
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
        )->class('form-control item-menu') !!}
    </div>
</div>
<!-- Asal url -->
<div class="form-group row">
    <div class="col-12">
        <label class="form-check-inline">
            {!! Html::radio('source')->value('link')->class('form-check-input')->checked(old('source', $groupMenu->source ?? 'link') == 'link') !!} Link
        </label>
        <label class="form-check-inline">
            {!! Html::radio('source')->value('Modul')->class('form-check-input')->checked(old('source', $groupMenu->source ?? 'link') == 'Modul') !!} Modul
        </label>
    </div>
</div>

<!-- Url Field -->
<div class="form-group row">
    <div class="col-12">
        {!! Html::select('sourcelist', [])->class('form-control') !!}
        {!! Html::text('href')->class('form-control item-menu')->attribute('maxlength', 255)->attribute('placeholder', 'http://contoh.com') !!}
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

            $('select[name=sourcelist]').change(function() {
                let _val = $(this).val()
                $('input[name=href]').val(_val)
            })

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
        })
    </script>
@endpush
