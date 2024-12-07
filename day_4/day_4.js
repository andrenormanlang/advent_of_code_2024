const fs = require('fs');
const path = require('path');

// Part One: Definitions
const DIRECTIONS = [
  { name: 'Right', dx: 0, dy: 1 },
  { name: 'Left', dx: 0, dy: -1 },
  { name: 'Down', dx: 1, dy: 0 },
  { name: 'Up', dx: -1, dy: 0 },
  { name: 'Diagonal Down-Right', dx: 1, dy: 1 },
  { name: 'Diagonal Down-Left', dx: 1, dy: -1 },
  { name: 'Diagonal Up-Right', dx: -1, dy: 1 },
  { name: 'Diagonal Up-Left', dx: -1, dy: -1 },
];

const TARGET = 'XMAS';
const TARGET_LENGTH = TARGET.length;

function isValidPosition(x, y, rows, cols) {
  return x >= 0 && x < rows && y >= 0 && y < cols;
}

function searchFromPosition(x, y, direction, grid) {
  const rows = grid.length;
  const cols = grid[0].length;
  let match = '';

  for (let i = 0; i < TARGET_LENGTH; i++) {
    const newX = x + direction.dx * i;
    const newY = y + direction.dy * i;

    if (!isValidPosition(newX, newY, rows, cols)) {
      return false;
    }

    match += grid[newX][newY];
  }

  return match === TARGET;
}

function countXMAS(grid) {
  const MAS = 'MAS';
  const SAM = 'SAM';
  const rows = grid.length;
  const cols = grid[0].length;
  let count = 0;

  // Iterate through each cell, excluding the borders where X-MAS cannot fit
  for (let x = 1; x < rows - 1; x++) {
    for (let y = 1; y < cols - 1; y++) {
      // Extract the two diagonals centered at (x, y)
      const diag1 = grid[x - 1][y - 1] + grid[x][y] + grid[x + 1][y + 1];
      const diag2 = grid[x - 1][y + 1] + grid[x][y] + grid[x + 1][y - 1];

      // Check if both diagonals are either "MAS" or "SAM"
      const isDiag1Valid = (diag1 === MAS || diag1 === SAM);
      const isDiag2Valid = (diag2 === MAS || diag2 === SAM);

      if (isDiag1Valid && isDiag2Valid) {
        count++;
        // Uncomment the line below to log each occurrence
        // console.log(`Found X-MAS at center (${x}, ${y})`);
      }
    }
  }

  return count;
}

function countXMASOccurrences(grid) {
  let count = 0;

  for (let x = 0; x < grid.length; x++) {
    for (let y = 0; y < grid[0].length; y++) {
      // Check if the current cell matches the first character of the target
      if (grid[x][y] === TARGET[0]) {
        // Search in all directions
        for (const direction of DIRECTIONS) {
          if (searchFromPosition(x, y, direction, grid)) {
            count++;
            // Optionally, log the occurrence
            // console.log(`Found "XMAS" at (${x}, ${y}) going ${direction.name}`);
          }
        }
      }
    }
  }

  return count;
}

// Read the input file
const inputPath = path.join(__dirname, 'day_4.in');
let inputData;

try {
  inputData = fs.readFileSync(inputPath, 'utf-8');
} catch (err) {
  console.error(`Error reading the input file: ${err.message}`);
  process.exit(1);
}

// Parse the input into a 2D array (grid)
const grid = inputData
  .trim()
  .split('\n')
  .map(line => line.trim().toUpperCase().split(''));

// Part One: Count "XMAS" occurrences
const totalXMAS = countXMASOccurrences(grid);
console.log(`Total occurrences of "XMAS": ${totalXMAS}`);

// Part Two: Count "X-MAS" occurrences
const totalXMASPattern = countXMAS(grid);
console.log(`Total occurrences of "X-MAS": ${totalXMASPattern}`);
