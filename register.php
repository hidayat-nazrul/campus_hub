<?php
require 'includes/db.php';
include 'includes/header.php';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($name) || empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT); // Password hashing requirement[cite: 10]
        $stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $username, $hashed, $role);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Username already exists.";
        }
    }
}
?>
<!-- Gunakan layout borang yang sama seperti Mini Project 1 di sini (rujuk register kod sebelum ini) -->
<div class="row justify-content-center"><div class="col-md-6"><div class="card shadow-sm"><div class="card-body">
    <h3 class="card-title text-center">Register</h3>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST" action="">
        <input type="text" name="name" class="form-control mb-3" placeholder="Full Name" required>
        <input type="text" name="username" class="form-control mb-3" placeholder="Username" required>
        <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
        <select name="role" class="form-select mb-3" required>
            <option value="">Select Role...</option>
            <option value="User">User</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
</div></div></div></div>
<?php include 'includes/footer.php'; ?>