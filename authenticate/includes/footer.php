</div>

<!-- Footer -->
<footer class="app-footer">
  <div class="d-flex align-items-center gap-2">
    <i class="fas fa-calendar-day"></i>
    <span><?php date_default_timezone_set('Asia/Karachi'); echo date('d-M-Y'); ?></span>
  </div>
  <div>
    © 2025 RFQ Portal
  </div>
</footer>
</main>

<!-- Scripts -->
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
  // Page-specific JavaScript execution
  <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    echo $current_page === 'dashboard.php' ? 'setInterval(fetchStats, 5000); fetchStats();' : '';
    if ($_SESSION['user_role'] === 'student'){
      echo 'setInterval(fetchNotifications, 5000); fetchNotifications(); setInterval(pollNewQuotes, 5000);';
    }
  ?>
</script>
</body>
</html>