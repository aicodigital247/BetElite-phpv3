<?php
/**
 * BETELITE - Admin Promotional Ad Banners and campaigns Configuration Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');

// Handle new campaign injection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = clean_input($_POST['title'] ?? '');
    $img = clean_input($_POST['image_url'] ?? '');
    $link = clean_input($_POST['link'] ?? '');
    $status = 'ACTIVE';
    
    $stmt = $conn->prepare("INSERT INTO `ads` (`title`, `image_url`, `link`, `type`, `status`, `start_date`, `end_date`) VALUES (?, ?, ?, 'BANNER', 'ACTIVE', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))");
    if ($stmt) {
        $stmt->bind_param("sss", $title, $img, $link);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch campaigns list
$campaigns = [];
$res = $conn->query("SELECT * FROM `ads` ORDER BY `id` DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $campaigns[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏆 Admin Ad Campaigns Manager | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
      <div class="glass-panel p-5">
        <h2 class="text-xs text-amber-500 font-extrabold pb-2 border-b border-white/5 uppercase tracking-wider mb-4">Deploy Ad Banner</h2>
        <form method="POST" class="space-y-4 text-xs">
          <div>
            <label class="font-bold text-gray-400 block mb-1">Banner Title</label>
            <input type="text" name="title" placeholder="Boost your profit with 100+ Odds Combos" required class="bg-bgDark border border-white/10 text-white rounded p-2.5 w-full">
          </div>
          <div>
            <label class="font-bold text-gray-400 block mb-1">Banner Image URL</label>
            <input type="text" name="image_url" placeholder="https://unsplash..." required class="bg-bgDark border border-white/10 text-white rounded p-2.5 w-full">
          </div>
          <div>
            <label class="font-bold text-gray-400 block mb-1">Target link / hash</label>
            <input type="text" name="link" placeholder="#wallet" required class="bg-bgDark border border-white/10 text-white rounded p-2.5 w-full">
          </div>
          <button type="submit" class="w-full btn-emerald-graded py-2.5 rounded text-xs font-bold">Launch Campign Active</button>
        </form>
      </div>
    </div>

    <!-- Active campaigns -->
    <div class="md:col-span-2 space-y-4">
      <div class="flex items-center justify-between border-b border-white/5 pb-2.5">
        <h2 class="text-sm font-extrabold text-white uppercase">Sponsorship Campaign Ledger</h2>
        <a href="/admin/index.php" class="text-xs text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded font-bold">Admin Home</a>
      </div>

      <div class="glass-panel overflow-hidden">
        <div class="p-4 divide-y divide-white/5 space-y-3">
          <?php foreach($campaigns as $ad): ?>
            <div class="flex items-center justify-between py-2.5 text-xs">
              <div>
                <span class="text-[9px] bg-amber-500 text-bgDark font-mono px-1.5 py-0.5 rounded font-extrabold uppercase">BANNER</span>
                <span class="font-bold text-white ml-2"><?= htmlspecialchars($ad['title']) ?></span>
              </div>
              <span class="font-mono text-gray-400"><?= $ad['clicks'] ?> clicks / <?= $ad['status'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
