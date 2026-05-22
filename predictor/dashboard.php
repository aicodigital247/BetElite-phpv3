<?php
/**
 * BETELITE - Predictor back-office dashboard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('PREDICTOR');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>📊 Predictor Center Dashboard | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] p-6 font-sans">
  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-6">
      <div>
        <h1 class="text-xl font-bold text-white flex items-center gap-2">📊 Predictor Performance Desk</h1>
        <p class="text-xs text-gray-400">Design Tickets, review sales stats, withdraw commissions securely.</p>
      </div>
      <a href="/index.php" class="text-xs text-emerald-400 bg-emerald-500/10 px-3.5 py-1.5 rounded-lg font-bold">Arena Home</a>
    </div>

    <!-- Predictor Quick Desk Grid link shortcuts -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
      <div class="glass-panel p-5">
        <h3 class="text-xs text-gray-400 font-semibold uppercase">Published combos</h3>
        <p class="text-3xl font-black text-white mt-1.5 font-mono">3 Active</p>
        <a href="/predictor/my_predictions.php" class="text-[10px] text-mintGreen block mt-2 font-semibold">View Ticket Statuses →</a>
      </div>
      <div class="glass-panel p-5">
        <h3 class="text-xs text-gray-400 font-semibold uppercase">Platform accuracy</h3>
        <p class="text-3xl font-black text-[#fbbf24] mt-1.5 font-mono">85.4%</p>
        <span class="text-[10px] text-gray-500 block mt-2 font-semibold">Superb Performance Grade</span>
      </div>
      <div class="glass-panel p-5">
        <h3 class="text-xs text-gray-400 font-semibold uppercase">Available Sales wallet</h3>
        <p class="text-3xl font-black text-white mt-1.5 font-mono">$<?= number_format($_SESSION['predictor_earnings'] ?? 480.00, 2) ?></p>
        <a href="/predictor/earnings.php" class="text-[10px] text-mintGreen block mt-2 font-semibold">Cashout Vault funds →</a>
      </div>
    </div>

    <div class="glass-panel p-6 text-center">
      <h3 class="text-md font-bold text-white mb-2">Build New Combo Sports Tips Bundle</h3>
      <p class="text-xs text-gray-400 mb-4">Select soccer matches scheduled by supervisor admins, compile selections and total odds coefficients, list ticket prices.</p>
      <a href="/predictor/create_prediction.php" class="btn-emerald-graded inline-block py-2.5 px-6 rounded-xl text-xs font-bold">Compile Tickets Combo</a>
    </div>
  </div>
</body>
</html>
