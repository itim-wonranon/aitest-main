<?php if(session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<!-- Header Component -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm mb-4">
    <div class="container-fluid">
        <!-- Sidebar Toggle -->
        <button type="button" id="sidebarCollapse" class="btn btn-theme me-3">
            <i class="fas fa-align-left"></i>
        </button>
        
        <span class="d-none d-md-block text-muted">ระบบบริหารจัดการโรงเรียนสาธิตวิทยา (Module 6: แดชบอร์ดผู้บริหาร)</span>

        <!-- Right Menu -->
        <div class="ms-auto d-flex align-items-center">
            
            <!-- Notification Bell -->
            <a class="nav-link text-muted me-3 position-relative" href="#">
                <i class="fas fa-bell fa-lg"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.55rem;">
                    3
                </span>
            </a>

            <!-- User Dropdown (Module 4 related) -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle text-dark fw-bold d-flex align-items-center" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name'] ?? 'User'); ?>&background=2C3E50&color=F5E7C6&rounded=true" alt="User" width="32" class="me-2">
                    <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'ผู้ใช้'); ?> (<?php echo ucfirst(htmlspecialchars($_SESSION['role'] ?? 'guest')); ?>)
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user fa-sm fa-fw me-2 text-muted"></i> โปรไฟล์</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cogs fa-sm fa-fw me-2 text-muted"></i> ตั้งค่า</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt fa-sm fa-fw me-2"></i> ออกจากระบบ</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
