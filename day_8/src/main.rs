use std::collections::{HashMap, HashSet};

// Compute Greatest Common Divisor (GCD)
fn gcd(a: isize, b: isize) -> isize {
    if b == 0 {
        a.abs()
    } else {
        gcd(b, a % b)
    }
}

// Part One Logic:
// For each pair of antennas with the same frequency, generate the two antinodes at 2:1 ratio.
fn compute_part_one_antinodes(rows: usize, cols: usize, antennas_by_freq: &HashMap<char, Vec<(usize, usize)>>) -> usize {
    let mut antinodes = HashSet::new();
    for (_, positions) in antennas_by_freq.iter() {
        for i in 0..positions.len() {
            for j in i + 1..positions.len() {
                let (r1, c1) = positions[i];
                let (r2, c2) = positions[j];

                // Compute P1 and P2
                let p1 = (2 * r1 as isize - r2 as isize, 2 * c1 as isize - c2 as isize);
                let p2 = (2 * r2 as isize - r1 as isize, 2 * c2 as isize - c1 as isize);

                // Check bounds and insert
                if p1.0 >= 0 && p1.0 < rows as isize && p1.1 >= 0 && p1.1 < cols as isize {
                    antinodes.insert((p1.0 as usize, p1.1 as usize));
                }
                if p2.0 >= 0 && p2.0 < rows as isize && p2.1 >= 0 && p2.1 < cols as isize {
                    antinodes.insert((p2.0 as usize, p2.1 as usize));
                }
            }
        }
    }
    antinodes.len()
}

// Part Two Logic:
// For each pair of antennas with the same frequency, find all integer lattice points on the line 
// through them (both directions) and mark them as antinodes.
fn compute_part_two_antinodes(rows: usize, cols: usize, antennas_by_freq: &HashMap<char, Vec<(usize, usize)>>) -> usize {
    let mut antinodes = HashSet::new();

    for (_, positions) in antennas_by_freq.iter() {
        // Skip frequencies with only one antenna (they don't create lines by themselves)
        if positions.len() < 2 {
            continue;
        }

        for i in 0..positions.len() {
            for j in i + 1..positions.len() {
                let (r1, c1) = positions[i];
                let (r2, c2) = positions[j];

                let dr = r2 as isize - r1 as isize;
                let dc = c2 as isize - c1 as isize;
                let g = gcd(dr, dc);
                let step_r = dr / g;
                let step_c = dc / g;

                // Extend forward
                let (mut curr_r, mut curr_c) = (r1 as isize, c1 as isize);
                while curr_r >= 0 && curr_r < rows as isize && curr_c >= 0 && curr_c < cols as isize {
                    antinodes.insert((curr_r as usize, curr_c as usize));
                    curr_r += step_r;
                    curr_c += step_c;
                }

                // Extend backward
                let (mut curr_r, mut curr_c) = (r1 as isize, c1 as isize);
                while curr_r >= 0 && curr_r < rows as isize && curr_c >= 0 && curr_c < cols as isize {
                    antinodes.insert((curr_r as usize, curr_c as usize));
                    curr_r -= step_r;
                    curr_c -= step_c;
                }
            }
        }
    }

    antinodes.len()
}

fn main() {
    // Include the input file at compile time
    let input = include_str!("day_8.in");

    // Convert lines of the included string into a Vec<String>
    let grid: Vec<String> = input.lines().map(|line| line.to_string()).collect();

    let rows = grid.len();
    let cols = if rows > 0 { grid[0].len() } else { 0 };

    // Collect antennas by frequency
    let mut antennas_by_freq: HashMap<char, Vec<(usize, usize)>> = HashMap::new();

    for (r, line) in grid.iter().enumerate() {
        for (c, ch) in line.chars().enumerate() {
            if ch != '.' {
                antennas_by_freq.entry(ch).or_default().push((r, c));
            }
        }
    }

    // Compute results for Part One and Part Two
    let part_one_result = compute_part_one_antinodes(rows, cols, &antennas_by_freq);
    let part_two_result = compute_part_two_antinodes(rows, cols, &antennas_by_freq);

    println!("day 8 part one: {}", part_one_result);
    println!("day 8 part two: {}", part_two_result);
}
