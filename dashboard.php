<?php
require 'includes/db.php';
include 'includes/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle Delete with security check (only owner or admin)[cite: 10]
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT user_id, image FROM services WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $svc = $res->fetch_assoc();
        if ($role === 'Admin' || $svc['user_id'] == $user_id) {
            if (file_exists("uploads/" . $svc['image'])) unlink("uploads/" . $svc['image']);
            $conn->query("DELETE FROM services WHERE id = $del_id");
            echo "<div class='alert alert-success'>Service deleted.</div>";
        }
    }
}
?>

<h2>My Dashboard (<?php echo htmlspecialchars($role); ?>)</h2>
<hr>
<div class="row">
    <?php
    // JOIN and ORDER BY requirements[cite: 11]
    if ($role === 'Admin') {
        $sql = "SELECT services.*, users.name FROM services JOIN users ON services.user_id = users.id ORDER BY services.created_at DESC";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT services.*, users.name FROM services JOIN users ON services.user_id = users.id WHERE services.user_id = ? ORDER BY services.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // htmlspecialchars() to prevent XSS
        echo '
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="uploads/'. htmlspecialchars($row['image']) .'" class="card-img-top" style="height:200px; object-fit:cover;">
                <div class="card-body">
                    <h5>'. htmlspecialchars($row['title']) .'</h5>
                    <p class="text-success fw-bold">RM '. number_format($row['price'], 2) .'</p>
                    <p>'. nl2br(htmlspecialchars($row['description'])) .'</p>
                    <small>By: '. htmlspecialchars($row['name']) .'</small>
                </div>
                <div class="card-footer bg-white">
                    <a href="edit_service.php?id='. $row['id'] .'" class="btn btn-sm btn-warning">Edit</a>
                    <a href="?delete='. $row['id'] .'" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this service?\')">Delete</a>
                </div>
            </div>
        </div>';
    }
    ?>
</div>
<?php include 'includes/footer.php'; ?>