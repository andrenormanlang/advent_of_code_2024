package main

import (
    "bufio"
    "flag"
    "fmt"
    "log"
    "os"
)

// Position represents a coordinate on the grid
type Position struct {
    x, y int
}

// Direction represents movement direction as (dx, dy)
type Direction struct {
    dx, dy int
}

// State represents the guard's state (position and direction)
type State struct {
    pos Position
    dir Direction
}

// readGrid reads the grid from the input file and returns it as a 2D slice of runes,
// along with the guard's starting position and initial direction.
func readGrid(filename string) ([][]rune, Position, Direction, error) {
    // Open the input file
    file, err := os.Open(filename)
    if err != nil {
        return nil, Position{}, Direction{}, fmt.Errorf("failed to open input file: %v", err)
    }
    defer file.Close()

    // Read the grid into a 2D slice
    var grid [][]rune
    scanner := bufio.NewScanner(file)
    y := 0
    var start Position
    var currentDir Direction
    found := false
    for scanner.Scan() {
        line := scanner.Text()
        row := []rune(line)
        for x, char := range row {
            if char == '^' && !found {
                start = Position{x, y}
                currentDir = Direction{0, -1} // Initially facing up
                found = true
            }
        }
        grid = append(grid, row)
        y++
    }

    if err := scanner.Err(); err != nil {
        return nil, Position{}, Direction{}, fmt.Errorf("error reading input file: %v", err)
    }

    if !found {
        return nil, Position{}, Direction{}, fmt.Errorf("guard's starting position (^) not found in the grid")
    }

    return grid, start, currentDir, nil
}

// copyGrid creates a deep copy of the grid
func copyGrid(grid [][]rune) [][]rune {
    newGrid := make([][]rune, len(grid))
    for i := range grid {
        newRow := make([]rune, len(grid[i]))
        copy(newRow, grid[i])
        newGrid[i] = newRow
    }
    return newGrid
}

// turnRight returns the new direction after turning right 90 degrees
func turnRight(dir Direction) Direction {
    switch dir {
    case Direction{0, -1}: // Up
        return Direction{1, 0} // Right
    case Direction{1, 0}: // Right
        return Direction{0, 1} // Down
    case Direction{0, 1}: // Down
        return Direction{-1, 0} // Left
    case Direction{-1, 0}: // Left
        return Direction{0, -1} // Up
    default:
        return dir // Should not reach here
    }
}

// simulateMovement simulates the guard's movement on the grid.
// It returns a map of visited positions and a boolean indicating whether the guard left the grid.
func simulateMovement(grid [][]rune, start Position, currentDir Direction) (map[Position]bool, bool) {
    visited := make(map[Position]bool)
    pos := start
    visited[pos] = true

    for {
        // Calculate the position in front
        front := Position{pos.x + currentDir.dx, pos.y + currentDir.dy}

        // Check if front is within bounds
        if front.y < 0 || front.y >= len(grid) || front.x < 0 || front.x >= len(grid[0]) {
            // Guard leaves the map
            return visited, true
        }

        // Check if there's an obstacle
        if grid[front.y][front.x] == '#' {
            // Turn right
            currentDir = turnRight(currentDir)
        } else {
            // Move forward
            pos = front
            visited[pos] = true
        }
    }
}

// simulateMovementWithLoopDetection simulates the guard's movement on the grid.
// It returns true if the guard gets stuck in a loop, false otherwise.
func simulateMovementWithLoopDetection(grid [][]rune, start Position, currentDir Direction) bool {
    visitedStates := make(map[State]bool)
    pos := start
    dir := currentDir

    for {
        state := State{pos, dir}
        if visitedStates[state] {
            // Detected a loop
            return true
        }
        visitedStates[state] = true

        // Calculate the position in front
        front := Position{pos.x + dir.dx, pos.y + dir.dy}

        // Check if front is within bounds
        if front.y < 0 || front.y >= len(grid) || front.x < 0 || front.x >= len(grid[0]) {
            // Guard leaves the map
            return false
        }

        // Check if there's an obstacle
        if grid[front.y][front.x] == '#' {
            // Turn right
            dir = turnRight(dir)
        } else {
            // Move forward
            pos = front
        }
    }
}

// partOne implements the solution for Part One.
func partOne(grid [][]rune, start Position, currentDir Direction) {
    visited, left := simulateMovement(grid, start, currentDir)
    if left {
        fmt.Printf("Part One: The guard visited %d distinct positions before leaving the map.\n", len(visited))
    } else {
        fmt.Println("Part One: The guard did not leave the map.")
    }
}

// partTwo implements the solution for Part Two.
func partTwo(originalGrid [][]rune, start Position, currentDir Direction) {
    count := 0
    gridHeight := len(originalGrid)
    gridWidth := len(originalGrid[0])

    // Iterate through all positions in the grid
    for y := 0; y < gridHeight; y++ {
        for x := 0; x < gridWidth; x++ {
            pos := Position{x, y}

            // Skip if the position is not empty or it's the starting position
            if originalGrid[y][x] != '.' || (pos.x == start.x && pos.y == start.y) {
                continue
            }

            // Create a copy of the grid and place an obstruction at (x, y)
            gridCopy := copyGrid(originalGrid)
            gridCopy[y][x] = '#' // Add obstruction

            // Simulate movement with loop detection
            loop := simulateMovementWithLoopDetection(gridCopy, start, currentDir)
            if loop {
                count++
            }
        }
    }

    fmt.Printf("Part Two: There are %d possible positions to place a new obstruction to cause a loop.\n", count)
}

func main() {
    // Define command-line flags to choose which part to run
    part1Flag := flag.Bool("part1", false, "Run Part One")
    part2Flag := flag.Bool("part2", false, "Run Part Two")
    allFlag := flag.Bool("all", false, "Run both Part One and Part Two")
    inputFile := flag.String("input", "day_6.in", "Input file name")

    flag.Parse()

    // If no flags are provided, run both parts
    if !*part1Flag && !*part2Flag && !*allFlag {
        *allFlag = true
    }

    // Read the grid and get the starting position and direction
    grid, start, currentDir, err := readGrid(*inputFile)
    if err != nil {
        log.Fatalf("Error: %v", err)
    }

    if *part1Flag || *allFlag {
        partOne(grid, start, currentDir)
    }

    if *part2Flag || *allFlag {
        partTwo(grid, start, currentDir)
    }
}
