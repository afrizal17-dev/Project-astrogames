<?php
$pageTitle = 'Login Member & Admin';
require_once __DIR__ . '/includes/functions.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (!empty($email) && !empty($pass)) {
        $user = dbFetchOne("SELECT * FROM users WHERE email = ? OR username = ?", [$email, $email]);
        
        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['user'] = [
                'id' => $user['id'], 
                'username' => $user['username'], 
                'role' => $user['role'], 
                'email' => $user['email'],
                'full_name' => $user['full_name'],
                'avatar' => $user['avatar']
            ];
            
            if ($user['role'] === 'admin') {
                header("Location: " . BASE_URL . "/admin/index.php");
            } else {
                header("Location: " . BASE_URL . "/index.php");
            }
            exit;
        } else {
            $msg = 'Email/Username atau Password tidak valid.';
        }
    } else {
        $msg = 'Email/Username dan Password wajib diisi.';
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
            
            <!-- FORM LOGIN TEGAK & RAPI -->
            <div class="auth-card">
                <h3 class="text-white fw-bold mb-2 text-center">MASUK KE ASTROGAMES</h3>
                <p class="text-muted small text-center mb-4">Masuk ke akun Anda untuk akses katalog, wishlist, dan admin dashboard.</p>

                <?php if (!empty($msg)): ?>
                    <div class="alert alert-danger small p-2 mb-3 text-center"><?php echo $msg; ?></div>
                <?php endif; ?>

                <form method="POST" action="<?php echo BASE_URL; ?>/login.php">
                    <div class="mb-3">
                        <label class="astro-label" for="inputEmail"><i class="fas fa-envelope text-purple me-1"></i> Email / Username</label>
                        <input type="text" id="inputEmail" name="email" class="astro-input" placeholder="Masukkan email atau username Anda" required autocomplete="username">
                    </div>

                    <div class="mb-4">
                        <label class="astro-label" for="inputPassword"><i class="fas fa-lock text-purple me-1"></i> Password</label>
                        <input type="password" id="inputPassword" name="password" class="astro-input" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <button type="submit" class="btn btn-purple btn-lg w-100 fw-bold py-3 mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i> LOGIN SEKARANG
                    </button>

                    <div class="text-center small text-muted">
                        Belum punya akun? <a href="<?php echo BASE_URL; ?>/register.php" class="text-purple fw-bold text-decoration-none">Daftar Akun Baru</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
