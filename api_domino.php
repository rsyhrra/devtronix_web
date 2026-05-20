<?php
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$data_dir = __DIR__ . '/domino_data/';

if (!is_dir($data_dir)) {
    mkdir($data_dir, 0777, true);
}

// Helpers
function get_room_file($room_id) {
    global $data_dir;
    return $data_dir . preg_replace('/[^a-zA-Z0-9]/', '', $room_id) . '.json';
}

function get_room_state($room_id) {
    $file = get_room_file($room_id);
    if (!file_exists($file)) return null;
    return json_decode(file_get_contents($file), true);
}

function save_room_state($room_id, $state) {
    $state['last_activity'] = time();
    $file = get_room_file($room_id);
    file_put_contents($file, json_encode($state, JSON_PRETTY_PRINT));
}

// Core Game Functions
function generate_deck() {
    $deck = [];
    for ($i = 0; $i <= 6; $i++) {
        for ($j = $i; $j <= 6; $j++) {
            $deck[] = [$i, $j];
        }
    }
    shuffle($deck);
    return $deck;
}

function determine_first_player($hands) {
    // Look for 6-6, 5-5, etc.
    for ($d = 6; $d >= 0; $d--) {
        foreach ($hands as $p_id => $hand) {
            foreach ($hand as $tile) {
                if ($tile[0] == $d && $tile[1] == $d) {
                    return $p_id;
                }
            }
        }
    }
    return array_key_first($hands);
}

function check_game_over(&$state) {
    // 1. Someone has 0 cards
    foreach ($state['players'] as $p) {
        $p_id = $p['id'];
        if (count($state['hands'][$p_id]) == 0) {
            $state['status'] = 'finished';
            $state['winner'] = $p_id;
            return;
        }
    }
    
    // 2. Deadlock (Gaple)
    $all_passed = true;
    foreach ($state['players'] as $p) {
        if (!$p['passed']) {
            $all_passed = false;
            break;
        }
    }
    
    if ($all_passed) {
        $state['status'] = 'finished';
        $state['winner'] = 'draw';
        $min_dots = 999;
        $winner_id = null;
        
        foreach ($state['players'] as $p) {
            $p_id = $p['id'];
            $dots = 0;
            foreach ($state['hands'][$p_id] as $tile) {
                $dots += $tile[0] + $tile[1];
            }
            if ($dots < $min_dots) {
                $min_dots = $dots;
                $winner_id = $p_id;
            }
        }
        $state['winner'] = $winner_id;
    }
}

// Handlers
if ($action == 'create_room') {
    $room_id = substr(md5(uniqid()), 0, 6);
    $p_id = uniqid();
    $name = substr($_POST['name'] ?? 'Player', 0, 20);
    
    $state = [
        'room_id' => $room_id,
        'status' => 'waiting',
        'players' => [
            ['id' => $p_id, 'name' => $name, 'passed' => false]
        ],
        'hands' => [],
        'board' => [],
        'turn_index' => 0,
        'left_end' => null,
        'right_end' => null,
        'winner' => null,
        'last_activity' => time()
    ];
    
    save_room_state($room_id, $state);
    echo json_encode(['success' => true, 'room_id' => $room_id, 'player_id' => $p_id]);
    exit;
}

if ($action == 'join_room') {
    $room_id = $_POST['room_id'] ?? '';
    $name = substr($_POST['name'] ?? 'Player', 0, 20);
    $req_p_id = $_POST['player_id'] ?? '';
    $state = get_room_state($room_id);
    
    if (!$state) {
        echo json_encode(['success' => false, 'error' => 'Room not found']);
        exit;
    }
    
    if ($state['status'] != 'waiting') {
        echo json_encode(['success' => false, 'error' => 'Game already started']);
        exit;
    }
    
    // Check if user is already in room (re-join)
    if (!empty($req_p_id)) {
        foreach ($state['players'] as $p) {
            if ($p['id'] == $req_p_id) {
                echo json_encode(['success' => true, 'room_id' => $room_id, 'player_id' => $p['id']]);
                exit;
            }
        }
    }

    // Ensure unique name
    $original_name = $name;
    $counter = 2;
    $name_exists = true;
    while ($name_exists) {
        $name_exists = false;
        foreach ($state['players'] as $p) {
            if ($p['name'] == $name) {
                $name_exists = true;
                $name = $original_name . ' ' . $counter;
                $counter++;
                break;
            }
        }
    }
    
    if (count($state['players']) >= 4) {
        echo json_encode(['success' => false, 'error' => 'Room is full']);
        exit;
    }
    
    $p_id = uniqid();
    $state['players'][] = ['id' => $p_id, 'name' => $name, 'passed' => false];
    
    if (count($state['players']) == 4) {
        // Start game
        $state['status'] = 'playing';
        $deck = generate_deck();
        
        $state['hands'] = [];
        foreach ($state['players'] as $index => $p) {
            $state['hands'][$p['id']] = array_splice($deck, 0, 7);
        }
        
        $first_id = determine_first_player($state['hands']);
        foreach ($state['players'] as $idx => $p) {
            if ($p['id'] == $first_id) {
                $state['turn_index'] = $idx;
                break;
            }
        }
    }
    
    save_room_state($room_id, $state);
    echo json_encode(['success' => true, 'room_id' => $room_id, 'player_id' => $p_id]);
    exit;
}

if ($action == 'leave_room') {
    $room_id = $_POST['room_id'] ?? '';
    $p_id = $_POST['player_id'] ?? '';
    $state = get_room_state($room_id);
    
    if ($state && $state['status'] == 'waiting') {
        $new_players = [];
        foreach ($state['players'] as $p) {
            if ($p['id'] !== $p_id) {
                $new_players[] = $p;
            }
        }
        $state['players'] = $new_players;
        save_room_state($room_id, $state);
    }
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action == 'get_state') {
    $room_id = $_GET['room_id'] ?? '';
    $p_id = $_GET['player_id'] ?? '';
    $state = get_room_state($room_id);
    
    if (!$state) {
        echo json_encode(['success' => false, 'error' => 'Room not found']);
        exit;
    }

    $time_now = time();
    $all_active = true;
    $disconnected_player = null;

    foreach ($state['players'] as &$p) {
        if ($p['id'] == $p_id) {
            $p['last_seen'] = $time_now;
        }
        
        if (in_array($state['status'], ['playing', 'paused'])) {
            $last = $p['last_seen'] ?? $time_now;
            if ($time_now - $last > 8) {
                $all_active = false;
                if (!$disconnected_player) $disconnected_player = $p['name'];
            }
        }
    }
    unset($p);

    if ($state['status'] == 'playing' && !$all_active) {
        $state['status'] = 'paused';
        $state['disconnect_timer'] = $time_now + 30;
        $state['disconnected_player'] = $disconnected_player;
    } else if ($state['status'] == 'paused') {
        if ($all_active) {
            $state['status'] = 'playing';
            unset($state['disconnect_timer']);
            unset($state['disconnected_player']);
        } else {
            if ($time_now > ($state['disconnect_timer'] ?? 0)) {
                $state['status'] = 'aborted';
            } else {
                $state['disconnected_player'] = $disconnected_player;
            }
        }
    }

    $safe_state = $state;
    // Update state file to record last_seen and status changes
    save_room_state($room_id, $state);
    
    $safe_state['current_time'] = $time_now; // send server time for countdown
    $safe_state['my_hand'] = isset($state['hands'][$p_id]) ? $state['hands'][$p_id] : [];
    
    $hand_counts = [];
    foreach ($state['players'] as $p) {
        $pid = $p['id'];
        $hand_counts[$pid] = isset($state['hands'][$pid]) ? count($state['hands'][$pid]) : 0;
    }
    $safe_state['hand_counts'] = $hand_counts;
    unset($safe_state['hands']);
    
    $safe_state['success'] = true;
    echo json_encode($safe_state);
    exit;
}

if ($action == 'play_card') {
    $room_id = $_POST['room_id'] ?? '';
    $p_id = $_POST['player_id'] ?? '';
    $tile_json = $_POST['tile'] ?? '[]';
    $side = $_POST['side'] ?? 'right';
    
    $tile = json_decode($tile_json);
    $state = get_room_state($room_id);
    
    if (!$state || $state['status'] != 'playing') {
        echo json_encode(['success' => false, 'error' => 'Invalid game state']);
        exit;
    }
    
    $current_turn_p_id = $state['players'][$state['turn_index']]['id'];
    if ($current_turn_p_id != $p_id) {
        echo json_encode(['success' => false, 'error' => 'Not your turn']);
        exit;
    }
    
    $has_tile = false;
    $tile_idx = -1;
    foreach ($state['hands'][$p_id] as $idx => $t) {
        if (($t[0] == $tile[0] && $t[1] == $tile[1]) || ($t[0] == $tile[1] && $t[1] == $tile[0])) {
            $has_tile = true;
            $tile_idx = $idx;
            $tile = $t;
            break;
        }
    }
    
    if (!$has_tile) {
        echo json_encode(['success' => false, 'error' => 'Tile not in hand']);
        exit;
    }
    
    $board_empty = empty($state['board']);
    $valid = false;
    $placed_tile = $tile;
    
    if ($board_empty) {
        $valid = true;
        $state['left_end'] = $tile[0];
        $state['right_end'] = $tile[1];
    } else {
        if ($side == 'left') {
            if ($tile[1] == $state['left_end']) {
                $placed_tile = [$tile[0], $tile[1]];
                $state['left_end'] = $tile[0];
                $valid = true;
            } else if ($tile[0] == $state['left_end']) {
                $placed_tile = [$tile[1], $tile[0]];
                $state['left_end'] = $tile[1];
                $valid = true;
            }
        } else {
            if ($tile[0] == $state['right_end']) {
                $placed_tile = [$tile[0], $tile[1]];
                $state['right_end'] = $tile[1];
                $valid = true;
            } else if ($tile[1] == $state['right_end']) {
                $placed_tile = [$tile[1], $tile[0]];
                $state['right_end'] = $tile[0];
                $valid = true;
            }
        }
    }
    
    if (!$valid) {
        echo json_encode(['success' => false, 'error' => 'Invalid move']);
        exit;
    }
    
    array_splice($state['hands'][$p_id], $tile_idx, 1);
    
    if ($board_empty || $side == 'right') {
        $state['board'][] = ['player' => $p_id, 'tile' => $placed_tile, 'side' => 'right'];
    } else {
        array_unshift($state['board'], ['player' => $p_id, 'tile' => $placed_tile, 'side' => 'left']);
    }
    
    foreach ($state['players'] as &$p) {
        $p['passed'] = false;
    }
    
    $state['turn_index'] = ($state['turn_index'] + 1) % 4;
    
    check_game_over($state);
    save_room_state($room_id, $state);
    
    echo json_encode(['success' => true]);
    exit;
}

if ($action == 'pass') {
    $room_id = $_POST['room_id'] ?? '';
    $p_id = $_POST['player_id'] ?? '';
    
    $state = get_room_state($room_id);
    if (!$state || $state['status'] != 'playing') {
        echo json_encode(['success' => false, 'error' => 'Invalid game state']);
        exit;
    }
    
    $current_turn_p_id = $state['players'][$state['turn_index']]['id'];
    if ($current_turn_p_id != $p_id) {
        echo json_encode(['success' => false, 'error' => 'Not your turn']);
        exit;
    }
    
    $has_valid_move = false;
    $le = $state['left_end'];
    $re = $state['right_end'];
    foreach ($state['hands'][$p_id] as $t) {
        if ($t[0] == $le || $t[1] == $le || $t[0] == $re || $t[1] == $re) {
            $has_valid_move = true;
            break;
        }
    }
    
    if ($has_valid_move) {
        echo json_encode(['success' => false, 'error' => 'You have a valid tile, you cannot pass!']);
        exit;
    }
    
    $state['players'][$state['turn_index']]['passed'] = true;
    $state['turn_index'] = ($state['turn_index'] + 1) % 4;
    
    check_game_over($state);
    save_room_state($room_id, $state);
    
    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
