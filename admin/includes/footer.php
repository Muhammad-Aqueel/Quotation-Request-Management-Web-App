    </div> <!-- /.container -->
    <!-- Place this outside the container (for full width) -->
    <footer class="bg-light text-center text-muted small py-3 mt-auto shadow-lg w-100 fixed-bottom">
        <div class="container">
            © 2025 | <i class="fas fa-calendar-day"></i> <?= date('d-M-Y') ?>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- DataTables JS + Bootstrap Integration -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
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

        // Start polling
        setInterval(pollNewQuotes, 5000); // Every 5 seconds
        
        function toggleAll(source) {
            document.querySelectorAll('input[name="quotation_ids[]"], input[name="request_ids[]"]').forEach(cb => cb.checked = source.checked);
        }

        function fetchNotifications() {
            fetch('ajax/notifications.php')
                .then(res => res.json())
                .then(data => {
                const count = data.length;
                const badge = document.getElementById('noti-count');
                const list = document.getElementById('notifications-list');

                badge.textContent = count ? count : '';
                list.innerHTML = '';

                if (data.length === 0) {
                    list.innerHTML = '<li class="dropdown-item text-muted small">No new notification.</li>';
                    return;
                }

                data.forEach(n => {
                    const li = document.createElement('li');
                    li.className = 'dropdown-item';
                    li.innerHTML = `
                    <strong>${n.name}</strong> - ${n.company}<br>
                    <small>${n.submitted_at}</small><br>
                    <a href="view_quotation.php?id=${n.id}" class="nav_active_link small">View Quotation</a>
                    `;
                    list.appendChild(li);
                });
            });
        }

        setInterval(fetchNotifications, 5000); // update every 5s
        fetchNotifications();

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

        setInterval(fetchStats, 5000); // update every 5s
        fetchStats();

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

        function deleteRequestItem(itemId, requestId, btn) {
            if (document.querySelectorAll('#items .item-row').length <= 1) {
                alert("At least one item is required.");
                return;
            }
            
            if(itemId == 0, requestId == 0){
                const container = document.getElementById('items');
                if (container.children.length > 1) {
                    btn.parentElement.remove();
                }
                return;
            }

            if (!confirm("Delete this item?")) return;

            fetch('ajax/delete_request_item.php', {
                method: 'POST',
                headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `item_id=${itemId}&request_id=${requestId}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                const row = document.getElementById('item-' + itemId);
                if (row) row.remove();
                } else {
                alert(data.message || 'Failed to delete item.');
                }
            })
            .catch(() => alert('Server error while deleting item.'));
        }

        function close_modal(){
            msg_m = document.getElementById("msg-modal");
            msg_b = document.getElementById("msg-backdrop");
            msg_m.classList.remove('d-block');
            msg_b.classList.remove('show');
            msg_b.classList.add('d-none');
        }
    </script>
</body>
</html>
