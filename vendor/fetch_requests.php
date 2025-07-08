<?php
require_once '../admin/includes/db.php';

$category = $_POST['category_id'] ?? '';
$date_from = $_POST['date_from'] ?? '';
$date_to = $_POST['date_to'] ?? '';

$sql = "SELECT requests.*,request_categories.id as cat_id,request_categories.name as category_name FROM requests JOIN request_categories ON requests.category_id = request_categories.id WHERE 1 ";
$params = [];

if ($category) {
  $sql .= "AND category_id = ? ";
  $params[] = $category;
}
if ($date_from) {
  $sql .= "AND created_at >= ? ";
  $params[] = $date_from . ' 00:00:00';
}
if ($date_to) {
  $sql .= "AND created_at <= ? ";
  $params[] = $date_to . ' 23:59:59';
}

// $sql .= "ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

// Render cards
if ($requests):
  foreach ($requests as $r): if($r['status'] == '1'): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100 shadow-sm" style="background: #ffffffcc;backdrop-filter: blur(5px);">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-clipboard-list nav_active_link"></i> <?= htmlspecialchars($r['title']) ?></h5>
            <h6><?= htmlspecialchars($r['category_name']) ?></h6>
            <p class="card-text text-truncate" title="<?= htmlspecialchars($r['description']) ?>"><?= nl2br(htmlspecialchars($r['description'])) ?></p>
            <p><i class="fa-solid fa-calendar-days text-muted"></i> <?= date('d-M-Y', strtotime(htmlspecialchars($r['created_at']))) ?>&emsp;<i class="fa-solid fa-clock text-muted"></i> <?= date('h:i A', strtotime(htmlspecialchars($r['created_at']))) ?></p>
            <a href="quote.php?request_id=<?= $r['id'] ?>" class="btn btn-primary theme_bg_color theme_border_color">
                <i class="fas fa-pen-to-square"></i> Submit Quotation
            </a>
        </div>
      </div>
    </div>
  <?php endif; endforeach;
else:
  echo "<div class='alert alert-info'>No requests found for selected filters.</div>";
endif;




