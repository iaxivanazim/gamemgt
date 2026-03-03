<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

<!-- payout rules -->
<script>
    let gameTypeID = null;




    $('.gameTypeBtn').click(function() {

        $('.gameTypeBtn').removeClass('active bg-warning text-dark');

        $(this).addClass('active bg-warning text-dark');

        gameTypeID = $(this).data('id');

        loadRules();

    });



    function loadRules() {

        $.get('/payout_rules/fetch/' + gameTypeID, function(data) {

            let html = '';

            data.forEach(r => {

                html += `

<tr>

<td>

<input class="form-control"
value="${r.bet_name}"
onchange="updateRule(${r.payout_id},this.value,'bet_name')">

</td>


<td>

<input class="form-control"
value="${r.bet_position}"
onchange="updateRule(${r.payout_id},this.value,'bet_position')">

</td>


<td>

<input class="form-control"
value="${r.payout_multiplier}"
onchange="updateRule(${r.payout_id},this.value,'payout_multiplier')">

</td>


<td>

<select
onchange="updateRule(${r.payout_id},this.value,'is_active')"
class="form-control">

<option value="1"
${r.is_active==1?'selected':''}>
Active
</option>

<option value="0"
${r.is_active==0?'selected':''}>
Inactive
</option>

</select>

</td>


<td>

<button
class="btn btn-danger btn-sm"
onclick="deleteRule(${r.payout_id})">
Delete
</button>

</td>

</tr>

`;

            });

            $('#rulesTable').html(html);

        });

    }



    $('#addRuleBtn').click(function() {

        if (!gameTypeID) {
            Swal.fire('Select Game Type First');
            return;
        }

        Swal.fire({

            title: 'Add Payout Rule',

            html: `

<input id="bet_name"
class="swal2-input"
placeholder="Bet Name">

<input id="bet_position"
class="swal2-input"
placeholder="Position">

<input id="multiplier"
class="swal2-input"
placeholder="Multiplier">

`,

            preConfirm: () => {

                $.post('/payout_rules/store', {

                    game_type_id: gameTypeID,

                    bet_name: $('#bet_name').val(),

                    bet_position: $('#bet_position').val(),

                    payout_multiplier: $('#multiplier').val(),

                    is_active: 1,

                    _token: '{{csrf_token()}}'

                }, function() {

                    loadRules();

                });

            }

        });

    });



    function updateRule(id, value, column) {

        $.post('/payout_rules/update/' + id, {

            [column]: value,

            _token: '{{csrf_token()}}'

        });

    }



    function deleteRule(id) {

        Swal.fire({

            title: 'Delete?',

            showCancelButton: true

        }).then(res => {

            if (res.isConfirmed) {

                $.ajax({

                    url: '/payout_rules/delete/' + id,

                    type: 'DELETE',

                    data: {
                        _token: '{{csrf_token()}}'
                    },

                    success: function() {

                        loadRules();

                    }

                });

            }

        });

    }
</script>

<!-- Chips JS -->
<script>
    // Show new preset card
    function newPreset() {
        const card = document.getElementById('newPresetCard');
        card.style.setProperty('display', 'block', 'important');
        card.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    // Hide new preset card
    function cancelNew() {
        document.getElementById('newPresetCard').style.setProperty('display', 'none', 'important');
    }

    // Handle all forms (new + existing presets)
    $(document).on('submit', '.chipForm', function(e) {
        e.preventDefault();

        const form = $(this);
        const id = form.data('id');
        const token = form.find('input[name="_token"]').val();

        const data = {
            chip_1_value: form.find('[name="chip1"]').val(),
            chip_2_value: form.find('[name="chip2"]').val(),
            chip_3_value: form.find('[name="chip3"]').val(),
            chip_4_value: form.find('[name="chip4"]').val(),
            chip_5_value: form.find('[name="chip5"]').val(),
            base_value: form.find('[name="base_value"]').val(),
            _token: token
        };

        const url = id ? '/chips/' + id : '/chips';

        $.post(url, data, function(res) {
            Swal.fire({
                icon: 'success',
                title: res.message
            }).then(() => location.reload());
        });
    });

    // Delete a preset (soft delete → status 0)
    function deletePreset(id) {
        if (!id) return;
        Swal.fire({
            title: 'Delete preset?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545'
        }).then((r) => {
            if (r.isConfirmed) {
                $.post('/chips/delete/' + id, {
                    _token: '{{ csrf_token() }}'
                }, function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                });
            }
        });
    }

    // Restore a preset (status → 1)
    function restorePreset(id) {
        if (!id) return;
        Swal.fire({
            title: 'Restore preset?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754'
        }).then((r) => {
            if (r.isConfirmed) {
                $.post('/chips/restore/' + id, {
                    _token: '{{ csrf_token() }}'
                }, function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                });
            }
        });
    }
</script>