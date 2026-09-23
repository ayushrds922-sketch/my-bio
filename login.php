<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!empty($email) && !empty($pass)) {
        try {
            $db = new PDO('sqlite:' . __DIR__ . '/faymas.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];

                // Record Login Entry
                $db->exec("CREATE TABLE IF NOT EXISTS login_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, name TEXT, email TEXT, login_time DATETIME DEFAULT CURRENT_TIMESTAMP)");
                $log = $db->prepare("INSERT INTO login_logs (user_id, name, email) VALUES (?, ?, ?)");
                $log->execute([$user['id'], $user['name'], $user['email']]);

                header("Location: index.php");
                exit();
            } else {
                $msg = "Invalid Email or Password!";
            }
        } catch (Exception $e) {
            $msg = "Database Error: " . $e->getMessage();
        }
    } else {
        $msg = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ayux AI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body {
            background: #090a0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .auth-card {
            width: 100%;
            max-width: 380px;
            background: rgba(20, 24, 35, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 32px 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
        }
        .brand-badge {
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        h2 { color: #fff; font-size: 1.6rem; margin: 12px 0 6px; }
        p.subtitle { color: #94a3b8; font-size: 0.85rem; margin-bottom: 22px; }
        .input-box { margin-bottom: 15px; }
        .input-box label { display: block; font-size: 0.8rem; color: #cbd5e1; margin-bottom: 6px; }
        .input-box input {
            width: 100%;
            padding: 12px 14px;
            background: #0d1017;
            border: 1px solid #232936;
            border-radius: 10px;
            color: #fff;
            outline: none;
            font-size: 0.95rem;
        }
        .input-box input:focus { border-color: #38bdf8; }
        .btn-submit {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #fca5a5;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 16px;
            text-align: center;
        }
        .footer-link { text-align: center; margin-top: 20px; font-size: 0.85rem; color: #94a3b8; }
        .footer-link a { color: #38bdf8; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="auth-card">
    <span class="brand-badge">Ayux AI</span>
    <h2>Welcome Back</h2>
    <p class="subtitle">Login to explore exclusive prompts</p>

    <?php if (!empty($msg)): ?>
        <div class="alert-error"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="input-box">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="example@gmail.com" required>
        </div>
        <div class="input-box">
            <label>Password</label>
            <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Sign In</button>
    </form>

    <div class="footer-link">
        Don't have an account? <a href="register.php">Register</a>
    </div>
</div>

</body>
</html>
