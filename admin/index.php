<?php
/**
 * BETELITE - Admin Dashboard Overview Back-office Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Secure Session Guard
require_role('ADMIN');

// Simple stats query fallback to sample numbers if offline
$stats = [
    'users' => 0,
    'predictors' => 0,
    'predictions' => 0,
    'revenue' => 0.00
];

if ($uq = $conn->query("SELECT COUNT(*) as cnt FROM `users`")) $stats['users'] = $uq->fetch_assoc()['cnt'];
if ($pq = $conn->query("SELECT COUNT(*) as cnt FROM `users` WHERE `role` = 'PREDICTOR'")) $stats['predictors'] = $pq->fetch_assoc()['cnt'];
if ($prq = $conn->query("SELECT COUNT(*) as cnt FROM `predictions`")) $stats['predictions'] = $prq->fetch_assoc()['cnt'];
if ($eq = $conn->query("SELECT SUM(`amount`) as total FROM `earnings`")) $stats['revenue'] = (double)$eq->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏆 Admin Super-Control Dashboard | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] font-sans">
  
  <div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-white/5 pb-6 mb-8">
      <div>
        <span class="text-xs bg-red-500/10 text-red-400 border border-red-500/20 px-2.5 py-1 rounded font-extrabold tracking-widest uppercase">Admin Security Operations</span>
        <h1 class="text-2xl font-black text-white mt-1.5 flex items-center gap-1.5 tracking-tight">🏆 BETELITE Head Office</h1>
        <p class="text-xs text-gray-400 mt-1">Global SaaS platform supervision, user metrics, payouts, and football event injector parameters.</p>
      </div>
      <a href="/index.php" class="bg-[#1f2937]/80 hover:bg-[#374151] border border-white/5 text-xs text-white px-4 py-2 rounded-xl font-bold transition">返回 Main View</a>
    </div>

    <!-- Quick Navigation Dashboard Grid Links -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <a href="/admin/users.php" class="glass-panel p-4 hover:border-mintGreen/30 transition block">
        <h3 class="text-xs text-gray-400 font-semibold uppercase">Manage Users</h3>
        <p class="text-2xl font-black text-white font-mono mt-1"><?= $stats['users'] ?></p>
        <span class="text-[10px] text-mintGreen font-medium mt-1 block">Inspect Profiles →</span>
      </a>
      <a href="/admin/predictions.php" class="glass-panel p-4 hover:border-mintGreen/30 transition block">
        <h3 class="text-xs text-gray-400 font-semibold uppercase">My Marketplace</h3>
        <p class="text-2xl font-black text-white font-mono mt-1"><?= $stats['predictions'] ?></p>
        <span class="text-[10px] text-mintGreen font-medium mt-1 block">Verify Wins →</span>
      </a>
      <a href="/admin/matches.php" class="glass-panel p-4 hover:border-mintGreen/30 transition block">
        <h3 class="text-xs text-gray-400 font-semibold uppercase">Schedules Injector</h3>
        <p class="text-2xl font-black text-[#fbbf24] font-mono mt-1">Live Games</p>
        <span class="text-[10px] text-mintGreen font-medium mt-1 block">Deploy Matches →</span>
      </a>
      <a href="/admin/payments.php" class="glass-panel p-4 hover:border-mintGreen/30 transition block">
        <h3 class="text-xs text-gray-400 font-semibold uppercase">Bank Cashouts</h3>
        <p class="text-2xl font-black text-white font-mono mt-1">$<?= number_format($stats['revenue'], 2) ?></p>
        <span class="text-[10px] text-mintGreen font-medium mt-1 block">Approve Payouts →</span>
      </a>
    </div>

    <!-- Inner grid listing details -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="glass-panel p-6 md:col-span-2">
        <h3 class="text-sm font-extrabold text-white uppercase border-b border-white/5 pb-3 mb-4">⚙️ Platform Operations Ticker</h3>
        <div class="space-y-3 font-mono text-[11px] text-gray-400">
          <p class="flex justify-between border-b border-white/5 pb-1.5"><span>PHP Engine Runtime:</span> <span class="text-white">v8.2 (Secure)</span></p>
          <p class="flex justify-between border-b border-white/5 pb-1.5"><span>MySQLi Status:</span> <span class="text-emerald-400 font-bold">Online</span></p>
          <p class="flex justify-between border-b border-white/5 pb-1.5"><span>Platform Royalty Fee Split:</span> <span class="text-white">20% Admin, 80% Seller</span></p>
          <p class="flex justify-between"><span>Active Session ID:</span> <span class="text-white truncate max-w-xs"><?= session_id() ?></span></p>
        </div>
      </div>
      
      <!-- Quick Info panel -->
      <div class="glass-panel p-6">
        <h3 class="text-sm font-extrabold text-white uppercase border-b border-white/5 pb-3 mb-3">📢 Booster Campaigns</h3>
        <p class="text-xs text-gray-400 leading-relaxed mb-4">Administrate ongoing featured sponsor slots, click conversions, and popups inside the mini app viewport.</p>
        <a href="/admin/ads.php" class="btn-emerald-graded text-center w-full block py-2.5 rounded-xl text-xs font-bold">Launch Campaigns Center</a>
      </div>
    </div>
  </div>

</body>
</html>
