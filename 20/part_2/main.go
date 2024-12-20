package main

import (
	"bufio"
	"fmt"
	"os"
	"strconv"
)

// Position represents a coordinate on the grid
type Position struct {
	x int
	y int
}

// Direction vectors: Up, Down, Left, Right
var directions = []Position{
	{0, -1}, // Up
	{0, 1},  // Down
	{-1, 0}, // Left
	{1, 0},  // Right
}

// BFS computes the shortest distance from the start position to all reachable positions
func BFS(grid [][]rune, start Position) [][]int {
	rows := len(grid)
	cols := len(grid[0])
	distances := make([][]int, rows)
	for i := range distances {
		distances[i] = make([]int, cols)
		for j := range distances[i] {
			distances[i][j] = -1 // -1 denotes unreachable
		}
	}

	queue := []Position{start}
	distances[start.y][start.x] = 0

	for len(queue) > 0 {
		current := queue[0]
		queue = queue[1:]
		currentDist := distances[current.y][current.x]

		for _, dir := range directions {
			newX := current.x + dir.x
			newY := current.y + dir.y

			// Check bounds
			if newX < 0 || newX >= cols || newY < 0 || newY >= rows {
				continue
			}

			// Check if the cell is a wall
			if grid[newY][newX] == '#' {
				continue
			}

			// Check if already visited
			if distances[newY][newX] != -1 {
				continue
			}

			// Update distance and enqueue
			distances[newY][newX] = currentDist + 1
			queue = append(queue, Position{newX, newY})
		}
	}

	return distances
}

// BFSWithCheat computes all possible end positions p2 from p1 within cheatDuration steps, allowing to pass through walls
func BFSWithCheat(grid [][]rune, p1 Position, cheatDuration int) map[Position]int {
	rows := len(grid)
	cols := len(grid[0])
	visited := make(map[Position]int)
	queue := []struct {
		pos   Position
		steps int
	}{
		{p1, 0},
	}

	visited[p1] = 0

	for len(queue) > 0 {
		current := queue[0]
		queue = queue[1:]

		if current.steps >= cheatDuration {
			continue
		}

		for _, dir := range directions {
			newX := current.pos.x + dir.x
			newY := current.pos.y + dir.y

			// Check bounds
			if newX < 0 || newX >= cols || newY < 0 || newY >= rows {
				continue
			}

			newPos := Position{newX, newY}
			newSteps := current.steps + 1

			// Allow passing through walls
			// But the cheat must end on a normal track
			if newSteps <= cheatDuration {
				// Update if not visited or found a shorter path
				if existingSteps, exists := visited[newPos]; !exists || newSteps < existingSteps {
					visited[newPos] = newSteps
					queue = append(queue, struct {
						pos   Position
						steps int
					}{newPos, newSteps})
				}
			}
		}
	}

	return visited
}

func main() {
	// Open the input file
	file, err := os.Open("day_20.in")
	if err != nil {
		fmt.Println("Failed to read input file:", err)
		return
	}
	defer file.Close()

	// Read the grid
	scanner := bufio.NewScanner(file)
	var grid [][]rune
	var start, end Position

	for y := 0; scanner.Scan(); y++ {
		line := scanner.Text()
		row := []rune(line)
		grid = append(grid, row)
		for x, cell := range row {
			if cell == 'S' {
				start = Position{x, y}
				grid[y][x] = '.' // Treat 'S' as track
			} else if cell == 'E' {
				end = Position{x, y}
				grid[y][x] = '.' // Treat 'E' as track
			}
		}
	}

	if err := scanner.Err(); err != nil {
		fmt.Println("Error reading input:", err)
		return
	}

	// Compute distances from start and end
	distanceStart := BFS(grid, start)
	distanceEnd := BFS(grid, end)

	// Shortest path length without cheat
	shortestPathLength := distanceStart[end.y][end.x]
	if shortestPathLength == -1 {
		fmt.Println("No path found from Start to End without cheating.")
		return
	}

	fmt.Printf("Shortest path length without cheating: %d picoseconds.\n", shortestPathLength)

	// Collect all positions on the shortest path
	type Cheat struct {
		p1 Position
		p2 Position
	}

	cheats := make(map[string]struct{}) // To ensure uniqueness

	for y := 0; y < len(grid); y++ {
		for x := 0; x < len(grid[0]); x++ {
			if distanceStart[y][x] == -1 || distanceEnd[y][x] == -1 {
				continue
			}
			if distanceStart[y][x]+distanceEnd[y][x] == shortestPathLength {
				p1 := Position{x, y}
				// Find all p2 reachable from p1 within 20 steps, allowing to pass through walls
				visitedP2 := BFSWithCheat(grid, p1, 20)
				for p2, stepsCheat := range visitedP2 {
					// Cheat must end on normal track
					if grid[p2.y][p2.x] != '.' {
						continue
					}
					// Calculate time saved
					timeSaved := shortestPathLength - (distanceStart[y][x] + stepsCheat + distanceEnd[p2.y][p2.x])
					if timeSaved >= 100 {
						// Create a unique key for the cheat
						key := strconv.Itoa(p1.x) + "," + strconv.Itoa(p1.y) + "-" + strconv.Itoa(p2.x) + "," + strconv.Itoa(p2.y)
						cheats[key] = struct{}{}
					}
				}
			}
		}
	}

	// Output the number of valid cheats
	fmt.Printf("Number of cheats that save at least 100 picoseconds (Part 2): %d\n", len(cheats))
}
