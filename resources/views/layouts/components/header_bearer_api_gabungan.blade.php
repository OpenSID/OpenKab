{
    'X-CSRF-TOKEN': "{{ csrf_token() }}",
    {{-- 'Content-Type': 'application/json', --}}
    'Accept': 'application/json',
    'Authorization': 'Bearer {{ cache()->get('user_token_'.auth()->id()) }}'
}