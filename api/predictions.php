<?php
/**
 * BETELITE - REST API Predictions Listing Center
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Fetch lists
$predictions = [];
$res = $conn->query("SELECT * FROM `predictions` ORDER BY `id` DESC");

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $predictions[] = $row;
    }
} else {
    // Seed default predictions returned inline as fallback if SQL is empty
    $predictions = [
        [
            'id' => 1,
            'predictor_id' => 2,
            'match_id' => 1,
            'title' => 'Man Utd vs Chelsea - Super Combo',
            'description' => 'Highly analysed bundle for the Manchester Derby-like rival event. Safe odds.',
            'price' => '15.00',
            'tips_json' => '[{"prediction":"Match Winner","option":"Manchester United","odds":"2.10","confidence":"82"},{"prediction":"Both Teams to Score","option":"Yes","odds":"1.65","confidence":"78"},{"prediction":"Total Goals","option":"Over 2.5","odds":"1.75","confidence":"85"}]',
            'total_odds' => '6.06',
            'confidence' => 81,
            'is_vip' => 0,
            'status' => 'PENDING',
            'sales_count' => 4,
            'views' => 32,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 2,
            'predictor_id' => 2,
            'match_id' => 2,
            'title' => 'El Clasico Live VIP Ticket',
            'description' => 'Premium live tips for the ongoing El Clasico. Extremely hot.',
            'price' => '25.00',
            'tips_json' => '[{"prediction":"Next Goal (3rd Goal)","option":"Real Madrid","odds":"2.40","confidence":"90"},{"prediction":"Total Cards","option":"Over 4.5","odds":"1.80","confidence":"95"}]',
            'total_odds' => '4.32',
            'confidence' => 92,
            'is_vip' => 1,
            'status' => 'PENDING',
            'sales_count' => 2,
            'views' => 15,
            'created_at' => date('Y-m-d H:i:s')
        ],
        [
            'id' => 3,
            'predictor_id' => 2,
            'match_id' => 4,
            'title' => 'Tennis Finals Masterpiece',
            'description' => 'The Novak Djokovic masterclass prediction bundle.',
            'price' => '10.00',
            'tips_json' => '[{"prediction":"Match Winner","option":"Novak Djokovic","odds":"1.60","confidence":"95"},{"prediction":"Set Handicap","option":"Djokovic -1.5","odds":"1.90","confidence":"90"}]',
            'total_odds' => '3.04',
            'confidence' => 92,
            'is_vip' => 0,
            'status' => 'WON',
            'sales_count' => 9,
            'views' => 84,
            'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
        ]
    ];
}

response_json(true, 'Predictions listings successfully fetched.', [
    'predictions' => $predictions
]);
