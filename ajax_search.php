<?php
require 'includes/db.php';

$search = isset($_GET['q']) ? "%".$_GET['q']."%" : "%";

$stmt = $conn->prepare("SELECT services.*, users.name FROM services JOIN users ON services.user_id = users.id WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC");
$stmt->bind_param("ss", $search, $search);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <img src="uploads/'. htmlspecialchars($row['image']) .'" class="card-img-top" style="height:150px; object-fit:cover;">
                <div class="card-body">
                    <h5 class="card-title">'. htmlspecialchars($row['title']) .'</h5>
                    <h6 class="text-primary">RM '. number_format($row['price'], 2) .'</h6>
                    <p class="card-text small">'. htmlspecialchars($row['description']) .'</p>
                    <small class="text-muted">Provider: '. htmlspecialchars($row['name']) .'</small>
                </div>
            </div>
        </div>';
    }
} else {
    echo "<div class='col-12'><p class='text-muted'>No services found.</p></div>";
}
?>