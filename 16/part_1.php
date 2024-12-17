<?php

// Read the maze input from the file day_16.in
$filename = 'day_16.in';
if (!file_exists($filename)) {
    die("Input file $filename not found.\n");
}

$maze = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($maze === false) {
    die("Could not read input file $filename.\n");
}

$rows = count($maze);
$cols = strlen($maze[0]);

// Find Start (S) and End (E) positions
$startX = $startY = $endX = $endY = null;
for ($y = 0; $y < $rows; $y++) {
    for ($x = 0; $x < $cols; $x++) {
        if ($maze[$y][$x] === 'S') {
            $startX = $x;
            $startY = $y;
        }
        if ($maze[$y][$x] === 'E') {
            $endX = $x;
            $endY = $y;
        }
    }
}

if ($startX === null || $endX === null) {
    die("No start or end found in the maze.\n");
}

// Directions: 0 = North, 1 = East, 2 = South, 3 = West
// We start facing East, so initial direction = 1
$directions = [
    [0, -1], // North (dx=0, dy=-1)
    [1, 0],  // East  (dx=1, dy=0)
    [0, 1],  // South (dx=0, dy=1)
    [-1,0],  // West  (dx=-1,dy=0)
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

// Priority queue for Dijkstra: stores [x, y, direction, cost]
$pq = new SplPriorityQueue();
$pq->setExtractFlags(SplPriorityQueue::EXTR_DATA);
$pq->insert([$startX, $startY, 1, 0], 0);

while (!$pq->isEmpty()) {
    list($x, $y, $dir, $currentCost) = $pq->extract();

    // If this cost is not the current best known, skip
    if ($currentCost > $dist[$y][$x][$dir]) {
        continue;
    }

    // Check if we've reached the end
    if ($x === $endX && $y === $endY) {
        echo $currentCost . "\n";
        exit(0);
    }

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

// If no path found
echo "No path found\n";
