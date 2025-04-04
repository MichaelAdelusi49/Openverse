<!-- components/../controllers/loading_spinner.php -->
<div id="loading-spinner" class="spinner-container" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
    </div>
    <p class="loading-text">Searching Openverse...</p>
</div>

<style>
.spinner-container {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    z-index: 1000;
    background: rgba(255, 255, 255, 0.9);
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.loading-text {
    margin-top: 10px;
    color: #333;
}
</style>

<script>
function showLoading() {
    document.getElementById('loading-spinner').style.display = 'block';
}

function hideLoading() {
    document.getElementById('loading-spinner').style.display = 'none';
}

// Add to form submissions
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', showLoading);
});

// Add to AJAX requests
const originalFetch = window.fetch;
window.fetch = async (...args) => {
    showLoading();
    try {
        return await originalFetch(...args);
    } finally {
        hideLoading();
    }
};
</script>