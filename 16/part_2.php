<?php

// Read the maze input from the file day_16.in
$filename = 'day_16.in';
if (!file_exists($filename)) {
    die("Input file $filename not found.\n");
}

$maze = file($filename, FILE_IGNORE_NEW_LINES);
if ($maze === false) {
    die("Could not read input file $filename.\n");
}
$maze = array_filter($maze, fn($line) => trim($line) !== '');
$maze = array_values($maze);

$rows = count($maze);
$cols = strlen($maze[0]);

// Find Start (S) and End (E) positions
$startX = $startY = $endX = $endY = null;
for ($y = 0; $y < $rows; $y++) {
    for ($x = 0; $x < $cols; $x++) {
        $c = $maze[$y][$x];
        if ($c === 'S') {
            $startX = $x;
            $startY = $y;
        }
        if ($c === 'E') {
            $endX = $x;
            $endY = $y;
        }
    }
}

if ($startX === null || $endX === null) {
    die("No start (S) or end (E) found in the maze.\n");
}

// Directions: 0 = North, 1 = East, 2 = South, 3 = West
$directions = [
    [0, -1], // North
    [1, 0],  // East
    [0, 1],  // South
    [-1,0],  // West
];

$INF = PHP_INT_MAX;
$dist = [];
for ($y = 0; $y < $rows; $y++) {
    $dist[$y] = [];
    for ($x = 0; $x < $cols; $x++) {
        $dist[$y][$x] = [$INF, $INF, $INF, $INF];
    }
}

// Start facing East (direction = 1)
$dist[$startY][$startX][1] = 0;

// Priority queue for Dijkstra: stores [x, y, dir, cost]
// We'll store negative cost as priority for min-heap behavior
$pq = new SplPriorityQueue();
$pq->setExtractFlags(SplPriorityQueue::EXTR_DATA);
$pq->insert([$startX, $startY, 1, 0], 0);

while (!$pq->isEmpty()) {
    list($x, $y, $dir, $currentCost) = $pq->extract();

    // If this cost is not the current best known, skip
    if ($currentCost > $dist[$y][$x][$dir]) {
        continue;
    }

    // If we reached the end, we don't stop here because we need the minimal cost for all directions
    // just continue Dijkstra until all minimal distances are found.

    // Move forward
    $dx = $directions[$dir][0];
    $dy = $directions[$dir][1];
    $nx = $x + $dx;
    $ny = $y + $dy;
    if ($nx >= 0 && $nx < $cols && $ny >= 0 && $ny < $rows && $maze[$ny][$nx] !== '#') {
        $newCost = $currentCost + 1;
        if ($newCost < $dist[$ny][$nx][$dir]) {
            $dist[$ny][$nx][$dir] = $newCost;
            $pq->insert([$nx, $ny, $dir, $newCost], -$newCost);
        }
    }

    // Turn left (cost 1000)
    $leftDir = ($dir + 3) % 4;
    $leftCost = $currentCost + 1000;
    if ($leftCost < $dist[$y][$x][$leftDir]) {
        $dist[$y][$x][$leftDir] = $leftCost;
        $pq->insert([$x, $y, $leftDir, $leftCost], -$leftCost);
    }

    // Turn right (cost 1000)
    $rightDir = ($dir + 1) % 4;
    $rightCost = $currentCost + 1000;
    if ($rightCost < $dist[$y][$x][$rightDir]) {
        $dist[$y][$x][$rightDir] = $rightCost;
        $pq->insert([$x, $y, $rightDir, $rightCost], -$rightCost);
    }
}

// Find the minimal cost to reach E in any direction
$minCost = $INF;
$minDir = null;
for ($d = 0; $d < 4; $d++) {
    if ($dist[$endY][$endX][$d] < $minCost) {
        $minCost = $dist[$endY][$endX][$d];
        $minDir = $d;
    }
}

// Part One result
echo "Minimal cost: $minCost\n";

// Part Two:
// We need to identify all tiles that are on at least one shortest path.
// We'll do a reverse search from all end states (those with dist == minCost).

$on_best_path = [];
for ($y = 0; $y < $rows; $y++) {
    $on_best_path[$y] = array_fill(0, $cols, false);
}

// We'll use a queue to do a reverse BFS/DFS of states on shortest paths
// Start from all directions at E that have cost = minCost
$queue = [];
for ($d = 0; $d < 4; $d++) {
    if ($dist[$endY][$endX][$d] == $minCost) {
        $queue[] = [$endX, $endY, $d];
        $on_best_path[$endY][$endX] = true;
    }
}

// To avoid reprocessing states multiple times, track visited states in backtracking
$visited_states = [];
for ($y = 0; $y < $rows; $y++) {
    $visited_states[$y] = [];
    for ($x = 0; $x < $cols; $x++) {
        $visited_states[$y][$x] = [false, false, false, false];
    }
}

foreach ($queue as $st) {
    list($qx, $qy, $qd) = $st;
    $visited_states[$qy][$qx][$qd] = true;
}

while (!empty($queue)) {
    list($cx, $cy, $cdir) = array_pop($queue);
    $ccost = $dist[$cy][$cx][$cdir];

    // Check possible predecessors

    // 1. Forward predecessor:
    // If we got here by moving forward 1 step from (px, py, cdir), then dist[py][px][cdir] = ccost - 1.
    $dx = $directions[$cdir][0];
    $dy = $directions[$cdir][1];
    $px = $cx - $dx;
    $py = $cy - $dy;

    if ($px >= 0 && $px < $cols && $py >= 0 && $py < $rows && $maze[$py][$px] !== '#') {
        if ($dist[$py][$px][$cdir] == $ccost - 1) {
            // This is a valid predecessor
            $on_best_path[$py][$px] = true;
            if (!$visited_states[$py][$px][$cdir]) {
                $visited_states[$py][$px][$cdir] = true;
                $queue[] = [$px, $py, $cdir];
            }
        }
    }

    // 2. Turn predecessors:
    // If we turned left or right to get (cx, cy, cdir) from (cx, cy, pdir)
    // left turn: pdir = (cdir + 1) % 4
    // right turn: pdir = (cdir + 3) % 4
    $pdir_left = ($cdir + 1) % 4;
    $pdir_right = ($cdir + 3) % 4;

    // Check left turn predecessor
    if ($dist[$cy][$cx][$pdir_left] == $ccost - 1000) {
        $on_best_path[$cy][$cx] = true; 
        if (!$visited_states[$cy][$cx][$pdir_left]) {
            $visited_states[$cy][$cx][$pdir_left] = true;
            $queue[] = [$cx, $cy, $pdir_left];
        }
    }

    // Check right turn predecessor
    if ($dist[$cy][$cx][$pdir_right] == $ccost - 1000) {
        $on_best_path[$cy][$cx] = true; 
        if (!$visited_states[$cy][$cx][$pdir_right]) {
            $visited_states[$cy][$cx][$pdir_right] = true;
            $queue[] = [$cx, $cy, $pdir_right];
        }
    }
}

// Count how many tiles are part of at least one best path
$count = 0;
for ($y = 0; $y < $rows; $y++) {
    for ($x = 0; $x < $cols; $x++) {
        // Count only non-wall tiles that are on the best path
        $c = $maze[$y][$x];
        if ($c !== '#' && $on_best_path[$y][$x]) {
            $count++;
        }
    }
}

// Part Two result
echo "Tiles on at least one best path: $count\n";
