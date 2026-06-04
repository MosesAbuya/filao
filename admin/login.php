<?php
require_once __DIR__ . '/helpers.php';
sessionStart();

if (currentUser()) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfVerify($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect('login.php');
    }
    
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        setFlash('error', 'Please enter both email and password.');
        redirect('login.php');
    }
    
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND status = 'active'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            setFlash('success', 'Welcome back, ' . sanitize(explode(' ', $user['name'])[0]) . '!');
            redirect('index.php');
        } else {
            $reason = !$user ? "User not found or inactive." : "Password does not match.";
            setFlash('error', 'Invalid credentials: ' . $reason);
            redirect('login.php');
        }
    } catch (Exception $e) {
        setFlash('error', 'System Error: ' . $e->getMessage());
        redirect('login.php');
    }
}
?>
<?php $pageTitle = 'Login'; $bodyClass = 'login-page'; include 'partials/head.php'; ?>

<div class="login-card">
    <div class="login-logo">
        <div class="brand-icon">F</div>
        <h1>Filao Adventures</h1>
        <p>Admin Operations Platform</p>
    </div>
    
    <form action="login.php" method="POST">
        <?= csrfField() ?>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required autofocus placeholder="admin@filaoadventures.co.ke">
        </div>
        
        <div class="mb-4">
            <label for="password" class="form-label d-flex justify-content-between">
                <span>Password</span>
            </label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
    </form>
</div>

<?php include 'partials/scripts.php'; ?>
</body>
</html>
