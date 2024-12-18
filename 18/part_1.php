<?php
/**
 * Advent of Code 2024 - Day 18: RAM Run
 *
 * This script simulates bytes falling into a memory grid and finds the shortest path
 * from the top-left corner (0,0) to the bottom-right corner (70,70), avoiding
 * corrupted memory coordinates.
 */

/**
 * Function to read the first 1024 byte positions from the input file.
 *
 * @param string $filename The path to the input file.
 * @param int $limit The number of byte positions to read.
 * @return array An array of [X, Y] coordinates.
 */
function read_byte_positions($filename, $limit = 1024) {
    $byte_positions = [];

    // Open the file for reading
    $file = fopen($filename, 'r');
    if (!$file) {
        die("Unable to open file: $filename\n");
    }

    // Read lines until the limit is reached
    while (($line = fgets($file)) !== false && count($byte_positions) < $limit) {
        $line = trim($line);
        if (empty($line)) {
            continue; // Skip empty lines
        }

        // Split the line into X and Y
        list($x, $y) = explode(',', $line);

        // Convert to integers and add to the array
        $byte_positions[] = [(int)$x, (int)$y];
    }

    fclose($file);

    return $byte_positions;
}

/**
 * Function to initialize the memory grid and mark corrupted positions.
 *
 * @param int $size The size of the grid (size x size).
 * @param array $byte_positions The list of corrupted [X, Y] coordinates.
 * @return array A 2D array representing the grid.
 */
function initialize_grid($size, $byte_positions) {
    // Initialize a 2D grid filled with '.' (safe)
    $grid = array_fill(0, $size + 1, array_fill(0, $size + 1, '.'));

    // Mark corrupted positions with '#' (corrupted)
    foreach ($byte_positions as $pos) {
        list($x, $y) = $pos;

        // Ensure coordinates are within bounds
        if ($x >= 0 && $x <= $size && $y >= 0 && $y <= $size) {
            $grid[$y][$x] = '#';
        }
    }

    return $grid;
}

/**
 * Function to perform BFS and find the shortest path.
 *
 * @param array $grid The memory grid.
 * @param int $start_x Starting X coordinate.
 * @param int $start_y Starting Y coordinate.
 * @param int $end_x Exit X coordinate.
 * @param int $end_y Exit Y coordinate.
 * @return int|null The minimum number of steps, or null if unreachable.
 */
function bfs_shortest_path($grid, $start_x, $start_y, $end_x, $end_y) {
    $size = count($grid) - 1; // Assuming square grid

    // Check if start or end positions are corrupted
    if ($grid[$start_y][$start_x] === '#' || $grid[$end_y][$end_x] === '#') {
        return null; // Unreachable
    }

    // Directions: up, right, down, left
    $directions = [
        [-1, 0], // Up
        [0, 1],  // Right
        [1, 0],  // Down
        [0, -1], // Left
    ];

    // Initialize the queue with the starting position and step count
    $queue = [];
    array_push($queue, [$start_y, $start_x, 0]);

    // Initialize a visited grid
    $visited = array_fill(0, $size + 1, array_fill(0, $size + 1, false));
    $visited[$start_y][$start_x] = true;

    while (!empty($queue)) {
        // Dequeue the first element
        list($current_y, $current_x, $steps) = array_shift($queue);

        // Check if we've reached the exit
        if ($current_x === $end_x && $current_y === $end_y) {
            return $steps;
        }

        // Explore all possible directions
        foreach ($directions as $dir) {
            $new_y = $current_y + $dir[0];
            $new_x = $current_x + $dir[1];

            // Check boundaries
            if ($new_x < 0 || $new_x > $size || $new_y < 0 || $new_y > $size) {
                continue; // Out of bounds
            }

            // Check if the position is safe and not visited
            if ($grid[$new_y][$new_x] === '.' && !$visited[$new_y][$new_x]) {
                $visited[$new_y][$new_x] = true;
                array_push($queue, [$new_y, $new_x, $steps + 1]);
            }
        }
    }

    // If the exit is unreachable
    return null;
}

/**
 * Main execution function.
 */
function main() {
    $input_file = 'day_18.in'; // Input file path

    // Read the first 1024 byte positions
    $byte_positions = read_byte_positions($input_file, 1024);

    // Initialize the 71x71 grid (0 to 70 inclusive)
    $grid_size = 70;
    $grid = initialize_grid($grid_size, $byte_positions);

    // Define start and end positions
    $start_x = 0;
    $start_y = 0;
    $end_x = $grid_size;
    $end_y = $grid_size;

    // Find the shortest path using BFS
    $min_steps = bfs_shortest_path($grid, $start_x, $start_y, $end_x, $end_y);

    if ($min_steps !== null) {
        echo "Minimum number of steps to reach the exit: $min_steps\n";
    } else {
        echo "The exit is unreachable.\n";
    }
}

// Execute the main function
main();
?>
