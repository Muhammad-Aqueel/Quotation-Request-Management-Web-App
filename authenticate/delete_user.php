<?php
require 'includes/db.php';
require 'includes/auth.php';
require_login();
require_admin();

$id = intval($_GET['id'] ?? 0);
if ($id) {
  try {
    $stmt = $pdo->prepare("SELECT * FROM `users` INNER JOIN requests ON users.id = requests.user_id WHERE users.id = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetchAll();
    if(count($result) > 0){
      $_SESSION['user_delete_message'] = '<div class="alert alert-info text-center"><h4 class="text-center"><i class="fas fa-exclamation-circle"></i> User can\'t be deleted due to having a request.</h4></div>';
    } else {
      $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
      $_SESSION['user_delete_message'] = '<div class="alert alert-success text-center"><h4 class="text-center"><i class="fa-solid fa-file-arrow-up"></i> User deleted successfully.</h4></div>';
    }
  } catch (PDOException $e) {
    $_SESSION['user_delete_message'] = "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> " . $e->getMessage() . "</div>";
  }
}

header("Location: user_management.php");
exit;
