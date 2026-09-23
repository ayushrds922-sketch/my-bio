<?php
$db = new PDO('sqlite:' . __DIR__ . '/faymas.db');
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db->prepare("SELECT * FROM prompts WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($item['title']) ?> - Ayux AI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: #0b0c10; color: #fff; padding-bottom: 30px; }

        .view-header {
            position: sticky;
            top: 0;
            background: rgba(11, 12, 16, 0.95);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #1a1c23;
            z-index: 100;
        }
        .btn-back {
            color: #38bdf8;
            text-decoration: none;
            font-size: 1.1rem;
            margin-right: 14px;
            font-weight: 700;
        }

        .post-card {
            max-width: 480px;
            margin: 14px auto;
            background: #141720;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid #202738;
        }
        .post-card img { width: 100%; display: block; }

        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 16px;
            border-bottom: 1px solid #1e2433;
        }
        .btn-copy {
            background: #ffffff;
            color: #000;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .prompt-content { padding: 16px; }
        .prompt-title { font-size: 1.15rem; font-weight: 700; margin-bottom: 10px; }
        .prompt-box {
            background: #090b10;
            border: 1px solid #252e42;
            padding: 12px;
            border-radius: 10px;
            font-size: 0.88rem;
            line-height: 1.45;
            color: #cbd5e1;
            margin-bottom: 14px;
        }

        .ai-buttons { display: flex; gap: 10px; }
        .btn-ai {
            flex: 1;
            text-align: center;
            padding: 11px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            color: #fff;
        }
        .chatgpt { background: #10a37f; }
        .gemini { background: #2563eb; }

        .toast {
            position: fixed;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            background: #10b981;
            color: #fff;
            padding: 9px 20px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: none;
            z-index: 999;
        }
    </style>
</head>
<body>

    <header class="view-header">
        <a href="index.php" class="btn-back">&#8592; Back</a>
        <div style="font-size: 0.95rem; font-weight: 600;">Prompt Detail</div>
    </header>

    <div style="padding: 10px;">
        <div class="post-card">
            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="Prompt Image">

            <div class="action-bar">
                <span style="font-size:0.8rem; color:#94a3b8;">Free AI Prompt</span>
                <button class="btn-copy" onclick="copyPrompt()">Copy Prompt</button>
            </div>

            <div class="prompt-content">
                <div class="prompt-title"><?= htmlspecialchars($item['title']) ?></div>
                <div class="prompt-box" id="promptText"><?= htmlspecialchars($item['prompt']) ?></div>

                <div style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 8px;">Create image directly on:</div>
                <div class="ai-buttons">
                    <a href="https://chatgpt.com" target="_blank" class="btn-ai chatgpt">Open ChatGPT</a>
                    <a href="https://gemini.google.com" target="_blank" class="btn-ai gemini">Open Gemini</a>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast">Prompt Copied to Clipboard!</div>

    <script>
    function copyPrompt() {
        const text = document.getElementById('promptText').innerText;
        navigator.clipboard.writeText(text).then(() => {
            const toast = document.getElementById('toast');
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 2000);
        });
    }
    </script>
</body>
</html>
