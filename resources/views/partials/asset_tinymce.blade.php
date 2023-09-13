@push('css')
@vite(['resources/sass/tinymce.scss'])
@endpush

@push('js')
    @vite(['resources/js/tinymce.js'])
    <script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
    <script nonce="{{ csp_nonce() }}"  >
        document.addEventListener("DOMContentLoaded", function(event) {
            tinymce.baseURL = '{{ asset('vendor/tinymce') }}';
            tinymce.init({
                selector: '.editor',
                setup: (editor) => {
                    editor.on('blur', () => {
                        tinymce.triggerSave()
                    });
                },
                height: 700,
                promotion: false,
                theme: 'silver',
                formats: {
                    menjorok: { block: 'p', styles: { 'text-indent': '30px' }}
                },
                block_formats: 'Paragraph=p; Header 1=h1; Header 2=h2; Header 3=h3; Header 4=h4; Header 5=h5; Header 6=h6; Div=div; Preformatted=pre; Blockquote=blockquote; Menjorok=menjorok',
                style_formats_merge: true,
                plugins: [
                    'advlist', 'autolink', 'link', 'image', 'lists', 'charmap', 'preview', 'anchor', 'pagebreak',
                    'searchreplace', 'wordcount', 'visualblocks', 'visualchars', 'insertdatetime', 'media', 'nonbreaking',
                    'table', 'directionality', 'emoticons'
                ],
                hidden_input: false,
                toolbar1: "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blocks",
                toolbar2: "| link unlink anchor | image media | forecolor backcolor | preview code | fontfamily fontsizeinput",
                image_advtab: true,
                file_picker_callback (callback, value, meta) {
                    let x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth
                    let y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight

                    tinymce.activeEditor.windowManager.openUrl({
                    url : '/file-manager/tinymce5',
                    title : 'Laravel File manager',
                    width : x * 0.8,
                    height : y * 0.8,
                    onMessage: (api, message) => {
                        callback(message.content, { text: message.text })
                    }
                    })
                },
                content_css: false,
                skin: 'tinymce-5',
                relative_urls: false,
                remove_script_host: false
            });
        });
    </script>
@endpush
