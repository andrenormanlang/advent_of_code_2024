use std::collections::{HashSet, VecDeque};
use std::fs;

// Define the structure for grid coordinates
#[derive(Debug, Clone, Copy, PartialEq, Eq, Hash)]
struct Position {
    x: usize,
    y: usize,
}

// Function to read and parse the grid
fn parse_grid(input: &str) -> (Vec<Vec<char>>, Position, Position) {
    let mut grid: Vec<Vec<char>> = Vec::new();
    let mut start = Position { x: 0, y: 0 };
    let mut end = Position { x: 0, y: 0 };

    for (y, line) in input.lines().enumerate() {
        let mut row: Vec<char> = Vec::new();
        for (x, c) in line.chars().enumerate() {
            if c == 'S' {
                start = Position { x, y };
                row.push('.'); // Treat 'S' as track
            } else if c == 'E' {
                end = Position { x, y };
                row.push('.'); // Treat 'E' as track
            } else {
                row.push(c);
            }
        }
        grid.push(row);
    }

    (grid, start, end)
}

// BFS to find distances from a given start position
fn bfs(grid: &Vec<Vec<char>>, start: Position) -> Vec<Vec<Option<usize>>> {
    let rows = grid.len();
    let cols = grid[0].len();
    let mut distances = vec![vec![None; cols]; rows];
    let mut queue: VecDeque<Position> = VecDeque::new();

    queue.push_back(start);
    distances[start.y][start.x] = Some(0);

    let directions = vec![
        (-1isize, 0isize), // Up
        (1, 0),            // Down
        (0, -1),           // Left
        (0, 1),            // Right
    ];

    while let Some(current) = queue.pop_front() {
        let current_distance = distances[current.y][current.x].unwrap();

        for (dy, dx) in &directions {
            let new_y = current.y as isize + dy;
            let new_x = current.x as isize + dx;

            if new_y >= 0 && new_y < rows as isize && new_x >= 0 && new_x < cols as isize {
                let new_y_usize = new_y as usize;
                let new_x_usize = new_x as usize;
                let new_pos = Position {
                    x: new_x_usize,
                    y: new_y_usize,
                };

                if grid[new_y_usize][new_x_usize] != '#' && distances[new_y_usize][new_x_usize].is_none() {
                    distances[new_y_usize][new_x_usize] = Some(current_distance + 1);
                    queue.push_back(new_pos);
                }
            }
        }
    }

    distances
}

fn main() {
    // Read the input from a file named "day_20.in"
    let input = fs::read_to_string("day_20.in").expect("Failed to read input file.");

    // Parse the grid to get the grid, start, and end positions
    let (grid, start, end) = parse_grid(&input);

    // Perform BFS from start
    let distance_start = bfs(&grid, start);

    // Perform BFS from end
    let distance_end = bfs(&grid, end);

    // Get the shortest path length without cheat
    let shortest_path_length = match distance_start[end.y][end.x] {
        Some(l) => l,
        None => {
            println!("No path found from Start to End without cheating.");
            return;
        }
    };

    println!(
        "Shortest path length without cheating: {} picoseconds.",
        shortest_path_length
    );

    let mut valid_cheats: HashSet<(Position, Position)> = HashSet::new();

    // Iterate through all positions on the grid
    for y in 0..grid.len() {
        for x in 0..grid[0].len() {
            // Check if (x, y) is on the shortest path
            if let Some(d_start_p1) = distance_start[y][x] {
                if let Some(d_end_p1) = distance_end[y][x] {
                    if d_start_p1 + d_end_p1 == shortest_path_length {
                        let p1 = Position { x, y };

                        // Find all p2 reachable from p1 in up to 2 steps, allowing to pass through walls
                        let mut p2_set: HashSet<Position> = HashSet::new();
                        let mut p2_queue: VecDeque<(Position, usize)> = VecDeque::new();
                        p2_queue.push_back((p1, 0));
                        p2_set.insert(p1);

                        let cheat_directions = vec![
                            (-1isize, 0isize), // Up
                            (1, 0),            // Down
                            (0, -1),           // Left
                            (0, 1),            // Right
                        ];

                        while let Some((current_p2, steps)) = p2_queue.pop_front() {
                            if steps >= 2 {
                                continue;
                            }

                            for (dy, dx) in &cheat_directions {
                                let new_y = current_p2.y as isize + dy;
                                let new_x = current_p2.x as isize + dx;

                                if new_y >= 0 && new_y < grid.len() as isize && new_x >=0 && new_x < grid[0].len() as isize {
                                    let new_y_usize = new_y as usize;
                                    let new_x_usize = new_x as usize;
                                    let new_p2 = Position {
                                        x: new_x_usize,
                                        y: new_y_usize,
                                    };

                                    // Allow passing through walls during cheat steps
                                    // But p2 must end on track
                                    if !p2_set.contains(&new_p2) {
                                        p2_set.insert(new_p2);
                                        p2_queue.push_back((new_p2, steps +1));
                                    }
                                }
                            }
                        }

                        // After BFS, filter p2_set to include only positions on track
                        let p2_set_filtered: HashSet<Position> = p2_set.into_iter()
                            .filter(|p2| grid[p2.y][p2.x] != '#')
                            .collect();

                        // Now, for each p2 in p2_set_filtered
                        for p2 in p2_set_filtered.iter() {
                            // Calculate time_saved
                            // time_saved = shortest_path_length - (distance_start[p1] + distance_end[p2] + 2)
                            // Ensure p2 is reachable from start and end
                            if let Some(d_end_p2) = distance_end[p2.y][p2.x] {
                                let time_saved = shortest_path_length as isize - ((d_start_p1 as isize) + (d_end_p2 as isize) + 2);
                                if time_saved >= 100 {
                                    valid_cheats.insert((p1, *p2));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    let valid_cheat_count = valid_cheats.len();

    println!(
        "Number of cheats that save at least 100 picoseconds (Part 1): {}",
        valid_cheat_count
    );
}
