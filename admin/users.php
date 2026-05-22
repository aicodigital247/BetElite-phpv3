<?php
/**
 * BETELITE - Admin User Accounts Dashboard Page
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');

// Handle Role Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $uid = (int)$_POST['user_id'];
    $role = clean_input($_POST['role'] ?? 'USER');
    
    $stmt = $conn->prepare("UPDATE `users` SET `role` = ? WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("si", $role, $uid);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch users
$users = [];
$res = $conn->query("SELECT * FROM `users` ORDER BY `id` ASC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🏆 Admin User Control | BETELITE</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-[#0b0c10] text-[#f1f3f5] font-sans p-6">

  <div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between border-b border-white/5 pb-4 mb-6">
      <div>
        <h1 class="text-xl font-bold text-white flex items-center gap-2">👤 User Account Directory</h1>
        <p class="text-xs text-gray-400">Upgrade normal players to VIP tags or assign PREDICTOR badges.</p>
      </div>
      <a href="/admin/index.php" class="text-xs text-emerald-400 bg-emerald-500/10 px-3.5 py-1.5 rounded-lg font-bold">Admin Home</a>
    </div>

    <div class="glass-panel overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-white/10 bg-white/5 text-gray-400">
              <th class="p-3.5">ID</th>
              <th class="p-3.5">Username</th>
              <th class="p-3.5">Full Name</th>
              <th class="p-3.5">System Privilege</th>
              <th class="p-3.5">VIP tag</th>
              <th class="p-3.5 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <?php foreach($users as $u): ?>
            <tr>
              <td class="p-3.5 font-mono text-gray-400"><?= $u['id'] ?></td>
              <td class="p-3.5 font-bold text-white">@<?= htmlspecialchars($u['username']) ?></td>
              <td class="p-3.5 text-gray-300"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?></td>
              <td class="p-3.5">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-white"><?= $u['role'] ?></span>
              </td>
              <td class="p-3.5">
                <span class="text-goldVip font-bold"><?= $u['is_vip'] ? '👑 ACTIVE' : '❌' ?></span>
              </td>
              <td class="p-3.5 text-right">
                <form method="POST" class="inline-flex gap-1.5">
                  <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                  <select name="role" onchange="this.form.submit()" class="bg-[#0d0e12] border border-white/10 rounded px-2 py-1 text-[10px] text-white">
                    <option value="USER" <?= $u['role'] === 'USER' ? 'selected' : '' ?>>User</option>
                    <option value="PREDICTOR" <?= $u['role'] === 'PREDICTOR' ? 'selected' : '' ?>>Predictor</option>
                    <option value="ADMIN" <?= $u['role'] === 'ADMIN' ? 'selected' : '' ?>>Admin</option>
                  </select>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</body>
</html>
