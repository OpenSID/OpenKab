@push('css')
    <style nonce="{{ csp_nonce() }}">
       .select2-container--default .select2-selection--multiple .select2-selection__choice{
        background-color: #007bff;
        border-color: #007bff;
        color: #ffffff;
       }
       .select2-container--default .select2-selection--multiple .select2-selection__choice__remove{
        border-right: none;
        color: #FFFFFF
       }       
    </style>
@endpush
