<?php
  include 'includes/header.php';

  $request_id = intval($_GET['request_id'] ?? 0);
  if (!$request_id) {
    echo "<div class='container'><div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> No request selected.</div><a href='quotations.php?request_id=0' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
    include 'includes/footer.php';
    exit;
  }

  // Get request
  $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND user_id = ?");
  $stmt->execute([$request_id, $_SESSION['user_id']]);
  $request = $stmt->fetch();

  // Get items
  $stmt = $pdo->prepare("SELECT * FROM request_items WHERE request_id = ?");
  $stmt->execute([$request_id]);
  $items = $stmt->fetchAll();

  // Get vendors and quotations
  $stmt = $pdo->prepare("SELECT q.id AS quotation_id, q.vendor_id, q.total_amount, v.name, v.company
                        FROM quotations q
                        JOIN vendors v ON q.vendor_id = v.id
                        WHERE q.request_id = ? AND q.status != 'Deleted'");
  $stmt->execute([$request_id]);
  $vendors = $stmt->fetchAll();

  $vendor_ids = array_column($vendors, 'vendor_id');
  $vendor_map = [];
  foreach ($vendors as $v) {
      $vendor_map[$v['vendor_id']] = $v;
  }

  // Unit prices
  $stmt = $pdo->prepare("SELECT qi.unit_price, qi.request_item_id, q.vendor_id
                        FROM quotation_items qi
                        JOIN quotations q ON qi.quotation_id = q.id
                        WHERE q.request_id = ?");
  $stmt->execute([$request_id]);
  $prices = $stmt->fetchAll();

  $price_matrix = [];
  foreach ($prices as $p) {
      $price_matrix[$p['request_item_id']][$p['vendor_id']] = $p['unit_price'];
  }

  if(!$vendors){
    echo "<div class='container'><div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> Quotations not exist to compare.</div><a href='quotations.php?request_id=" . $request_id . "' class='btn btn-secondary btn-sm mt-3'><i class='fas fa-arrow-left'></i> Back</a></div>";
    include 'includes/footer.php';
    exit;
  }
?>

<?php if($request):?>
  <h2 class="mb-4"><i class="fas fa-scale-balanced"></i> Compare Quotations – <?= htmlspecialchars($request['title']) ?></h2>

  <div class="table-responsive">
    <table class="table table-bordered align-middle table-sm table-striped">
      <thead>
        <tr class="table-light">
          <th>Item</th>
          <th>Qty</th>
          <?php foreach ($vendor_ids as $vid): ?>
            <th><?= htmlspecialchars($vendor_map[$vid]['name']) ?><br><small><?= htmlspecialchars($vendor_map[$vid]['company']) ?></small></th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $item): ?>
          <?php
          $row_prices = $price_matrix[$item['id']] ?? [];
          $min = $row_prices ? min($row_prices) : null;
          ?>
          <tr>
            <td><?= htmlspecialchars($item['item_name']) ?></td>
            <td><?= $item['quantity'] ?></td>
            <?php foreach ($vendor_ids as $vid): ?>
              <?php
                $price = $row_prices[$vid] ?? null;
                $class = ($price !== null && $price == $min) ? 'table-success fw-bold' : '';
              ?>
              <td class="<?= $class ?>">
                <?= $price !== null ? number_format($price, 1) : '—' ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="2" class="text-center fw-bold"><i class="fas fa-coins"></i> Total</td>
          <?php $min_total_price = $vendor_map[$vid]['total_amount']; ?>
          <?php foreach ($vendor_ids as $vid): ?>
            <?php
              $min_total_price = $vendor_map[$vid]['total_amount'] < $min_total_price ? $vendor_map[$vid]['total_amount'] : $min_total_price;
            ?>
          <?php endforeach; ?>
          <?php foreach ($vendor_ids as $vid): ?>
            <?php
              $class = ($vendor_map[$vid]['total_amount'] == $min_total_price) ? 'table-success fw-bold' : '';
            ?>
            <td class="<?= $class ?>"><strong><?= number_format($vendor_map[$vid]['total_amount'], 1) ?></strong></td>
          <?php endforeach; ?>
        </tr>
      </tfoot>
    </table>
  </div>
<?php else: echo "<div class='alert alert-warning'><i class='fas fa-exclamation-circle'></i> No request selected.</div>"; ?>
<?php endif ?>

<a href="quotations.php?request_id=<?= $request_id ?>" class="btn btn-secondary btn-sm mt-3">
  <i class="fas fa-arrow-left"></i> Back
</a>

<?php include 'includes/footer.php'; ?>
