<?php
/**
 * BETELITE - Predictor Ticket combo compilation Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('PREDICTOR');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>🏆 Compile Combo Tips | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-md mx-auto py-8">
    <div class="glass-panel p-6 border-mintGreen/10">
      <h1 class="text-lg font-bold text-white mb-2">🏆 Design Prediction Combo Bundle</h1>
      <p class="text-xs text-gray-400 mb-6 leading-relaxed">Sellers of prediction tips can design combo bundle predictions (exactly 3 game tips) using the high-performance dedicated compiler tools inside the primary Telegram client UI interface.</p>
      <a href="/predictor/dashboard.php" class="btn-emerald-graded w-full text-center py-2.5 rounded-xl text-xs font-bold block mb-2">Back to Dashboard</a>
      <a href="/index.php" class="bg-[#1f2937] hover:bg-white/5 w-full text-center py-2.5 rounded-xl text-xs text-white block transition font-bold">Open Arena client</a>
    </div>
  </div>
</body>
</html>
