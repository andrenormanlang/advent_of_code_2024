use std::collections::HashSet;
use std::fs::File;
use std::io::{self, BufRead};
use std::path::Path;

/// Struct representing a robot with position (x, y) and velocity (vx, vy)
#[derive(Debug, Clone)]
struct Robot {
    x: i64,
    y: i64,
    vx: i64,
    vy: i64,
}

/// Helper function to perform Euclidean modulo operation
/// Equivalent to `rem_euclid` for compatibility with older Rust versions
fn rem_euclid(a: i64, b: i64) -> i64 {
    ((a % b) + b) % b
}

/// Function to parse robots from input lines
fn parse_robots(lines: &[String]) -> Vec<Robot> {
    let mut robots = Vec::new();

    for line in lines {
        // Expected format: p=x,y v=dx,dy
        // Example: p=0,4 v=3,-3
        let parts: Vec<&str> = line.split_whitespace().collect();
        if parts.len() != 2 {
            continue;
        }

        // Parse position
        let pos_part = parts[0].trim_start_matches("p=").trim_matches(',');
        let pos: Vec<i64> = pos_part
            .split(',')
            .filter_map(|s| s.parse::<i64>().ok())
            .collect();

        // Parse velocity
        let vel_part = parts[1].trim_start_matches("v=");
        let vel: Vec<i64> = vel_part
            .split(',')
            .filter_map(|s| s.parse::<i64>().ok())
            .collect();

        if pos.len() == 2 && vel.len() == 2 {
            robots.push(Robot {
                x: pos[0],
                y: pos[1],
                vx: vel[0],
                vy: vel[1],
            });
        }
    }

    robots
}

/// Function to compute the safety factor after `t` seconds (Part One)
fn compute_safety_factor(robots: &[Robot], width: i64, height: i64, t: i64) -> i64 {
    let mut q1 = 0;
    let mut q2 = 0;
    let mut q3 = 0;
    let mut q4 = 0;

    for robot in robots {
        // Calculate position at time t with wrapping
        let x = rem_euclid(robot.x + robot.vx * t, width);
        let y = rem_euclid(robot.y + robot.vy * t, height);

        // Determine quadrants based on x and y
        if x < width / 2 {
            if y < height / 2 {
                q1 += 1;
            }
            if y > height / 2 {
                q2 += 1;
            }
        }
        if x > width / 2 {
            if y < height / 2 {
                q3 += 1;
            }
            if y > height / 2 {
                q4 += 1;
            }
        }
    }

    q1 * q2 * q3 * q4
}

/// Function to find the fewest number of seconds for unique alignment (Part Two)
fn find_alignment_time(robots: &[Robot], width: i64, height: i64) -> Option<i64> {
    let max_time = 101 * 103; // Least Common Multiple (LCM) of width and height

    for t in 0..=max_time {
        let mut positions = HashSet::new();
        let mut all_unique = true;

        for robot in robots {
            let x = rem_euclid(robot.x + robot.vx * t, width);
            let y = rem_euclid(robot.y + robot.vy * t, height);

            // If the position is already in the set, not unique
            if !positions.insert((x, y)) {
                all_unique = false;
                break;
            }
        }

        if all_unique {
            return Some(t);
        }
    }

    None
}

fn main() -> io::Result<()> {
    // Define the input file path
    let input_path = "day_14.in";

    // Check if the input file exists
    if !Path::new(input_path).exists() {
        eprintln!("Error: Input file '{}' not found.", input_path);
        std::process::exit(1);
    }

    // Open the input file
    let file = File::open(input_path)?;
    let reader = io::BufReader::new(file);
    let lines: Vec<String> = reader.lines().filter_map(Result::ok).collect();

    // Parse robots from input
    let robots = parse_robots(&lines);

    // Define space dimensions
    let width: i64 = 101;
    let height: i64 = 103;

    // Part One: Compute safety factor after 100 seconds
    let safety_factor = compute_safety_factor(&robots, width, height, 100);
    println!("Safety Factor after 100 seconds: {}", safety_factor);

    // Part Two: Find the fewest number of seconds for unique alignment
    match find_alignment_time(&robots, width, height) {
        Some(t) => println!(
            "Fewest number of seconds for alignment (Part Two): {}",
            t
        ),
        None => println!("No alignment found within the time frame."),
    }

    Ok(())
}
