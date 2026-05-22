<?php
/**
 * BETELITE - REST API Live and Scheduled Sports events
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$matches = [];
$res = $conn->query("SELECT * FROM `matches` ORDER BY `id` ASC");

if ($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $matches[] = $row;
    }
} else {
    // Seed inline mocks as safety mechanism values
    $matches = [
        [
            'id' => 1,
            'sport' => 'Football',
            'home_team' => 'Manchester United',
            'away_team' => 'Chelsea',
            'home_logo' => '🔴',
            'away_logo' => '🔵',
            'match_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'status' => 'SCHEDULED',
            'home_score' => 0,
            'away_score' => 0,
            'live_timer' => null,
            'extra_stats' => null
        ],
        [
            'id' => 2,
            'sport' => 'Football',
            'home_team' => 'Real Madrid',
            'away_team' => 'Barcelona',
            'home_logo' => '⚪',
            'away_logo' => '🔵🔴',
            'match_time' => date('Y-m-d H:i:s', strtotime('-1 minute')),
            'status' => 'LIVE',
            'home_score' => 2,
            'away_score' => 1,
            'live_timer' => "57'",
            'extra_stats' => '{"possession":[52,48],"shots_on_target":[6,4],"yellow_cards":[1,2],"corners":[4,3]}'
        ],
        [
            'id' => 3,
            'sport' => 'Basketball',
            'home_team' => 'LA Lakers',
            'away_team' => 'Golden State Warriors',
            'home_logo' => '🟡',
            'away_logo' => '🔵🟡',
            'match_time' => date('Y-m-d H:i:s', strtotime('+4 hours')),
            'status' => 'SCHEDULED',
            'home_score' => 0,
            'away_score' => 0,
            'live_timer' => null,
            'extra_stats' => null
        ],
        [
            'id' => 4,
            'sport' => 'Tennis',
            'home_team' => 'Novak Djokovic',
            'away_team' => 'Carlos Alcaraz',
            'home_logo' => '🎾',
            'away_logo' => '🇪🇸',
            'match_time' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            'status' => 'COMPLETED',
            'home_score' => 3,
            'away_score' => 1,
            'live_timer' => 'FT',
            'extra_stats' => '{"aces":[12,8],"double_faults":[2,4],"unforced_errors":[24,31]}'
        ],
        [
            'id' => 5,
            'sport' => 'eSports',
            'home_team' => 'Natus Vincere',
            'away_team' => 'FaZe Clan',
            'home_logo' => '💛',
            'away_logo' => '❤️',
            'match_time' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'status' => 'SCHEDULED',
            'home_score' => 0,
            'away_score' => 0,
            'live_timer' => null,
            'extra_stats' => null
        ]
    ];
}

response_json(true, 'Live and Scheduled sports matches fetched.', [
    'matches' => $matches
]);
