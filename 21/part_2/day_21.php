<?php
/**
 * Keypad Conundrum - PHP Solution
 *
 * This script translates a Python solution to PHP for solving the Keypad Conundrum problem.
 * It reads input codes from a file, calculates the shortest sequence of button presses required
 * to type each code, computes the complexity for each code, and sums these complexities.
 */

/**
 * Reads the input file and returns an array of codes.
 *
 * @param string $filePath Path to the input file.
 * @return array Array of codes.
 */
function readInput($filePath) {
    $data = file_get_contents($filePath);
    $lines = explode(PHP_EOL, trim($data));
    return array_filter($lines, function($line) {
        return trim($line) !== '';
    });
}

/**
 * Generates all combinations of selecting $k elements from a set of size $n.
 *
 * @param int $n Total number of elements.
 * @param int $k Number of elements to choose.
 * @return array Array of combinations, each combination is an array of indices.
 */
function combinations($n, $k) {
    $result = [];
    $combo = [];
    function combHelper($n, $k, $start, &$combo, &$result) {
        if ($k == 0) {
            $result[] = $combo;
            return;
        }
        for ($i = $start; $i <= $n - $k; $i++) {
            $combo[] = $i;
            combHelper($n, $k - 1, $i + 1, $combo, $result);
            array_pop($combo);
        }
    }
    combHelper($n, $k, 0, $combo, $result);
    return $result;
}

/**
 * Generates all possible sequences by interleaving 'ca' and 'cb' moves.
 *
 * @param string $ca Character A (e.g., 'v', '^', etc.).
 * @param int $a Number of times to press 'ca'.
 * @param string $cb Character B (e.g., '>', '<', etc.).
 * @param int $b Number of times to press 'cb'.
 * @return array Array of unique sequences.
 */
function get_combos($ca, $a, $cb, $b) {
    $n = $a + $b;
    $combos = [];
    $combinations = combinations($n, $a);
    foreach ($combinations as $indices) {
        $sequence = array_fill(0, $n, $cb);
        foreach ($indices as $idx) {
            $sequence[$idx] = $ca;
        }
        $sequenceStr = implode('', $sequence) . 'A';
        $combos[] = $sequenceStr;
    }
    // Remove duplicates
    $combos = array_unique($combos);
    return $combos;
}

/**
 * Generates all valid sequences to move from key 'a' to key 'b' on the given keypad.
 *
 * @param string $a Starting key.
 * @param string $b Target key.
 * @param array $keypad Keypad mapping (either numeric or directional).
 * @return array Array of valid sequences.
 */
function generate_ways($a, $b, $keypad) {
    global $dd; // Movement directions
    $ca = '';
    $cb = '';
    $cur_loc = $keypad[$a];
    $next_loc = $keypad[$b];
    $di = $next_loc[0] - $cur_loc[0];
    $dj = $next_loc[1] - $cur_loc[1];
    $moves = [];

    if ($di > 0) {
        $moves[] = ['v', $di];
    } elseif ($di < 0) {
        $moves[] = ['^', -$di];
    }

    if ($dj > 0) {
        $moves[] = ['>', $dj];
    } elseif ($dj < 0) {
        $moves[] = ['<', -$dj];
    }

    // Flatten the moves
    $flat_moves = [];
    foreach ($moves as $move) {
        list($char, $count) = $move;
        for ($i = 0; $i < $count; $i++) {
            $flat_moves[] = $char;
        }
    }

    $ca = ''; // Not used directly in this implementation
    $cb = ''; // Not used directly in this implementation

    // To generate all unique sequences by interleaving the moves
    // Since 'ca' and 'cb' are not separate here, we'll use get_combos directly with all move characters
    // Instead, we need to generate all permutations of the move characters and append 'A'

    // However, the original Python code uses get_combos to interleave two different characters
    // Here, all moves are of potentially different characters, so get_combos is not directly applicable
    // Instead, we generate all unique permutations of the move sequence and append 'A'

    // To handle this, we can use a function to generate all unique permutations of the move characters
    $permutations = get_unique_permutations($flat_moves);
    $combos = [];
    foreach ($permutations as $perm) {
        $seq = implode('', $perm) . 'A';
        // Validate the sequence
        $ci = $cur_loc[0];
        $cj = $cur_loc[1];
        $valid = true;
        $chars = str_split($seq);
        // Iterate through the sequence excluding the last 'A'
        for ($i = 0; $i < count($chars) - 1; $i++) {
            $move_char = $chars[$i];
            if (!isset($dd[$move_char])) {
                $valid = false;
                break;
            }
            $di_move = $dd[$move_char][0];
            $dj_move = $dd[$move_char][1];
            $ci += $di_move;
            $cj += $dj_move;
            // Check if new position is valid
            $found = false;
            foreach ($keypad as $key => $pos) {
                if ($pos[0] === $ci && $pos[1] === $cj) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $valid = false;
                break;
            }
        }
        if ($valid) {
            $combos[] = $seq;
        }
    }

    // Remove duplicates
    $combos = array_unique($combos);
    return $combos;
}

/**
 * Generates all unique permutations of an array.
 *
 * @param array $items Array of items to permute.
 * @return array Array of unique permutations.
 */
function get_unique_permutations($items) {
    $results = [];
    $perms = [];
    $used = [];
    permute_helper($items, $perms, $used, $results);
    return $results;
}

/**
 * Helper function for generating unique permutations.
 *
 * @param array $items Array of items to permute.
 * @param array $perms Current permutation being built.
 * @param array $used Flags indicating whether an item is used.
 * @param array &$results Reference to the results array.
 */
function permute_helper($items, &$perms, &$used, &$results) {
    if (count($perms) === count($items)) {
        $results[] = $perms;
        return;
    }
    $seen = [];
    for ($i = 0; $i < count($items); $i++) {
        if (isset($used[$i]) || in_array($items[$i], $seen)) {
            continue;
        }
        $seen[] = $items[$i];
        $used[$i] = true;
        $perms[] = $items[$i];
        permute_helper($items, $perms, $used, $results);
        array_pop($perms);
        unset($used[$i]);
    }
}

/**
 * Generates all valid sequences by interleaving 'ca' and 'cb' moves.
 *
 * @param string $ca Character A.
 * @param int $a Number of times to press 'ca'.
 * @param string $cb Character B.
 * @param int $b Number of times to press 'cb'.
 * @return array Array of valid sequences.
 */
function get_combos_python_style($ca, $a, $cb, $b) {
    $combinations = combinations($a + $b, $a);
    $combos = [];
    foreach ($combinations as $indices) {
        $sequence = array_fill(0, $a + $b, $cb);
        foreach ($indices as $idx) {
            $sequence[$idx] = $ca;
        }
        $sequenceStr = implode('', $sequence) . 'A';
        $combos[] = $sequenceStr;
    }
    // Remove duplicates
    $combos = array_unique($combos);
    return $combos;
}

/**
 * Memoization for generate_ways function.
 */
$generate_ways_cache = [];

/**
 * Generates all valid sequences to move from key 'a' to key 'b' on the given keypad.
 *
 * @param string $a Starting key.
 * @param string $b Target key.
 * @param array $keypad Keypad mapping (either numeric or directional).
 * @return array Array of valid sequences.
 */
function generate_ways_memo($a, $b, $keypad) {
    global $generate_ways_cache;
    $cache_key = "$a|$b|" . json_encode($keypad);
    if (isset($generate_ways_cache[$cache_key])) {
        return $generate_ways_cache[$cache_key];
    }
    $ways = generate_ways($a, $b, $keypad);
    $generate_ways_cache[$cache_key] = $ways;
    return $ways;
}

/**
 * Memoization for get_cost function.
 */
$get_cost_cache = [];

/**
 * Recursively calculates the minimum cost to move from key 'a' to key 'b' on the given keypad.
 *
 * @param string $a Starting key.
 * @param string $b Target key.
 * @param array $keypad_keypad Indicator for keypad type (false for numeric, true for directional).
 * @param int $depth Recursion depth.
 * @return int Minimum cost.
 */
function get_cost($a, $b, $keypad, $depth = 0) {
    global $get_cost_cache, $direction_keypad, $numeric_keypad;

    // Determine which keypad to use
    $current_keypad = $keypad ? $direction_keypad : $numeric_keypad;

    $cache_key = "$a|$b|$keypad|$depth";
    if (isset($get_cost_cache[$cache_key])) {
        return $get_cost_cache[$cache_key];
    }

    if ($depth == 0) {
        // Base case: find the minimum length among all generated ways
        $ways = generate_ways_memo($a, $b, $current_keypad);
        if (empty($ways)) {
            $get_cost_cache[$cache_key] = PHP_INT_MAX;
            return PHP_INT_MAX;
        }
        $min_length = PHP_INT_MAX;
        foreach ($ways as $way) {
            $len = strlen($way);
            if ($len < $min_length) {
                $min_length = $len;
            }
        }
        $get_cost_cache[$cache_key] = $min_length;
        return $min_length;
    }

    // Recursive case
    $ways = generate_ways_memo($a, $b, $current_keypad);
    if (empty($ways)) {
        $get_cost_cache[$cache_key] = PHP_INT_MAX;
        return PHP_INT_MAX;
    }

    $best_cost = PHP_INT_MAX;
    foreach ($ways as $seq) {
        $new_seq = 'A' . $seq;
        $cost = 0;
        $chars = str_split($new_seq);
        for ($i = 0; $i < count($chars) - 1; $i++) {
            $char_a = $chars[$i];
            $char_b = $chars[$i + 1];
            $recursive_cost = get_cost($char_a, $char_b, true, $depth - 1);
            if ($recursive_cost === PHP_INT_MAX) {
                $cost = PHP_INT_MAX;
                break;
            }
            $cost += $recursive_cost;
        }
        if ($cost < $best_cost) {
            $best_cost = $cost;
        }
    }

    $get_cost_cache[$cache_key] = $best_cost;
    return $best_cost;
}

/**
 * Calculates the cost of pressing a code with a given recursion depth.
 *
 * @param string $code The code to press (e.g., "140A").
 * @param int $depth Recursion depth.
 * @return int Cost of the code.
 */
function get_code_cost($code, $depth) {
    $code = 'A' . $code;
    $cost = 0;
    $chars = str_split($code);
    for ($i = 0; $i < count($chars) - 1; $i++) {
        $a = $chars[$i];
        $b = $chars[$i + 1];
        $step_cost = get_cost($a, $b, false, $depth);
        if ($step_cost === PHP_INT_MAX) {
            // Invalid sequence
            return PHP_INT_MAX;
        }
        $cost += $step_cost;
    }
    return $cost;
}

/**
 * Main execution function.
 */
function main() {
    global $numeric_keypad, $direction_keypad;

    // Define the numeric keypad layout
    $numeric_keypad = [
        "7" => [0, 0],
        "8" => [0, 1],
        "9" => [0, 2],
        "4" => [1, 0],
        "5" => [1, 1],
        "6" => [1, 2],
        "1" => [2, 0],
        "2" => [2, 1],
        "3" => [2, 2],
        "0" => [3, 1],
        "A" => [3, 2]
    ];

    // Define the directional keypad layout
    $direction_keypad = [
        "^" => [0, 1],
        "A" => [0, 2],
        "<" => [1, 0],
        "v" => [1, 1],
        ">" => [1, 2]
    ];

    // Define movement directions
    global $dd;
    $dd = [
        ">" => [0, 1],
        "v" => [1, 0],
        "<" => [0, -1],
        "^" => [-1, 0]
    ];

    // Read input codes
    $inputPath = __DIR__ . '/day_21.in';
    $lines = readInput($inputPath);

    $ans = 0;
    foreach ($lines as $line) {
        $code = $line;
        // Extract numeric part by removing leading zeros and the trailing 'A'
        if (preg_match('/^0*(\d+)A$/', $code, $matches)) {
            $numeric_part = intval($matches[1]);
        } else {
            echo "Invalid code format: $code\n";
            continue;
        }

        // Calculate the shortest sequence length with depth=25
        $sequence_length = get_code_cost($code, 25);

        if ($sequence_length === PHP_INT_MAX) {
            echo "No valid sequence found for code: $code\n";
            continue;
        }

        // Calculate complexity
        $complexity = $sequence_length * $numeric_part;
        echo "Code: $code, Sequence Length: $sequence_length, Numeric Part: $numeric_part, Complexity: $complexity\n";

        // Add to total complexity
        $ans += $complexity;
    }

    echo "Total Complexity: $ans\n";
}

// Execute the main function
main();
?>
