<?php include 'includes/header.php'; ?>
<div class="text-center mt-5">
    <h1>Welcome to Campus Service Hub</h1>
    <p class="lead">Find student skills and services or offer your own!</p>
    <a href="search.php" class="btn btn-outline-primary btn-lg mt-3">Explore Services</a>
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="register.php" class="btn btn-primary btn-lg mt-3">Join Now</a>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>