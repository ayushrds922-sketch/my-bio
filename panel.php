<?php
$db = new PDO('sqlite:' . __DIR__ . '/faymas.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ensure column exists
try {
    $db->exec("ALTER TABLE prompts ADD COLUMN category TEXT DEFAULT 'Trending Prompt'");
} catch (Exception $e) {}

$msg = '';
$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $del = $db->prepare("DELETE FROM prompts WHERE id = ?");
    $del->execute([$del_id]);
    header("Location: panel.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $creator = trim($_POST['creator'] ?? 'Ayush');
    $category = trim($_POST['category'] ?? 'Trending Prompt');
    $prompt = trim($_POST['prompt'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = time() . '_' . basename($_FILES['image_file']['name']);
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $file_name)) {
            $image_url = 'uploads/' . $file_name;
        }
    }

    if (!empty($title) && !empty($prompt) && !empty($image_url)) {
        $stmt = $db->prepare("INSERT INTO prompts (title, creator, category, prompt, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $creator, $category, $prompt, $image_url]);
        $msg = "Success: Prompt published to live feed!";
    } else {
        $msg = "Error: Please select a photo and enter prompt text.";
    }
}

$today_visitors = $db->query("SELECT COUNT(*) FROM visitors WHERE date(visit_time) = date('now', 'localtime')")->fetchColumn();
$yesterday_visitors = $db->query("SELECT COUNT(*) FROM visitors WHERE date(visit_time) = date('now', 'localtime', '-1 day')")->fetchColumn();
$total_visits = $db->query("SELECT COUNT(*) FROM visitors")->fetchColumn();
$all_posts = $db->query("SELECT * FROM prompts ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:sans-serif; }
        body { background:#08090d; color:#fff; padding:15px; }
        .head { display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid #222; margin-bottom:15px; }
        .head h2 { color:#38bdf8; font-size:18px; }
        .stats { display:grid; grid-template-columns:repeat(3, 1fr); gap:10px; margin-bottom:20px; }
        .box { background:#141722; padding:12px; border-radius:10px; text-align:center; border:1px solid #252b3b; }
        .box span { font-size:11px; color:#888; text-transform:uppercase; display:block; }
        .box b { font-size:20px; color:#38bdf8; margin-top:5px; display:block; }
        .card { background:#141722; padding:16px; border-radius:12px; margin-bottom:20px; border:1px solid #252b3b; }
        .card h3 { font-size:15px; margin-bottom:12px; color:#fff; }
        label { font-size:12px; color:#aaa; margin:10px 0 4px; display:block; }
        input, select, textarea { width:100%; padding:10px; background:#08090d; border:1px solid #333; border-radius:8px; color:#fff; font-size:14px; outline:none; }
        textarea { min-height:80px; }
        .btn { width:100%; padding:12px; background:#38bdf8; border:none; border-radius:8px; color:#000; font-weight:bold; font-size:15px; margin-top:14px; cursor:pointer; }
        .msg { background:rgba(56,189,248,0.2); border:1px solid #38bdf8; color:#38bdf8; padding:8px; border-radius:6px; font-size:13px; margin-bottom:12px; text-align:center; }
        .item { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid #222; }
        .item img { width:40px; height:40px; border-radius:6px; object-fit:cover; margin-right:10px; }
        .del-btn { color:#ff4d4d; border:1px solid #ff4d4d; padding:4px 8px; border-radius:5px; text-decoration:none; font-size:12px; }
    </style>
</head>
<body>
    <div class="head">
        <h2>AYUX AI ADMIN</h2>
        <a href="index.php" target="_blank" style="color:#38bdf8; text-decoration:none; font-size:13px;">View Live Feed &rarr;</a>
    </div>

    <div class="stats">
        <div class="box"><span>Today</span><b><?= $today_visitors ?></b></div>
        <div class="box"><span>Yesterday</span><b><?= $yesterday_visitors ?></b></div>
        <div class="box"><span>Total Visits</span><b><?= $total_visits ?></b></div>
    </div>

    <div class="card">
        <h3>+ Upload New Prompt Photo</h3>
        <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
        <form action="panel.php" method="POST" enctype="multipart/form-data">
            <label>Prompt Title</label>
            <input type="text" name="title" placeholder="e.g. Rainy Urban Fashion Portrait" required>

            <label>Category</label>
            <select name="category">
                <option value="Trending Prompt">Trending Prompt</option>
                <option value="Portrait AI">Portrait AI</option>
                <option value="Cinematic">Cinematic</option>
            </select>

            <label>Choose Photo (Gallery)</label>
            <input type="file" name="image_file" accept="image/*">

            <label>Or Image Link (Optional)</label>
            <input type="url" name="image_url" placeholder="https://...">

            <label>AI Prompt Text (Jo Copy Hoga)</label>
            <textarea name="prompt" placeholder="Paste full prompt here..." required></textarea>

            <button type="submit" class="btn">Publish To Feed</button>
        </form>
    </div>

    <div class="card">
        <h3>Website Active Prompts (<?= count($all_posts) ?>)</h3>
        <?php foreach($all_posts as $p): ?>
            <div class="item">
                <div style="display:flex; align-items:center;">
                    <img src="<?= htmlspecialchars($p['image_url']) ?>">
                    <div>
                        <div style="font-size:13px; font-weight:bold;"><?= htmlspecialchars($p['title']) ?></div>
                        <div style="font-size:11px; color:#888;"><?= htmlspecialchars($p['category'] ?? 'Trending Prompt') ?></div>
                    </div>
                </div>
                <a href="panel.php?delete=<?= $p['id'] ?>" class="del-btn" onclick="return confirm('Delete karein?')">Delete</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
