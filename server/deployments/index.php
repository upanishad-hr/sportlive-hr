<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployments - SPORT LIVE</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            padding: 2rem;
        }
        .container { max-width: 800px; margin: 0 auto; }
        h1 { font-size: 1.5rem; margin-bottom: 1.5rem; color: #f8fafc; }
        .count { color: #64748b; font-size: 0.875rem; margin-bottom: 1rem; }
        .deployments { display: flex; flex-direction: column; gap: 0.5rem; }
        .deployment {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .deployment:hover { border-color: #475569; }
        .deployment.main { border-left: 3px solid #22c55e; }
        .branch { font-weight: 500; color: #f8fafc; }
        .badge {
            background: #22c55e;
            color: #0f172a;
            padding: 0.125rem 0.5rem;
            border-radius: 4px;
            font-size: 0.625rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        .meta { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; }
        .url a { color: #38bdf8; text-decoration: none; font-size: 0.875rem; }
        .url a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h1>SPORT LIVE Deployments</h1>
        <?php
        $deployments = [];
        foreach (glob("/var/www/sportlive*") as $dir) {
            if (!is_dir($dir)) continue;
            $name = basename($dir);
            $mtime = filemtime($dir);
            if ($name === 'sportlive') {
                $branch = 'main';
                $url = 'https://sportlive.upanishad.hr';
                $isMain = true;
            } else {
                $branch = preg_replace('/^sportlive-/', '', $name);
                $url = "https://$branch.sportlive.upanishad.hr";
                $isMain = false;
            }
            $deployments[] = compact('branch', 'url', 'mtime', 'isMain');
        }
        usort($deployments, fn($a, $b) => $a['isMain'] ? -1 : ($b['isMain'] ? 1 : $b['mtime'] - $a['mtime']));
        ?>
        <div class="count"><?= count($deployments) ?> deployments</div>
        <div class="deployments">
            <?php foreach ($deployments as $d): ?>
            <div class="deployment<?= $d['isMain'] ? ' main' : '' ?>">
                <div>
                    <div class="branch">
                        <?= htmlspecialchars($d['branch']) ?>
                        <?php if ($d['isMain']): ?><span class="badge">Production</span><?php endif; ?>
                    </div>
                    <div class="meta"><?= date('M j, Y \a\t H:i', $d['mtime']) ?></div>
                </div>
                <div class="url"><a href="<?= $d['url'] ?>" target="_blank"><?= $d['url'] ?></a></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
