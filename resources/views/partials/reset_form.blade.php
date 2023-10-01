@push('js')
<script nonce="{{ csp_nonce() }}" type="module">
    $('#reset').click(function(){
        location.reload();
    });
</script>
@endpush
