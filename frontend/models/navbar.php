<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="../public/index.php">Media Search</a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
        <ul class="navbar-nav">
            <!-- Home Button -->
            <li class="nav-item">
                <a class="nav-link" href="../public/index.php">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>

            <!-- Saved Items Button -->
            <li class="nav-item">
                <a class="nav-link" href="../public/saved.php">
                    <i class="fas fa-bookmark"></i> Saved Items
                </a>
            </li>
        </ul>
    </div>

    <div class="ml-auto">
        <ul class="navbar-nav">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                        Hi, <?= htmlspecialchars($username ?? '') ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="../public/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </li>
            <?php else: ?>
                <!-- Login & Register -->
                <li class="nav-item">
                    <a class="nav-link" href="../public/login.php">Login</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../public/register.php">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
