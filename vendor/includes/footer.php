
  </div> <!-- /.container -->
  <!-- Place this outside the container (for full width) -->
  <footer class="bg-light text-center text-muted small py-3 mt-auto shadow-lg w-100 fixed-bottom">
    <div class="container">
      © 2025 | <i class="fas fa-calendar-day"></i> <?= date('d-M-Y') ?>
    </div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    msg_m = document.getElementById("msg-modal");
    msg_b = document.getElementById("msg-backdrop");
    function close_modal(){
      msg_m.classList.remove('d-block');
      msg_b.classList.remove('show');
      msg_b.classList.add('d-none');
    }
  </script>
</body>
</html>
