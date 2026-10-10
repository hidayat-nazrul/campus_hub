<?php
require 'includes/db.php';
include 'includes/header.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($desc) || empty($price) || $_FILES['image']['error'] !== 0) {
        $error = "All fields and a valid image are required.";
    } else {
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // File validation[cite: 10]
        if (!in_array($ext, ['jpg', 'png', 'jpeg'])) {
            $error = "Only JPG and PNG allowed.";
        } elseif ($file_size > 2097152) {
            $error = "File size must be under 2MB.";
        } else {
            $new_name = uniqid() . "." . $ext;
            move_uploaded_file($file_tmp, "uploads/" . $new_name);
            
            // Prepared statements to prevent SQL Injection
            $stmt = $conn->prepare("INSERT INTO services (user_id, title, description, price, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issds", $user_id, $title, $desc, $price, $new_name);
            $stmt->execute();
            $success = "Service added successfully!";
        }
    }
}
?>
<div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <h4>Add Service</h4>
        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateService()">
            <input type="text" id="title" name="title" class="form-control mb-2" placeholder="Service Title">
            <textarea id="desc" name="description" class="form-control mb-2" placeholder="Description"></textarea>
            <input type="number" step="0.01" id="price" name="price" class="form-control mb-2" placeholder="Price (RM)">
            <input type="file" id="image" name="image" class="form-control mb-3" accept=".jpg, .png, .jpeg">
            <button type="submit" class="btn btn-primary w-100">Submit Service</button>
        </form>
    </div>
</div>

<script>
// Client-side JS validation[cite: 11]
function validateService() {
    if(!document.getElementById('title').value || !document.getElementById('desc').value || !document.getElementById('price').value) {
        alert("All fields are required!"); return false;
    }
    let file = document.getElementById('image').files[0];
    if(!file) { alert("Image is required!"); return false; }
    if(file.size > 2097152) { alert("File exceeds 2MB!"); return false; }
    return true;
}
</script>
<?php include 'includes/footer.php'; ?>