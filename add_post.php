<?php
session_start();

// Database setup
$db = new PDO('sqlite:' . __DIR__ . '/faymas.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$db->exec("CREATE TABLE IF NOT EXISTS prompts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    creator TEXT NOT NULL,
    prompt TEXT NOT NULL,
    image_url TEXT NOT NULL,
    likes INTEGER DEFAULT 0,
    comments INTEGER DEFAULT 0,
    shares INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

$msg = '';
$msg_type = '';

// Image uploads directory setup
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $creator = trim($_POST['creator'] ?? '');
    $prompt = trim($_POST['prompt'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    // File upload check (Gallery se photo)
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image_file']['tmp_name'];
        $file_name = time() . '_' . basename($_FILES['image_file']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $image_url = 'uploads/' . $file_name;
        }
    }

    if (empty($title) || empty($creator) || empty($prompt) || empty($image_url)) {
        $msg = "Sabhi box bharna zaroori hai (Photo select karein ya link dalein).";
        $msg_type = "danger";
    } else {
        $stmt = $db->prepare("INSERT INTO prompts (title, creator, prompt, image_url) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$title, $creator, $prompt, $image_url])) {
            $msg = "Naya Prompt aur Photo successfully add ho gaya!";
            $msg_type = "success";
        } else {
            $msg = "Save karne me problem aayi, dobara try karein.";
            $msg_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Prompt - Faymas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .form-card {
            background: #14161d;
            border: 1px solid #232733;
            border-radius: 18px;
            padding: 22px;
            margin: 15px auto;
            max-width: 480px;
        }
        .form-label {
            display: block;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 6px;
            margin-top: 14px;
        }
        .form-input, .form-textarea {
            width: 100%;
            padding: 12px;
            background: #0d0f14;
            border: 1px solid #2c2f3b;
            border-radius: 10px;
            color: #fff;
            outline: none;
            font-size: 0.92rem;
        }
        .form-input:focus, .form-textarea:focus {
            border-color: #38bdf8;
        }
        .form-textarea {
            resize: vertical;
            min-height: 110px;
            line-height: 1.4;
        }
        .btn-submit {
            width: 100%;
            padding: 13px;
            background: linear-gradient(90deg, #2563eb, #38bdf8);
            border: none;
            border-radius: 25px;
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            margin-top: 20px;
            cursor: pointer;
        }
        .file-upload-box {
            border: 1px dashed #3b4252;
            padding: 14px;
            border-radius: 10px;
            text-align: center;
            background: #090a0d;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <header class="top-header">
        <a href="index.php" class="header-icon"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="logo-title">Add New Prompt</h1>
        <div style="width: 20px;"></div>
    </header>

    <div style="padding: 12px;">
        <div class="form-card">
            <?php if (!empty($msg)): ?>
                <div class="alert <?= $msg_type === 'success' ? 'alert-success' : 'alert-danger' ?>" style="margin-bottom:15px;">
                    <?= htmlspecialchars($msg) ?>
                </div>
            <?php endif; ?>

            <form action="add_post.php" method="POST" enctype="multipart/form-data">
                
                <!-- 1. Post Title Box -->
                <label class="form-label">Title / Caption:</label>
                <input type="text" name="title" class="form-input" placeholder="e.g. Wrap Your Story in Style ✨" required>

                <!-- 2. Creator Name Box -->
                <label class="form-label">Creator / Name (Aapka Naam):</label>
                <input type="text" name="creator" class="form-input" placeholder="e.g. Yasir Nisar" required>

                <!-- 3. Photo Upload from Gallery -->
                <label class="form-label">Photo Upload Karein (Phone Gallery se):</label>
                <div class="file-upload-box">
                    <input type="file" name="image_file" accept="image/*" style="width:100%; color:#94a3b8;">
                </div>

                <!-- 4. OR Photo URL -->
                <label class="form-label">Ya fir Photo ka direct Link (Optional):</label>
                <input type="url" name="image_url" class="form-input" placeholder="https://example.com/photo.jpg">

                <!-- 5. Prompt Text Box -->
                <label class="form-label">AI Prompt Text (Jo copy hoga):</label>
                <textarea name="prompt" class="form-textarea" placeholder="Yahan pura ChatGPT/Google ka image prompt likhein..." required></textarea>

                <button type="submit" class="btn-submit"><i class="fa-solid fa-plus"></i> Save & Publish</button>
            </form>
        </div>
    </div>

    <!-- Bottom Nav -->
    <nav class="bottom-nav">
        <a href="index.php" class="nav-link"><i class="fa-solid fa-house"></i></a>
        <a href="#" class="nav-link"><i class="fa-solid fa-magnifying-glass"></i></a>
        <a href="add_post.php" class="nav-link active add-btn"><i class="fa-solid fa-plus"></i></a>
        <a href="#" class="nav-link"><i class="fa-solid fa-layer-group"></i></a>
        <a href="#" class="nav-link"><i class="fa-regular fa-user"></i></a>
    </nav>

</body>
</html>
