use std::collections::{VecDeque, HashSet};
use std::fs::File;
use std::io::{self, BufRead};
use std::path::Path;

/// Represents the grid where '.' is safe and '#' is corrupted
type Grid = Vec<Vec<char>>;

/// Reads byte positions from a file.
/// Each line should be in the format: x,y
fn read_byte_positions<P: AsRef<Path>>(filename: P) -> io::Result<Vec<(usize, usize)>> {
    let file = File::open(filename)?;
    let reader = io::BufReader::new(file);
    let mut byte_positions = Vec::new();

    for (line_num, line) in reader.lines().enumerate() {
        let line = line?;
        if line.trim().is_empty() {
            continue; // Skip empty lines
        }
        let parts: Vec<&str> = line.trim().split(',').collect();
        if parts.len() != 2 {
            eprintln!("Invalid format at line {}: {}", line_num + 1, line);
            continue;
        }
        match (parts[0].parse::<usize>(), parts[1].parse::<usize>()) {
            (Ok(x), Ok(y)) => byte_positions.push((x, y)),
            _ => eprintln!("Invalid numbers at line {}: {}", line_num + 1, line),
        }
    }

    Ok(byte_positions)
}

/// Performs BFS to check if a path exists from start to end.
/// Returns true if no path exists (blocked), false otherwise.
fn is_path_blocked(grid: &Grid, start: (usize, usize), end: (usize, usize)) -> bool {
    let rows = grid.len();
    let cols = if rows > 0 { grid[0].len() } else { 0 };
    if rows == 0 || cols == 0 {
        return true;
    }

    let mut queue = VecDeque::new();
    let mut visited = HashSet::new();

    queue.push_back(start);
    visited.insert(start);

    // Define possible movements: Up, Down, Left, Right
    let directions = [(-1, 0isize), (1, 0isize), (0, -1isize), (0, 1isize)];

    while let Some((x, y)) = queue.pop_front() {
        if (x, y) == end {
            return false; // Path exists
        }

        for &(dx, dy) in &directions {
            let new_x = x as isize + dx;
            let new_y = y as isize + dy;

            // Check boundaries
            if new_x < 0 || new_x >= rows as isize || new_y < 0 || new_y >= cols as isize {
                continue;
            }

            let (nx, ny) = (new_x as usize, new_y as usize);

            if grid[nx][ny] == '.' && !visited.contains(&(nx, ny)) {
                visited.insert((nx, ny));
                queue.push_back((nx, ny));
            }
        }
    }

    true // No path exists
}

/// Finds the first byte that blocks the path.
/// Returns Some((x, y)) if a blocking byte is found, None otherwise.
fn find_blocking_byte(byte_positions: &[(usize, usize)], grid_size: (usize, usize)) -> Option<(usize, usize)> {
    let (rows, cols) = grid_size;
    let mut grid = vec![vec!['.'; cols]; rows];
    let start = (0, 0);
    let end = (rows - 1, cols - 1);

    // Ensure start and end are not corrupted
    grid[start.0][start.1] = '.';
    grid[end.0][end.1] = '.';

    for byte in byte_positions {
        let (x, y) = *byte;

        // Skip corrupting start or end positions
        if (x, y) == start || (x, y) == end {
            continue;
        }

        if x >= rows || y >= cols {
            eprintln!("Byte position out of bounds: {},{}", x, y);
            continue;
        }

        grid[x][y] = '#'; // Corrupt the cell

        // Check if the path is blocked after this corruption
        if is_path_blocked(&grid, start, end) {
            return Some((x, y)); // Found the blocking byte
        }
    }

    None // Path remains open after all bytes
}

fn main() -> io::Result<()> {
    let input_file = "day_18.in";
    let byte_positions = read_byte_positions(input_file)?;

    // Define grid size (71x71)
    let grid_size = (71, 71);

    match find_blocking_byte(&byte_positions, grid_size) {
        Some((x, y)) => println!("{},{}", x, y),
        None => println!("Path remains open after all bytes."),
    }

    Ok(())
}
