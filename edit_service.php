<?php
require 'includes/db.php';
include 'includes/header.php';

// Secure session handling & routing[cite: 11]
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
}

$error = ''; $success = '';
$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Prepared statements (prevent SQL injection) to fetch current data[cite: 11]
$stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Service not found.</div>";
    include 'includes/footer.php';
    exit;
}

$service = $result->fetch_assoc();

// Ensure only owner/admin can edit
if ($role !== 'Admin' && $service['user_id'] != $user_id) {
    echo "<div class='alert alert-danger'>Unauthorized access. You can only edit your own services.</div>";
    include 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);

    // Server-side validation using PHP[cite: 11]
    if (empty($title) || empty($desc) || empty($price)) {
        $error = "Title, description, and price are required.";
    } else {
        // Check if there is a new file uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_tmp = $_FILES['image']['tmp_name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Validate file type (JPG/PNG) & Limit file size[cite: 10]
            if (!in_array($ext, ['jpg', 'png', 'jpeg'])) {
                $error = "Only JPG and PNG allowed.";
            } elseif ($file_size > 2097152) {
                $error = "File size must be under 2MB.";
            } else {
                $new_name = uniqid() . "." . $ext;
                // Store file in server directory[cite: 10]
                if (move_uploaded_file($file_tmp, "uploads/" . $new_name)) {
                    // Delete old image
                    if (file_exists("uploads/" . $service['image'])) {
                        unlink("uploads/" . $service['image']);
                    }
                    // Update database with new image using prepared statements[cite: 11]
                    $update_stmt = $conn->prepare("UPDATE services SET title=?, description=?, price=?, image=? WHERE id=?");
                    $update_stmt->bind_param("ssdsi", $title, $desc, $price, $new_name, $service_id);
                    $update_stmt->execute();
                    $success = "Service updated successfully with new image!";
                    $service['image'] = $new_name; 
                } else {
                    $error = "Failed to upload new image.";
                }
            }
        } else {
            // Update without changing the image
            $update_stmt = $conn->prepare("UPDATE services SET title=?, description=?, price=? WHERE id=?");
            $update_stmt->bind_param("ssdi", $title, $desc, $price, $service_id);
            $update_stmt->execute();
            $success = "Service updated successfully!";
        }
        
        $service['title'] = $title;
        $service['description'] = $desc;
        $service['price'] = $price;
    }
}
?>

<div class="card mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <h4>Edit Service</h4>
        
        <!-- Display error & success messages[cite: 11] -->
        <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateEditService()">
            <div class="mb-2">
                <label>Title</label>
                <!-- htmlspecialchars() to prevent XSS[cite: 11] -->
                <input type="text" id="edit_title" name="title" class="form-control" value="<?php echo htmlspecialchars($service['title']); ?>">
            </div>
            <div class="mb-2">
                <label>Description</label>
                <textarea id="edit_desc" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($service['description']); ?></textarea>
            </div>
            <div class="mb-2">
                <label>Price (RM)</label>
                <input type="number" step="0.01" id="edit_price" name="price" class="form-control" value="<?php echo htmlspecialchars($service['price']); ?>">
            </div>
            <div class="mb-3">
                <label>Current Image</label><br>
                <img src="uploads/<?php echo htmlspecialchars($service['image']); ?>" width="120" class="mb-2 border rounded"><br>
                <label>Change Image (Optional - JPG/PNG limit 2MB)</label>
                <input type="file" id="edit_image" name="image" class="form-control" accept=".jpg, .png, .jpeg">
            </div>
            <button type="submit" class="btn btn-warning w-100">Update Service</button>
        </form>
    </div>
</div>

<script>
// Implement client-side validation using JavaScript[cite: 11]
function validateEditService() {
    if(!document.getElementById('edit_title').value || !document.getElementById('edit_desc').value || !document.getElementById('edit_price').value) {
        alert("Title, Description, and Price are required fields!"); 
        return false;
    }
    
    let fileInput = document.getElementById('edit_image');
    if(fileInput.files.length > 0) {
        let file = fileInput.files[0];
        // File validation (Client-side)[cite: 11]
        if(file.size > 2097152) { 
            alert("File size exceeds 2MB limit!"); 
            return false; 
        }
        let ext = file.name.split('.').pop().toLowerCase();
        if(ext !== 'jpg' && ext !== 'jpeg' && ext !== 'png') {
            alert("Only JPG or PNG images are allowed!"); 
            return false;
        }
    }
    return true;
}
</script>

<?php include 'includes/footer.php'; ?>