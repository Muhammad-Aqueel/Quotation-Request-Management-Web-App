    </div> <!-- /.container -->
    <!-- Place this outside the container (for full width) -->
    <footer class="bg-light text-center text-muted small py-3 mt-auto shadow-lg w-100 fixed-bottom d-flex align-items-center">
        <div class="container">
            © 2025 &nbsp;|&nbsp; <i class="fas fa-calendar-day"></i> <?= date('d-M-Y') ?>
        </div>
        <div class="me-3">
            <input type="checkbox" class="modeSwitch" id="modeSwitch">
            <label for="modeSwitch" class="modeSwitch-label">
                <i class="fas fa-moon"></i>
                <i class="fas fa-sun"></i>
                <span class="ball"></span>
            </label>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- DataTables JS + Bootstrap Integration -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- air datepicker JS -->
    <script src="../assets/js/air-datepicker.js"></script>
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/themeswitch.js"></script>
    <script>
        <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            echo $current_page === 'dashboard.php' ? 'setInterval(fetchStats, 5000); // update every 5s
            fetchStats();' : '';
            if ($_SESSION['user_role'] === 'student'){
                echo 'function fetchNotifications() {
                    fetch("ajax/notifications.php")
                        .then(res => res.json())
                        .then(data => {
                        const count = data.length;
                        const badge = document.getElementById("noti-count");
                        const list = document.getElementById("notifications-list");
                
                        badge.textContent = count ? count : "";
                        list.innerHTML = "";
                
                        if (data.length === 0) {
                            list.innerHTML = `<li class="dropdown-item text-muted small">No new notification.</li>`;
                            return;
                        }
                
                        data.forEach(n => {
                            const li = document.createElement("li");
                            li.className = "dropdown-item";
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
                // Start polling
                setInterval(pollNewQuotes, 5000); // Every 5 seconds';
            }
        ?>
    </script>
</body>
</html>
