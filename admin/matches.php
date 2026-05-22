<?php
/**
 * BETELITE - Admin Matches deployment Center
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');

// Handle match creation via raw browser form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['home_team'])) {
    $sport = clean_input($_POST['sport'] ?? 'Football');
    $home = clean_input($_POST['home_team'] ?? '');
    $away = clean_input($_POST['away_team'] ?? '');
    $home_logo = clean_input($_POST['home_logo'] ?? '🔴');
    $away_logo = clean_input($_POST['away_logo'] ?? '🔵');
    $kickoff = date('Y-m-d H:i:s', strtotime('+2 hours'));
    
    $stmt = $conn->prepare("INSERT INTO `matches` (`sport`, `home_team`, `away_team`, `home_logo`, `away_logo`, `match_time`, `status`) VALUES (?, ?, ?, ?, ?, ?, 'SCHEDULED')");
    if ($stmt) {
        $stmt->bind_param("ssssss", $sport, $home, $away, $home_logo, $away_logo, $kickoff);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch active matches
$matches = [];
$res = $conn->query("SELECT * FROM `matches` ORDER BY `id` DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $matches[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏆 Admin Match Injector | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">

  <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1">
      <div class="glass-panel p-5">
        <h2 class="text-xs text-orange-400 font-extrabold pb-2 border-b border-white/5 uppercase tracking-wider mb-4">Add Match Event</h2>
        
        <form method="POST" class="space-y-4 text-xs">
          <div>
            <label class="font-bold text-gray-400 block mb-1">Sport Category</label>
            <select name="sport" class="bg-bgDark border border-white/10 text-white rounded p-2.5 w-full">
              <option value="Football">Football</option>
              <option value="Basketball">Basketball</option>
              <option value="Tennis">Tennis</option>
              <option value="eSports">eSports</option>
            </select>
          </div>
          <div>
            <label class="font-bold text-gray-400 block mb-1">Home Team</label>
            <input type="text" name="home_team" placeholder="Arsenal" required class="bg-bgDark border border-white/10 text-white rounded p-2.5 w-full">
          </div>
          <div>
            <label class="font-bold text-gray-400 block mb-1">Away Team</label>
            <input type="text" name="away_team" placeholder="Liverpool" required class="bg-bgDark border border-white/10 text-white rounded p-2.5 w-full">
          </div>
          <button type="submit" class="w-full btn-emerald-graded py-2 rounded text-xs">Inject Scheduled Match</button>
        </form>
      </div>
    </div>

    <!-- Active Events list -->
    <div class="md:col-span-2 space-y-4">
      <div class="flex items-center justify-between border-b border-white/5 pb-2.5">
        <h2 class="text-sm font-extrabold text-white uppercase">Matched Schedules Database</h2>
        <a href="/admin/index.php" class="text-xs text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded font-bold">Admin Home</a>
      </div>

      <div class="glass-panel overflow-hidden">
        <div class="p-4 divide-y divide-white/5 space-y-3">
          <?php foreach($matches as $m): ?>
            <div class="flex items-center justify-between py-2.5 text-xs">
              <div>
                <span class="text-[9px] bg-slate-800 text-gray-300 font-mono px-1.5 py-0.5 rounded font-extrabold uppercase"><?= $m['sport'] ?></span>
                <span class="font-bold text-white ml-2"><?= htmlspecialchars($m['home_team'] . ' vs ' . $m['away_team']) ?></span>
              </div>
              <span class="font-mono text-gray-400">[<?= $m['home_score'] ?>:<?= $m['away_score'] ?>] - <?= $m['status'] ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
