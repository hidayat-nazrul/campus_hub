<?php include 'includes/header.php'; ?>
<h2>Search Services</h2>
<input type="text" id="searchBox" class="form-control mb-4" placeholder="Type to search services by title or description (Live Search)...">

<div class="row" id="results">
    <!-- AJAX results will be injected here without page reload -->
</div>

<script>
// AJAX fetch() implementation[cite: 11]
document.getElementById('searchBox').addEventListener('keyup', function() {
    let query = this.value;
    fetch('ajax_search.php?q=' + encodeURIComponent(query))
        .then(response => response.text())
        .then(data => {
            document.getElementById('results').innerHTML = data;
        });
});
// Load all initially
window.onload = () => document.getElementById('searchBox').dispatchEvent(new Event('keyup'));
</script>
<?php include 'includes/footer.php'; ?>