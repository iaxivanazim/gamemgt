<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // SUCCESS MESSAGE
        @if(session('success'))
        Swal.fire({
            icon: 'success'
            , title: 'Success'
            , text: "{{ session('success') }}"
            , timer: 2000
            , showConfirmButton: false
        });
        @endif

        // ERROR MESSAGE
        @if(session('error'))
        Swal.fire({
            icon: 'error'
            , title: 'Error'
            , text: "{{ session('error') }}"
        });
        @endif

        // VALIDATION ERRORS
        @if($errors -> any())
        Swal.fire({
            icon: 'error'
            , title: 'Validation Error'
            , html: `{!! implode('<br>', $errors->all()) !!}`
        });
        @endif

    });

</script>



<script>
    function deleteTable(id) {
        Swal.fire({
            title: 'Are you sure?'
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Yes, delete it!'
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
            title: 'Are you sure?'
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonText: 'Yes, delete it!'
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
            title: 'Delete Game Type?'
            , icon: 'warning'
            , showCancelButton: true
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
            title: 'Delete Theme?'
            , icon: 'warning'
            , showCancelButton: true
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
            behavior: 'smooth'
            , block: 'start'
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
            chip_1_value: form.find('[name="chip1"]').val()
            , chip_2_value: form.find('[name="chip2"]').val()
            , chip_3_value: form.find('[name="chip3"]').val()
            , chip_4_value: form.find('[name="chip4"]').val()
            , chip_5_value: form.find('[name="chip5"]').val()
            , base_value: form.find('[name="base_value"]').val()
            , _token: token
        };

        const url = id ? '/chips/' + id : '/chips';

        $.post(url, data, function(res) {
            Swal.fire({
                icon: 'success'
                , title: res.message
            }).then(() => location.reload());
        });
    });

    // Delete a preset (soft delete → status 0)
    function deletePreset(id) {
        if (!id) return;
        Swal.fire({
            title: 'Delete preset?'
            , icon: 'warning'
            , showCancelButton: true
            , confirmButtonColor: '#dc3545'
        }).then((r) => {
            if (r.isConfirmed) {
                $.post('/chips/delete/' + id, {
                    _token: '{{ csrf_token() }}'
                }, function(res) {
                    Swal.fire({
                        icon: 'success'
                        , title: res.message
                        , timer: 1500
                        , showConfirmButton: false
                    }).then(() => location.reload());
                });
            }
        });
    }

    // Restore a preset (status → 1)
    function restorePreset(id) {
        if (!id) return;
        Swal.fire({
            title: 'Restore preset?'
            , icon: 'question'
            , showCancelButton: true
            , confirmButtonColor: '#198754'
        }).then((r) => {
            if (r.isConfirmed) {
                $.post('/chips/restore/' + id, {
                    _token: '{{ csrf_token() }}'
                }, function(res) {
                    Swal.fire({
                        icon: 'success'
                        , title: res.message
                        , timer: 1500
                        , showConfirmButton: false
                    }).then(() => location.reload());
                });
            }
        });
    }

</script>

<script>
    const gameTypeSelect = document.getElementById('gameTypeSelect');
    const chipPresetSelect = document.getElementById('chipPresetSelect');

    // ── Game type change ──────────────────────────────────────────────────
    gameTypeSelect.addEventListener('change', function() {
        const code = this.options[this.selectedIndex] ? this.options[this.selectedIndex].dataset.code : null;
console.log('Selected game type code:', code);
        // hide all game field panels
        document.querySelectorAll('.game-fields').forEach(el => el.style.display = 'none');

        if (code) {
            document.getElementById('gameConfigSection').style.display = 'block';
            document.getElementById('payoutSection').style.display = 'block';

            const panel = document.getElementById('fields-' + code);
            if (panel) panel.style.display = 'block';

            // load payout rules
            console.log('Loading payout rules for game type ID:', this.value);
            loadPayoutRules(this.value);
        } else {
            document.getElementById('gameConfigSection').style.display = 'none';
            document.getElementById('payoutSection').style.display = 'none';
        }
    });

    // ── Chip preset preview ───────────────────────────────────────────────
    chipPresetSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const chips = JSON.parse(opt.dataset.chips || '[]');
        const base = opt.dataset.base || '—';

        document.querySelectorAll('.chip-preview-val').forEach((el, i) => {
            el.textContent = chips[i] !== undefined ? chips[i] : '—';
        });
        document.getElementById('basePreview').textContent = base;
    });

    // ── Felt color label ──────────────────────────────────────────────────
    document.getElementById('feltColor').addEventListener('input', function() {
        document.getElementById('feltColorLabel').textContent = this.value;
    });

    // ── Load payout rules via AJAX ────────────────────────────────────────
    function loadPayoutRules(gameTypeId) {
    $.get('/api/v1/payout-rules/' + gameTypeId, function (rules) {
        const tbody = document.getElementById('payoutRulesBody');

        if (!rules || !rules.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-muted">
                        No payout rules defined for this game.
                    </td>
                </tr>`;
            return;
        }

        tbody.innerHTML = rules.map(rule => `
    <tr>
        <td class="text-light">${rule.bet_name}</td>
        <td class="text-light">${rule.bet_position ?? '—'}</td>
        <td class="text-warning fw-bold">${rule.payout_multiplier}x</td>
        <td>
            <div class="form-check form-switch d-flex justify-content-center">
                <input class="form-check-input payout-toggle"
                       type="checkbox"
                       name="payout_overrides[${rule.payout_id}]"
                       value="1"
                       data-position="${rule.bet_position}"
                       ${rule.is_active ? 'checked' : ''}>
            </div>
        </td>
    </tr>
`).join('');

// after rendering — init B6 toggle state
syncB6State();
    }).fail(function () {
        document.getElementById('payoutRulesBody').innerHTML = `
            <tr>
                <td colspan="4" class="text-danger">Failed to load payout rules.</td>
            </tr>`;
    });
}

document.getElementById('tableName').addEventListener('input', function () {
    document.getElementById('configName').value = this.value;
});

// ── Min/Max pairs to validate ─────────────────────────────────────────
const minMaxPairs = [
    { min: 'minBet',    max: 'maxBet',    label: 'Bet'        },
    { min: 'sideMinBet',max: 'sideMaxBet',label: 'Side Bet'   },
    { min: 'tieMin',    max: 'tieMax',    label: 'Tie'        },
    { min: 'sideMin',   max: 'sideMax',   label: 'Side'       },
    { min: 'pairMin',   max: 'pairMax',   label: 'Pair'       },
    { min: 'hlMin',     max: 'hlMax',     label: 'H/L'        },
];

// ── Attach live validation on each field ─────────────────────────────
minMaxPairs.forEach(({ min, max, label }) => {
    const minEl = document.getElementById(min);
    const maxEl = document.getElementById(max);
    if (!minEl || !maxEl) return;

    const validate = () => {
        const minVal = parseFloat(minEl.value);
        const maxVal = parseFloat(maxEl.value);

        if (minEl.value === '' || maxEl.value === '') {
            clearError(minEl);
            clearError(maxEl);
            return true;
        }

        if (minVal <= 0) {
            setError(minEl, `${label} min must be greater than 0`);
        } else {
            clearError(minEl);
        }

        if (maxVal <= 0) {
            setError(maxEl, `${label} max must be greater than 0`);
        } else if (maxVal <= minVal) {
            setError(maxEl, `${label} max must be greater than min`);
        } else {
            clearError(maxEl);
        }
    };

    minEl.addEventListener('input', validate);
    maxEl.addEventListener('input', validate);
});

// ── Helpers ───────────────────────────────────────────────────────────
function setError(el, message) {
    el.classList.add('is-invalid');
    el.classList.remove('is-valid');

    // create feedback div if it doesn't exist
    let feedback = el.nextElementSibling;
    if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.classList.add('invalid-feedback');
        el.parentNode.insertBefore(feedback, el.nextSibling);
    }
    feedback.textContent = message;
}

function clearError(el) {
    el.classList.remove('is-invalid');
    el.classList.add('is-valid');

    const feedback = el.nextElementSibling;
    if (feedback && feedback.classList.contains('invalid-feedback')) {
        feedback.textContent = '';
    }
}

// ── Block form submit if any pair is invalid ──────────────────────────
document.getElementById('masterForm').addEventListener('submit', function (e) {
    let hasError = false;

    minMaxPairs.forEach(({ min, max, label }) => {
        const minEl = document.getElementById(min);
        const maxEl = document.getElementById(max);
        if (!minEl || !maxEl) return;
        if (minEl.value === '' && maxEl.value === '') return; // skip empty optional pairs

        const minVal = parseFloat(minEl.value);
        const maxVal = parseFloat(maxEl.value);

        if (minVal <= 0) {
            setError(minEl, `${label} min must be greater than 0`);
            hasError = true;
        }
        if (maxVal <= 0) {
            setError(maxEl, `${label} max must be greater than 0`);
            hasError = true;
        } else if (maxVal <= minVal) {
            setError(maxEl, `${label} max must be greater than min (${minVal})`);
            hasError = true;
        }
    });

    if (hasError) {
        e.preventDefault();

        // scroll to first error
        const firstError = document.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }

        Swal.fire({
            icon : 'error',
            title: 'Validation Error',
            text : 'Please fix the min/max fields before submitting.',
            confirmButtonColor: '#ffc107'
        });
    }
});

// ── Baccarat 6 commission hint ────────────────────────────────────────
const b6Select = document.getElementById('b6CommissionSelect');
if (b6Select) {
    b6Select.addEventListener('change', function () {
        document.getElementById('b6Multiplier').textContent =
            this.value == 1 ? '0.95x' : '0.50x';
    });
}

// ── Sync B6 dropdown state from payout toggle ─────────────────────────
function syncB6State() {
    const b6Toggle = document.querySelector('.payout-toggle[data-position="B6"]');
    if (!b6Toggle) return;

    const isActive   = b6Toggle.checked;
    const b6Select   = document.getElementById('b6CommissionSelect');
    const b6Badge    = document.getElementById('b6CommissionBadge');
    const b6Hint     = document.getElementById('b6Multiplier');

    if (!b6Select) return;

    if (isActive) {
        // enable
        b6Select.disabled                   = false;
        b6Select.style.color                = '#fff';
        b6Select.style.background           = '#0a1a0a';
        b6Select.style.cursor               = 'pointer';
        b6Badge.textContent                 = 'B6 Active';
        b6Badge.style.background            = '#0f2e1a';
        b6Badge.style.color                 = '#6fcf97';
        b6Badge.style.border                = '1px solid #6fcf97';
        b6Hint.textContent                  = b6Select.value == 1 ? '0.95x' : '0.50x';
    } else {
        // disable
        b6Select.disabled                   = true;
        b6Select.style.color                = '#555';
        b6Select.style.background           = '#111';
        b6Select.style.cursor               = 'not-allowed';
        b6Badge.textContent                 = 'B6 Inactive';
        b6Badge.style.background            = '#2e1010';
        b6Badge.style.color                 = '#eb5757';
        b6Badge.style.border                = '1px solid #eb5757';
        b6Hint.textContent                  = '—';
    }
}

// ── Listen for B6 payout toggle change ───────────────────────────────
// Use event delegation since payout rows may be dynamically rendered
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('payout-toggle') &&
        e.target.dataset.position === 'B6') {
        syncB6State();
    }
});

// Init on page load (edit page)
document.addEventListener('DOMContentLoaded', syncB6State);

</script>
