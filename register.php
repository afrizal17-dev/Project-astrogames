<?php
$pageTitle = 'Daftar Akun Baru';
require_once __DIR__ . '/includes/functions.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($pass)) {
        $msg = 'Semua field wajib diisi.';
    } elseif ($pass !== $confirmPass) {
        $msg = 'Konfirmasi password tidak cocok.';
    } else {
        // Hash password
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        try {
            dbExecute("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')", [
                $username, $email, $hashed, $username
            ]);
            $newUserId = dbFetchOne("SELECT LAST_INSERT_ID() as id")['id'];
            
            // Login user immediately
            $_SESSION['user'] = ['id' => $newUserId, 'username' => $username, 'role' => 'user', 'email' => $email];
            $_SESSION['flash_success'] = 'Registrasi berhasil! Selamat datang di GameCheck.';
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            $msg = 'Username atau Email sudah terdaftar.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
    .login-container {
        min-height: 80vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .auth-card {
        background: rgba(30, 30, 40, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.5rem;
        padding: 3rem 2rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .auth-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 45px rgba(0,0,0,0.5);
    }
    .astro-input {
        background: rgba(15, 15, 25, 0.5);
        border: 1px solid rgba(255,255,255,0.1);
        color: #fff;
        border-radius: 0.75rem;
        padding: 0.8rem 1rem;
        transition: all 0.3s ease;
        width: 100%;
        display: block;
    }
    .astro-input:focus {
        background: rgba(15, 15, 25, 0.8);
        border-color: #8b5cf6;
        box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.2);
        outline: none;
    }
    .btn-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        border: none;
        border-radius: 0.75rem;
        color: white;
        transition: all 0.3s ease;
    }
    .btn-purple:hover {
        background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3);
        color: white;
    }
    .astro-label {
        font-size: 0.9rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #d1d5db;
        display: block;
    }
</style>

<main class="container login-container py-5">

    <div class="row justify-content-center w-100">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5">
            <div class="auth-card">
                <h3 class="text-white fw-bold mb-2 text-center">DAFTAR AKUN ASTROGAMES</h3>
                <p class="text-muted small text-center mb-4">Buat akun gratis untuk menikmati fitur Laptop Compatibility Checker.</p>

                <?php if (!empty($msg)): ?>
                    <div class="alert alert-danger small p-2 mb-3 text-center"><?php echo htmlspecialchars($msg); ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="mb-3">
                        <label class="astro-label"><i class="fas fa-user text-purple me-1"></i> Username</label>
                        <input type="text" name="username" class="astro-input" placeholder="Username unik Anda" required>
                    </div>

                    <div class="mb-3">
                        <label class="astro-label"><i class="fas fa-envelope text-purple me-1"></i> Email</label>
                        <input type="email" name="email" class="astro-input" placeholder="email@domain.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="astro-label"><i class="fas fa-lock text-purple me-1"></i> Password</label>
                        <input type="password" name="password" class="astro-input" placeholder="••••••••" required>
                    </div>

                    <div class="mb-4">
                        <label class="astro-label"><i class="fas fa-check-double text-purple me-1"></i> Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="astro-input" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-purple btn-lg w-100 fw-bold py-3 mb-3">
                        <i class="fas fa-user-plus me-2"></i> REGISTRASI SEKARANG
                    </button>

                    <div class="text-center small text-muted">
                        Sudah punya akun? <a href="login.php" class="text-purple fw-bold text-decoration-none">Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
