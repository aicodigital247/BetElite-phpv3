<?php
/**
 * BETELITE - REST API Admin control operations
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

require_role('ADMIN');

$action = clean_input($_POST['action'] ?? '');

if ($action === 'create_match') {
    $sport = clean_input($_POST['sport'] ?? 'Football');
    $home = clean_input($_POST['home_team'] ?? '');
    $away = clean_input($_POST['away_team'] ?? '');
    $home_logo = clean_input($_POST['home_logo'] ?? '🔴');
    $away_logo = clean_input($_POST['away_logo'] ?? '🔵');
    $hours = (int)($_POST['offset_hours'] ?? 2);
    
    $kickoff = date('Y-m-d H:i:s', strtotime("+$hours hours"));
    $status = 'SCHEDULED';
    
    $stmt = $conn->prepare("INSERT INTO `matches` (`sport`, `home_team`, `away_team`, `home_logo`, `away_logo`, `match_time`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssssss", $sport, $home, $away, $home_logo, $away_logo, $kickoff, $status);
        $stmt->execute();
        $newMatchId = $stmt->insert_id;
        $stmt->close();
        
        response_json(true, 'Sports game injected successfully to schedules database.', ['match_id' => $newMatchId]);
    } else {
        response_json(false, 'Query compilation failed to matches database.');
    }

} elseif ($action === 'score_update') {
    $matchId = (int)($_POST['match_id'] ?? 0);
    $home_score = (int)($_POST['home_score'] ?? 0);
    $away_score = (int)($_POST['away_score'] ?? 0);
    
    $stmt = $conn->prepare("UPDATE `matches` SET `home_score` = ?, `away_score` = ?, `status` = 'LIVE', `live_timer` = '65\'' WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("iii", $home_score, $away_score, $matchId);
        $stmt->execute();
        $stmt->close();
        response_json(true, 'Live game score variables updated.');
    } else {
        response_json(false, 'Match query settlement failed.');
    }

} elseif ($action === 'settle_match') {
    $matchId = (int)($_POST['match_id'] ?? 0);
    $status = clean_input($_POST['status'] ?? 'COMPLETED');
    
    $stmt = $conn->prepare("UPDATE `matches` SET `status` = ?, `live_timer` = 'FT' WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("si", $status, $matchId);
        $stmt->execute();
        $stmt->close();
        
        // Auto-settle predictions related to this match as WON or LOST randomly
        $outcome = mt_rand(0, 1) ? 'WON' : 'LOST';
        $pStmt = $conn->prepare("UPDATE `predictions` SET `status` = ? WHERE `match_id` = ? AND `status` = 'PENDING'");
        if ($pStmt) {
            $pStmt->bind_param("si", $outcome, $matchId);
            $pStmt->execute();
            $pStmt->close();
        }

        response_json(true, 'Sports events Settled. Outcome vectors pushed successfully.');
    } else {
        response_json(false, 'Settlement execution aborted.');
    }

} elseif ($action === 'change_role') {
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $newRole = clean_input($_POST['role'] ?? 'USER');
    
    $stmt = $conn->prepare("UPDATE `users` SET `role` = ? WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("si", $newRole, $targetUserId);
        $stmt->execute();
        $stmt->close();
        response_json(true, "User account upgrade to $newRole committed.");
    } else {
        response_json(false, 'Role update statement failed.');
    }
}

response_json(false, 'Invalid admin request payload mapping.');
?>
