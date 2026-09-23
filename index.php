<?php
$db = new PDO('sqlite:' . __DIR__ . '/faymas.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Visitor Table Auto Create
$db->exec("CREATE TABLE IF NOT EXISTS visitors (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip_address TEXT,
    visit_time DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// Record this visitor visit
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$log_visit = $db->prepare("INSERT INTO visitors (ip_address) VALUES (?)");
$log_visit->execute([$user_ip]);

// Fetch Prompts
$posts = [];
try {
    $stmt = $db->query("SELECT * FROM prompts ORDER BY id DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayux AI - Prompt Feed</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background-color: #0b0c10; color: #ffffff; padding-bottom: 25px; }

        /* Clean Top Bar */
        .user-top-bar {
            position: sticky;
            top: 0;
            background: rgba(11, 12, 16, 0.95);
            backdrop-filter: blur(10px);
            padding: 16px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #1a1c23;
            z-index: 100;
        }

        .brand-title {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(90deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Pinterest Style Grid */
        .masonry-feed {
            column-count: 2;
            column-gap: 12px;
            padding: 14px 12px;
        }

        .pin-item {
            break-inside: avoid;
            margin-bottom: 12px;
            border-radius: 16px;
            overflow: hidden;
            background: #181a20;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(0,0,0,0.5);
            transition: transform 0.2s ease;
        }

        .pin-item:active { transform: scale(0.98); }

        .pin-item img {
            width: 100%;
            display: block;
            border-radius: 16px;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <!-- Header: Clean User Panel -->
    <header class="user-top-bar">
        <div class="brand-title">Ayux AI</div>
    </header>

    <!-- Photo Masonry Feed -->
    <main class="masonry-feed">
        <?php if (empty($posts)): ?>
            <div style="grid-column: 1 / -1; text-align: center; color: #64748b; padding: 50px 10px;">
                No prompts published yet.<br><br>
                Admin panel se prompt add karein.
            </div>
        <?php else: ?>
            <?php foreach ($posts as $p): ?>
                <div class="pin-item" onclick="location.href='view.php?id=<?= $p['id'] ?>'">
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="Prompt Image">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

</body>
</html>
