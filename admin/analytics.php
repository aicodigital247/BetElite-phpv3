<?php
/**
 * BETELITE - Admin global analytics dashboard page template
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🏆 Global Analytics | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-4xl mx-auto text-center py-12">
    <div class="glass-panel p-8 max-w-lg mx-auto border-mintGreen/10">
      <h1 class="text-2xl font-black text-white mb-3">📊 Analytics Platform Hub</h1>
      <p class="text-xs text-gray-400 mb-6 font-medium leading-relaxed">Global data ingestion, active sports conversion matrices, retention multipliers, and detailed transaction charts are synchronized in real-time inside the main Admin Control tab on your Telegram Client HUD toolbar.</p>
      <a href="/admin/index.php" class="btn-emerald-graded py-2.5 px-6 rounded-xl text-xs font-bold inline-block">返回 Supervisor Home</a>
    </div>
  </div>
</body>
</html>
