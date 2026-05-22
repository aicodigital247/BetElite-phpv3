<?php
/**
 * BETELITE - Admin platform parameters settings system
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>⚙️ Platform Settings | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-4xl mx-auto text-center py-12">
    <div class="glass-panel p-8 max-w-lg mx-auto border-red-500/10 shadow-2xl">
      <h1 class="text-2xl font-black text-white mb-3">⚙️ System Configuration Settings</h1>
      <p class="text-xs text-gray-400 mb-6 font-medium leading-relaxed">Platform commission splits, Telegram webapp credentials, payout rules, API rate-limiting thresholds and security parameters are configurable directly in your secure environment variable file parameters.</p>
      <a href="/admin/index.php" class="btn-emerald-graded py-2.5 px-6 rounded-xl text-xs font-bold inline-block">返回 Supervisor Home</a>
    </div>
  </div>
</body>
</html>
