use std::fs::File as StdFile;
use std::io::{self, BufRead};
use std::path::Path;

/// Represents a block on the disk.
/// `Some(id)` indicates a file block with the given file ID.
/// `None` indicates free space.
type Block = Option<usize>;

/// Represents a file with its ID, starting position, and length.
#[derive(Debug, Clone, PartialEq, Eq)]
struct DiskFile {
    id: usize,
    start: usize,
    length: usize,
}

fn main() -> io::Result<()> {
    // Read the disk map from the file "day_9.in"
    let disk_map = read_disk_map("day_9.in")?;

    // Parse the disk map into a vector of blocks
    let parsed_disk = parse_disk_map(&disk_map);

    // Clone the parsed disk for both compaction methods
    let mut disk_part_one = parsed_disk.clone();
    let mut disk_part_two = parsed_disk.clone();

    // Perform Part One compaction: Move individual blocks
    compact_part_one(&mut disk_part_one);

    // Calculate checksum for Part One
    let checksum_part_one = calculate_checksum(&disk_part_one);
    println!("Part One - Filesystem Checksum: {}", checksum_part_one);

    // Perform Part Two compaction: Move entire files
    compact_part_two(&mut disk_part_two);

    // Calculate checksum for Part Two
    let checksum_part_two = calculate_checksum(&disk_part_two);
    println!("Part Two - Filesystem Checksum: {}", checksum_part_two);

    Ok(())
}

/// Reads the disk map from the specified file.
/// Expects the file to contain a single line of digits.
fn read_disk_map<P>(filename: P) -> io::Result<String>
where
    P: AsRef<Path>,
{
    let file = StdFile::open(filename)?;
    let mut lines = io::BufReader::new(file).lines();

    if let Some(line) = lines.next() {
        Ok(line?.trim().to_string())
    } else {
        Err(io::Error::new(
            io::ErrorKind::InvalidData,
            "Input file is empty.",
        ))
    }
}

/// Parses the disk map string into a vector of blocks.
/// Alternates between file lengths and free space lengths, starting with a file.
fn parse_disk_map(disk_map: &str) -> Vec<Block> {
    let mut disk: Vec<Block> = Vec::new();
    let mut chars = disk_map.chars();

    // File IDs start at 0
    let mut file_id = 0;
    // Flag to indicate whether the current digit represents a file length or free space length
    let mut is_file = true;

    while let Some(c) = chars.next() {
        if let Some(length) = c.to_digit(10) {
            let length = length as usize;
            if is_file {
                // Add `length` number of blocks with the current file ID
                for _ in 0..length {
                    disk.push(Some(file_id));
                }
                file_id += 1;
            } else {
                // Add `length` number of free space blocks
                for _ in 0..length {
                    disk.push(None);
                }
            }
            // Toggle between file and free space
            is_file = !is_file;
        }
    }

    disk
}

/// Compacts the disk by moving individual file blocks to eliminate gaps.
/// This corresponds to Part One of the challenge.
fn compact_part_one(disk: &mut Vec<Block>) {
    loop {
        // Find the first free space
        if let Some(first_free) = disk.iter().position(|b| b.is_none()) {
            // Find the last file block after the first free space
            if let Some(from) = disk[first_free + 1..].iter().rposition(|b| b.is_some()) {
                let from = first_free + 1 + from;
                // Move the block from 'from' to 'first_free'
                disk[first_free] = disk[from];
                disk[from] = None;
            } else {
                // No more file blocks to move; compaction is complete
                break;
            }
        } else {
            // No free spaces left; compaction is complete
            break;
        }
    }
}

/// Compacts the disk by moving entire files to the leftmost possible free space spans.
/// This corresponds to Part Two of the challenge.
fn compact_part_two(disk: &mut Vec<Block>) {
    // Identify all files
    let mut files = identify_files(disk);

    // Sort files in decreasing order of file ID
    files.sort_by(|a, b| b.id.cmp(&a.id));

    for file in files {
        // Find the leftmost free space span that can fit the file, entirely before the file's current position
        if let Some(target_start) = find_leftmost_free_span(disk, file.length, file.start) {
            // Move the file to the target_start
            move_file(disk, file.id, file.start, target_start, file.length);
        }
        // If no suitable span is found, the file does not move
    }
}

/// Identifies all files on the disk, returning a vector of `DiskFile` structs.
fn identify_files(disk: &[Block]) -> Vec<DiskFile> {
    let mut files = Vec::new();
    let mut current_id = None;
    let mut start = 0;
    let mut length = 0;

    for (i, block) in disk.iter().enumerate() {
        match block {
            Some(id) => {
                if Some(*id) != current_id {
                    // If we were tracking a file, save it
                    if let Some(curr_id) = current_id {
                        files.push(DiskFile {
                            id: curr_id,
                            start,
                            length,
                        });
                    }
                    // Start tracking a new file
                    current_id = Some(*id);
                    start = i;
                    length = 1;
                } else {
                    // Continue tracking the current file
                    length += 1;
                }
            }
            None => {
                // If we were tracking a file, save it
                if let Some(curr_id) = current_id {
                    files.push(DiskFile {
                        id: curr_id,
                        start,
                        length,
                    });
                    current_id = None;
                }
            }
        }
    }

    // After the loop, check if a file was being tracked
    if let Some(curr_id) = current_id {
        files.push(DiskFile {
            id: curr_id,
            start,
            length,
        });
    }

    files
}

/// Finds the leftmost free space span that can fit a file of given length,
/// entirely before the specified position.
/// Returns the starting index of the span if found.
fn find_leftmost_free_span(disk: &[Block], length: usize, before_pos: usize) -> Option<usize> {
    let mut current_start = None;
    let mut current_length = 0;

    for (i, block) in disk.iter().enumerate() {
        if i >= before_pos {
            // We are only interested in spans before the file's current position
            break;
        }
        match block {
            None => {
                if current_start.is_none() {
                    current_start = Some(i);
                    current_length = 1;
                } else {
                    current_length += 1;
                }
                if current_length == length {
                    return current_start;
                }
            }
            Some(_) => {
                current_start = None;
                current_length = 0;
            }
        }
    }

    None
}

/// Moves a file from its current position to a new position.
/// Updates the disk blocks accordingly.
fn move_file(
    disk: &mut Vec<Block>,
    file_id: usize,
    current_start: usize,
    target_start: usize,
    length: usize,
) {
    // Move the file blocks to the target_start
    for i in 0..length {
        disk[target_start + i] = Some(file_id);
    }

    // Set the old blocks to free
    for i in 0..length {
        disk[current_start + i] = None;
    }
}

/// Calculates the filesystem checksum based on the compacted disk.
/// The checksum is the sum of (position * file_id) for all file blocks.
fn calculate_checksum(disk: &[Block]) -> usize {
    disk.iter()
        .enumerate()
        .filter_map(|(pos, block)| block.map(|id| pos * id))
        .sum()
}

#[cfg(test)]
mod tests {
    use super::*;

    #[test]
    fn test_parse_disk_map_example1() {
        let disk_map = "12345";
        let disk = parse_disk_map(disk_map);
        let expected = vec![
            Some(0), // 1 block file ID 0
            None,    // 2 blocks free
            None,
            Some(1), // 3 blocks file ID 1
            Some(1),
            Some(1),
            None,    // 4 blocks free
            None,
            None,
            None,
            Some(2), // 5 blocks file ID 2
            Some(2),
            Some(2),
            Some(2),
            Some(2),
        ];
        assert_eq!(disk, expected);
    }

    #[test]
    fn test_parse_disk_map_example2() {
        let disk_map = "2333133121414131402";
        let disk = parse_disk_map(disk_map);
        // Manually parsing:
        // 2 (file 0, length 2)
        // 3 (free, length 3)
        // 3 (file 1, length 3)
        // 3 (free, length 3)
        // 1 (file 2, length 1)
        // 3 (free, length 3)
        // 1 (file 3, length 1)
        // 2 (free, length 2)
        // 1 (file 4, length 1)
        // 4 (free, length 4)
        // 1 (file 5, length 1)
        // 3 (free, length 3)
        // 1 (file 6, length 1)
        // 4 (free, length 4)
        // 0 (file 7, length 0) -> Not possible, since digits are 0-9. Assuming '0' means 0 length.
        // 2 (file 8, length 2)
        // For simplicity, assuming '0' is treated as length 0 and skipped.

        // Therefore, '0' does not add any blocks.

        // Constructing expected disk:
        let mut expected = Vec::new();
        // File 0: 2 blocks
        expected.push(Some(0));
        expected.push(Some(0));
        // Free: 3 blocks
        expected.push(None);
        expected.push(None);
        expected.push(None);
        // File 1: 3 blocks
        expected.push(Some(1));
        expected.push(Some(1));
        expected.push(Some(1));
        // Free: 3 blocks
        expected.push(None);
        expected.push(None);
        expected.push(None);
        // File 2: 1 block
        expected.push(Some(2));
        // Free: 3 blocks
        expected.push(None);
        expected.push(None);
        expected.push(None);
        // File 3: 1 block
        expected.push(Some(3));
        // Free: 2 blocks
        expected.push(None);
        expected.push(None);
        // File 4: 1 block
        expected.push(Some(4));
        // Free: 4 blocks
        expected.push(None);
        expected.push(None);
        expected.push(None);
        expected.push(None);
        // File 5: 1 block
        expected.push(Some(5));
        // Free: 3 blocks
        expected.push(None);
        expected.push(None);
        expected.push(None);
        // File 6: 1 block
        expected.push(Some(6));
        // Free: 4 blocks
        expected.push(None);
        expected.push(None);
        expected.push(None);
        expected.push(None);
        // File 8: 2 blocks
        expected.push(Some(8));
        expected.push(Some(8));

        assert_eq!(disk, expected);
    }

    #[test]
    fn test_compact_part_one_example1() {
        let disk_map = "12345";
        let mut disk = parse_disk_map(disk_map);
        compact_part_one(&mut disk);
        let expected = vec![
            Some(0),
            Some(1),
            Some(1),
            Some(1),
            Some(2),
            Some(2),
            Some(2),
            Some(2),
            Some(2),
            None,
            None,
            None,
            None,
            None,
            None,
        ];
        assert_eq!(disk, expected);
    }

    #[test]
    fn test_compact_part_one_example2() {
        let disk_map = "2333133121414131402";
        let mut disk = parse_disk_map(disk_map);
        compact_part_one(&mut disk);
        // Expected checksum is 1928
        let checksum = calculate_checksum(&disk);
        assert_eq!(checksum, 1928);
    }

    #[test]
    fn test_compact_part_two_example1() {
        let disk_map = "12345";
        let mut disk = parse_disk_map(disk_map);
        compact_part_two(&mut disk);
        let expected = vec![
            Some(0),
            Some(1),
            Some(1),
            Some(1),
            Some(2),
            Some(2),
            Some(2),
            Some(2),
            Some(2),
            None,
            None,
            None,
            None,
            None,
            None,
        ];
        // Checksum should be same as Part One for this simple example
        let checksum = calculate_checksum(&disk);
        assert_eq!(checksum, 60);
    }

    #[test]
    fn test_compact_part_two_example2() {
        let disk_map = "2333133121414131402";
        let mut disk = parse_disk_map(disk_map);
        compact_part_two(&mut disk);
        // Expected checksum is 2858
        let checksum = calculate_checksum(&disk);
        assert_eq!(checksum, 2858);
    }

    #[test]
    fn test_calculate_checksum_empty() {
        let disk: Vec<Block> = vec![];
        let checksum = calculate_checksum(&disk);
        assert_eq!(checksum, 0);
    }

    #[test]
    fn test_calculate_checksum_only_free() {
        let disk: Vec<Block> = vec![None, None, None];
        let checksum = calculate_checksum(&disk);
        assert_eq!(checksum, 0);
    }

    #[test]
    fn test_calculate_checksum_single_file() {
        let disk: Vec<Block> = vec![Some(0), Some(0), Some(0)];
        let checksum = calculate_checksum(&disk);
        // Positions 0,1,2 with file ID 0: 0*0 + 1*0 + 2*0 = 0
        assert_eq!(checksum, 0);
    }
}
