<script>
    document.addEventListener('DOMContentLoaded', function() {

        // SUCCESS MESSAGE
        @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
        @endif

        // ERROR MESSAGE
        @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}"
        });
        @endif

        // VALIDATION ERRORS
        @if($errors -> any())
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `{!! implode('<br>', $errors->all()) !!}`
        });
        @endif

    });
</script>

@if($currentGameDay)
<script>
    let startTime = new Date("{{ $currentGameDay->started_at }}");

    setInterval(() => {
        let now = new Date();
        let diff = Math.floor((now - startTime) / 1000);

        let hours = Math.floor(diff / 3600);
        let minutes = Math.floor((diff % 3600) / 60);

        document.getElementById('gameDayTimer').innerHTML =
            `Running: ${hours}h ${minutes}m`;
    }, 60000);
</script>
@endif

<script>
    function startGameDay() {
        Swal.fire({
            title: 'Start Game Day?',
            text: "This will begin a new gaming cycle.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Start',
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post("{{ route('game-day.start') }}")
                    .then(response => {
                        Swal.fire('Started!', response.data.message, 'success')
                            .then(() => location.reload());
                    })
                    .catch(error => {
                        Swal.fire('Error!', error.response.data.message ?? 'Something went wrong.', 'error');
                    });
            }
        });
    }

    function closeGameDay(id) {
        Swal.fire({
            title: 'Close Game Day?',
            text: "Ensure all tables are reconciled before closing.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Close',
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/game-day/${id}/close`)
                    .then(response => {
                        Swal.fire('Closed!', response.data.message, 'success')
                            .then(() => location.reload());
                    })
                    .catch(error => {
                        Swal.fire('Error!', error.response.data.message ?? 'Something went wrong.', 'error');
                    });
            }
        });
    }
</script>

<script>
    function deleteTable(id) {
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/game-tables/${id}`)
                    .then(() => {
                        Swal.fire('Deleted!', '', 'success')
                            .then(() => location.reload());
                    });
            }
        });
    }
</script>

<script>
    function deleteTheme(id) {
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/themes/${id}`)
                    .then(() => {
                        Swal.fire('Deleted!', '', 'success')
                            .then(() => location.reload());
                    });
            }
        });
    }
</script>

<script>
    function deleteType(id) {
        Swal.fire({
            title: 'Delete Game Type?',
            icon: 'warning',
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/game-types/${id}`)
                    .then(() => location.reload());
            }
        });
    }
</script>

<script>
    function deleteTheme(id) {
        Swal.fire({
            title: 'Delete Theme?',
            icon: 'warning',
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                axios.delete(`/themes/${id}`)
                    .then(() => location.reload());
            }
        });
    }
</script>