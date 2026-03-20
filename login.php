<?php
session_start();
// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบบริหารจัดการโรงเรียนสาธิตวิทยา</title>
    
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
        }
        .login-bg {
            background: linear-gradient(135deg, #2C3E50 0%, #1a252f 100%);
            color: #F5E7C6;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            text-align: center;
        }
        .login-form-area {
            background: #fff;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .btn-theme {
            background-color: #F5E7C6;
            color: #333;
            font-weight: 600;
            border: 1px solid #D4B872;
            transition: 0.3s;
        }
        .btn-theme:hover {
            background-color: #D4B872;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card login-card flex-row">
                
                <!-- Left Side: Branding -->
                <div class="col-md-5 d-none d-md-flex login-bg">
                    <img src="images/favicon.svg" alt="Logo" width="100" class="mb-4" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3))">
                    <h3 class="fw-bold mb-2">สาธิตวิทยา</h3>
                    <p class="text-white-50">ระบบบริหารจัดการโรงเรียนแบบครบวงจร</p>
                </div>
                
                <!-- Right Side: Login Form -->
                <div class="col-md-7 login-form-area">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-dark">ลงชื่อเข้าใช้งาน</h4>
                        <p class="text-muted">กรุณากรอกชื่อผู้ใช้และรหัสผ่านของคุณ</p>
                    </div>

                    <?php if(isset($_SESSION['error_msg'])): ?>
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-circle me-1"></i> <?php echo $_SESSION['error_msg']; ?>
                        </div>
                        <?php unset($_SESSION['error_msg']); ?>
                    <?php endif; ?>

                    <form action="auth.php" method="POST">
                        <!-- CSRF Protection Token -->
                        <?php 
                            if(empty($_SESSION['csrf_token'])) {
                                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                            }
                        ?>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label text-muted fw-bold">ชื่อผู้ใช้ (Username)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="username">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label text-muted fw-bold">รหัสผ่าน (Password)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-theme py-2"><i class="fas fa-sign-in-alt me-2"></i> เข้าสู่ระบบ</button>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">หากพบปัญหาการเข้าสู่ระบบ <a href="#" class="text-decoration-none">ติดต่อผู้ดูแลระบบ</a></small>
                        </div>
                    </form>
                </div>
                
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
