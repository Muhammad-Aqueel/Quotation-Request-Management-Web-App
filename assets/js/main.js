new AirDatepicker('#date-range-picker', {
    range: true,
    multipleDatesSeparator: ' to ',
    // autoClose: true
});

let lastQuoteId = 0;

function pollNewQuotes() {
    fetch('ajax/check_new_quotes.php?last_id=' + lastQuoteId)
        .then(res => res.json())
        .then(data => {
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": true,
                    "positionClass": "toast-bottom-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
            }
            if (typeof toastr !== 'undefined' && data.new_id > lastQuoteId) {
                lastQuoteId = data.new_id;

                // Make sure options are set
                if (!toastr.options) {
                    toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    positionClass: 'toast-top-right',
                    timeOut: 5000
                    };
                }

                toastr.success('A new quotation was just submitted!', 'New Quotation');
                // 🔔 Optionally trigger a sound alert here
                // new Audio('sounds/notify.mp3').play();
            }
    });
}

// Initial fetch
fetch('ajax/check_new_quotes.php?last_id=0')
.then(res => res.json())
.then(data => {
    lastQuoteId = data.new_id;
});

function toggleAll(source) {
    document.querySelectorAll('input[name="quotation_ids[]"], input[name="request_ids[]"]').forEach(cb => cb.checked = source.checked);
}

function fetchStats() {
    fetch('ajax/dashboard_stats.php')
    .then(res => res.json())
    .then(data => {
        document.getElementById('pending_quotes').innerText = data.pending_quotes;
        document.getElementById('submitted_today').innerText = data.submitted_today;
        document.getElementById('month_total').innerText = data.month_total;
        document.getElementById('total_requests').innerText = data.total_requests;
        document.getElementById('vendor_count').innerText = data.vendor_count;
        document.getElementById('inactive_vendor_count').innerText = data.inactive_vendor_count;
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const table = new DataTable('#requestsTable', {
    pageLength: 10,
    lengthMenu: [5, 10, 25, 50, 100],
    ordering: true,
    info: true,
    responsive: true,
    columnDefs: [
        { orderable: false, targets: 0 } // Disable sorting for checkbox column
    ]
    });
});

function deleteAttachment(attachmentId, requestId) {
    if (!confirm('Delete this attachment?')) return;

    fetch('ajax/delete_request_attachment.php', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `id=${attachmentId}&request_id=${requestId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
        const el = document.getElementById('attachment-' + attachmentId);
        if (el) el.remove();
        } else {
        alert(data.message || 'Failed to delete attachment.');
        }
    })
    .catch(() => alert('Server error while deleting attachment.'));
}

function updateSerialNumbers() {
    const rows = document.querySelectorAll('#items .item-row');
    rows.forEach((row, index) => {
    const label = row.querySelector('.item-index');
    if (label) {
        label.textContent = index + 1;
    }
    });
}

function close_modal(){
    msg_m = document.getElementById("msg-modal");
    msg_b = document.getElementById("msg-backdrop");
    msg_m.classList.remove('d-block');
    msg_b.classList.remove('show');
    msg_b.classList.add('d-none');
}

document.addEventListener('DOMContentLoaded', function () {
    const eventDateInput = document.getElementById('date-range-picker');
    
    // Only run if the date picker exists on the page
    if (eventDateInput) {
        const form = eventDateInput.closest('form'); // Get the closest form that contains this input
        if (form) {
            form.addEventListener('submit', function (e) {
                const eventDate = eventDateInput.value.trim();
                if (!eventDate) {
                    e.preventDefault();
                    eventDateInput.focus();
                }
            });
        }
    }
});