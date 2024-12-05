<button type="reset" class="btn btn-danger btn-sm" onclick="resetForm();">
    <i class="fa fa-times"></i> Batal
</button>

<script>
function resetForm() {
    const form = document.getElementById('formSuplemen');
    if (form) {
        form.reset();
    }
}
</script>
