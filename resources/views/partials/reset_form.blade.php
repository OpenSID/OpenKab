@push('page_scripts')
<script type="module">
    $('#reset').click(function(){
        $('form')[0].reset();
    });
</script>
@endpush