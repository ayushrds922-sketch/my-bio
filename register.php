<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$db = new PDO('sqlite:' . __DIR__ . '/faymas.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($pass)) {
        $msg = "Sabhi boxes bharna zaroori hai.";
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $msg = "Yeh email pehle se registered hai!";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $insert = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert->execute([$name, $email, $hash]);
            header("Location: login.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Ayux AI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        body {
            background: #090a0f;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: hidden;
            position: relative;
        }
        .glow-1, .glow-2 {
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            filter: blur(90px);
            z-index: 1;
            animation: pulse 6s infinite alternate ease-in-out;
        }
        .glow-1 {
            background: rgba(129, 140, 248, 0.35);
            top: 8%;
            right: 5%;
        }
        .glow-2 {
            background: rgba(56, 189, 248, 0.35);
            bottom: 8%;
            left: 5%;
            animation-delay: 3s;
        }
        @keyframes pulse {
            0% { transform: scale(1) translate(0, 0); }
            100% { transform: scale(1.25) translate(-20px, -20px); }
        }

        .auth-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 380px;
            background: rgba(20, 24, 35, 0.75);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 34px 26px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .brand-badge {
            display: inline-block;
            background: rgba(129, 140, 248, 0.15);
            border: 1px solid rgba(129, 140, 248, 0.3);
            color: #818cf8;
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 14px;
        }
        h2 {
            color: #ffffff;
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        p.subtitle {
            color: #94a3b8;
            font-size: 0.88rem;
            margin-bottom: 24px;
        }

        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            font-size: 0.8rem;
            color: #cbd5e1;
            margin-bottom: 7px;
        }
        .input-group input {
            width: 100%;
            padding: 13px 16px;
            background: rgba(10, 13, 20, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.25s ease;
        }
        .input-group input:focus {
            border-color: #818cf8;
            box-shadow: 0 0 12px rgba(129, 140, 248, 0.3);
            background: rgba(15, 20, 32, 0.95);
        }

        .btn-submit {
            width: 100%;
            padding: 13px;
            margin-top: 10px;
            background: linear-gradient(135deg, #6366f1, #38bdf8);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
            transition: transform 0.15s, opacity 0.2s;
        }
        .btn-submit:active {
            transform: scale(0.98);
            opacity: 0.9;
        }

        .error-box {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 18px;
            text-align: center;
        }
        .switch-text {
            text-align: center;
            margin-top: 22px;
            font-size: 0.86rem;
            color: #94a3b8;
        }
        .switch-text a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="glow-1"></div>
    <div class="glow-2"></div>

    <div class="auth-card">
        <span class="brand-badge">Join Ayux AI</span>
        <h2>Create Account</h2>
        <p class="subtitle">Free prompts aur AI art explore karne ke liye judein</p>

        <?php if (!empty($msg)): ?>
            <div class="error-box"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Aapka Naam" required>
            </div>
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="name@example.com" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-submit">Register</button>
        </form>

        <div class="switch-text">
            Pehle se account hai? <a href="login.php">Login karein</a>
        </div>
    </div>

</body>
</html>
