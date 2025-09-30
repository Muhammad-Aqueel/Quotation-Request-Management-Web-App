  // Sidebar functionality
  document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggleIcon = sidebarToggle?.querySelector('i');
    const sidebarbrandlogo = document.getElementById('sidebar-brand-logo');
    const themeLogo = document.getElementById('themeLogo');

    const STORAGE_KEY = "sidebarmenu-state"; // localStorage key

    // Restore saved state on page load
    const savedState = localStorage.getItem(STORAGE_KEY);
    if (savedState === "collapsed") {
      sidebar.classList.add('collapsed');
      mainContent.classList.add('expanded');
      if (toggleIcon) toggleIcon.className = 'fas fa-chevron-right';
      if (sidebarbrandlogo) {sidebarbrandlogo.classList.add("opacity-0");
        themeLogo.style.width = "0px";
        sidebarbrandlogo.setAttribute("href", "javascript:void(0)")};
    } else {
      sidebar.classList.remove('collapsed');
      mainContent.classList.remove('expanded');
      if (toggleIcon) toggleIcon.className = 'fas fa-chevron-left';
      if (sidebarbrandlogo) {sidebarbrandlogo.classList.remove("opacity-0");
        themeLogo.style.width = "100%";
        sidebarbrandlogo.setAttribute("href", "dashboard.php")};
    }

    // Desktop toggle
    sidebarToggle?.addEventListener('click', function() {
      sidebar.classList.toggle('collapsed');
      mainContent.classList.toggle('expanded');

      if (sidebar.classList.contains('collapsed')) {
        if (toggleIcon) toggleIcon.className = 'fas fa-chevron-right';
        if (sidebarbrandlogo) {sidebarbrandlogo.classList.add("opacity-0");
          themeLogo.style.width = "0px";
          sidebarbrandlogo.setAttribute("href", "javascript:void(0)")};
        localStorage.setItem(STORAGE_KEY, "collapsed"); // save
      } else {
        if (toggleIcon) toggleIcon.className = 'fas fa-chevron-left';
        if (sidebarbrandlogo) {sidebarbrandlogo.classList.remove("opacity-0");
          themeLogo.style.width = "100%";
          sidebarbrandlogo.setAttribute("href", "dashboard.php")};
        localStorage.setItem(STORAGE_KEY, "expanded"); // save
      }
    });

    // Mobile toggle
    mobileToggle?.addEventListener('click', function() {
      sidebar.classList.add('show');
      backdrop.classList.add('show');
      document.body.style.overflow = 'hidden';
    });

    // Close mobile sidebar
    backdrop.addEventListener('click', closeMobileSidebar);

    function closeMobileSidebar() {
      sidebar.classList.remove('show');
      backdrop.classList.remove('show');
      document.body.style.overflow = '';
    }

    // Close mobile sidebar on window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        closeMobileSidebar();
      }
    });
  });

  // All your existing JavaScript functions remain the same
  new AirDatepicker('#date-range-picker', {
    range: true,
    multipleDatesSeparator: ' to ',
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

          if (!toastr.options) {
            toastr.options = {
              closeButton: true,
              progressBar: true,
              positionClass: 'toast-top-right',
              timeOut: 5000
            };
          }

          toastr.success('A new quotation was just submitted!', 'New Quotation');
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
        { orderable: false, targets: 0 }
      ]
    });
    const el = document.getElementById("air-datepicker-global-container");
    if (el) {
      el.style.zIndex = "9999";
    }
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
    
    if (eventDateInput) {
      const form = eventDateInput.closest('form');
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

  window.addEventListener('DOMContentLoaded', function () {
    const observer = new MutationObserver(function () {
      const datepicker = document.querySelector('.air-datepicker');
      if (datepicker && !datepicker.classList.contains('bg-body')) {
        datepicker.classList.add('bg-body', 'text-body');
      }
    });

    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  });

  function fetchNotifications() {
    fetch("ajax/notifications.php")
      .then(res => res.json())
      .then(data => {
        const count = data.length;
        const headerBadge = document.getElementById("headerNotiBadge");
        const sidebarBadge = document.getElementById("sidebarNotiBadge");
        const list = document.getElementById("notifications-list");

        // Update badges
        if (headerBadge) {
          headerBadge.textContent = count ? count : "";
          headerBadge.style.display = count ? "inline" : "none";
        }
        if (sidebarBadge) {
          sidebarBadge.textContent = count ? count : "";
          sidebarBadge.style.display = count ? "inline" : "none";
        }

        list.innerHTML = "";

        if (data.length === 0) {
          list.innerHTML = `<li class="notification-item text-muted small">No new notifications.</li>`;
          return;
        }

        data.forEach(n => {
          const li = document.createElement("li");
          li.className = "notification-item";
          li.innerHTML = `
            <div>
              <strong>${n.name}</strong>
              <span text-muted small">${n.company}</span><br>
              <small class="text-muted">${n.submitted_at}</small>
              <br>
              <a href="view_quotation.php?id=${n.id}" class="btn btn-sm theme_outline_btn_color mt-2">View Quotation</a>
            </div>
          `;
          list.appendChild(li);
        });
      });
  }