package main

import (
	"fmt"
	"os"
	"strconv"
	"strings"
)

// Rule represents an ordering rule where X must come before Y
type Rule struct {
	X int
	Y int
}

// TopologicalSort performs a topological sort on the given nodes with the provided rules.
// It returns a sorted slice of nodes if successful, or an error if a cycle is detected.
func TopologicalSort(nodes []int, rules []Rule) ([]int, error) {
	// Build adjacency list and in-degree count
	adjacency := make(map[int][]int)
	inDegree := make(map[int]int)

	// Initialize inDegree for all nodes
	for _, node := range nodes {
		inDegree[node] = 0
	}

	// Add edges based on rules
	for _, rule := range rules {
		// Only consider rules where both X and Y are in the nodes
		xPresent, yPresent := false, false
		for _, n := range nodes {
			if n == rule.X {
				xPresent = true
			}
			if n == rule.Y {
				yPresent = true
			}
			if xPresent && yPresent {
				break
			}
		}
		if xPresent && yPresent {
			adjacency[rule.X] = append(adjacency[rule.X], rule.Y)
			inDegree[rule.Y]++
		}
	}

	// Initialize queue with nodes having in-degree 0
	queue := []int{}
	for _, node := range nodes {
		if inDegree[node] == 0 {
			queue = append(queue, node)
		}
	}

	sorted := []int{}

	for len(queue) > 0 {
		// Dequeue
		current := queue[0]
		queue = queue[1:]
		sorted = append(sorted, current)

		// Decrease in-degree of neighbors
		for _, neighbor := range adjacency[current] {
			inDegree[neighbor]--
			if inDegree[neighbor] == 0 {
				queue = append(queue, neighbor)
			}
		}
	}

	// If sorted contains all nodes, return the sorted order
	if len(sorted) == len(nodes) {
		return sorted, nil
	}

	return nil, fmt.Errorf("cycle detected or incomplete sorting")
}

func main() {
	// Read the input file
	data, err := os.ReadFile("day_5.in")
	if err != nil {
		fmt.Printf("Error reading file: %v\n", err)
		return
	}

	// Split the input into lines
	lines := strings.Split(string(data), "\n")

	// Parse the rules and updates
	var rules []Rule
	var updates [][]int

	for _, line := range lines {
		line = strings.TrimSpace(line)
		if line == "" {
			continue // Skip empty lines
		}
		if strings.Contains(line, "|") {
			// This is a rule
			parts := strings.Split(line, "|")
			if len(parts) != 2 {
				fmt.Printf("Invalid rule line: %s\n", line)
				continue
			}
			xStr := strings.TrimSpace(parts[0])
			yStr := strings.TrimSpace(parts[1])
			x, err1 := strconv.Atoi(xStr)
			y, err2 := strconv.Atoi(yStr)
			if err1 != nil || err2 != nil {
				fmt.Printf("Invalid numbers in rule line: %s\n", line)
				continue
			}
			rules = append(rules, Rule{X: x, Y: y})
		} else if strings.Contains(line, ",") {
			// This is an update
			parts := strings.Split(line, ",")
			var update []int
			valid := true
			for _, part := range parts {
				numStr := strings.TrimSpace(part)
				num, err := strconv.Atoi(numStr)
				if err != nil {
					fmt.Printf("Invalid number in update line: %s\n", line)
					valid = false
					break
				}
				update = append(update, num)
			}
			if valid {
				updates = append(updates, update)
			}
		} else {
			// Unknown line format
			fmt.Printf("Unknown line format: %s\n", line)
		}
	}

	// Part One: Sum of middle page numbers from correctly ordered updates
	sumMiddlePartOne := 0
	var incorrectlyOrderedUpdates [][]int

	for idx, update := range updates {
		// Build a map from page number to its index
		pageIndex := make(map[int]int)
		for i, page := range update {
			pageIndex[page] = i
		}

		// Check all rules
		valid := true
		for _, rule := range rules {
			xIdx, xOk := pageIndex[rule.X]
			yIdx, yOk := pageIndex[rule.Y]
			if xOk && yOk {
				if xIdx >= yIdx {
					// Rule violated
					valid = false
					break
				}
			}
			// If either X or Y is not present, the rule does not apply
		}

		if valid {
			// Find the middle page number
			n := len(update)
			if n == 0 {
				continue // Skip empty updates
			}
			middleIdx := n / 2 // Integer division
			middlePage := update[middleIdx]
			sumMiddlePartOne += middlePage
		} else {
			// Collect incorrectly ordered updates for Part Two
			incorrectlyOrderedUpdates = append(incorrectlyOrderedUpdates, update)
			fmt.Printf("Update %d is incorrectly ordered: %v\n", idx+1, update)
		}
	}

	// Part Two: Sum of middle page numbers after correcting incorrectly ordered updates
	sumMiddlePartTwo := 0

	if len(incorrectlyOrderedUpdates) == 0 {
		fmt.Println("\nNo incorrectly ordered updates found for Part Two.")
	} else {
		fmt.Printf("\nProcessing %d incorrectly ordered updates for Part Two...\n", len(incorrectlyOrderedUpdates))
	}

	for idx, update := range incorrectlyOrderedUpdates {
		// Perform topological sort to reorder the update correctly
		sortedUpdate, err := TopologicalSort(update, rules)
		if err != nil {
			fmt.Printf("Error sorting update %d (%v): %v\n", idx+1, update, err)
			continue
		}

		fmt.Printf("Original Update %d: %v -> Sorted Update: %v\n", idx+1, update, sortedUpdate)

		// Find the middle page number
		n := len(sortedUpdate)
		if n == 0 {
			continue // Skip empty updates
		}
		middleIdx := n / 2 // Integer division
		middlePage := sortedUpdate[middleIdx]
		sumMiddlePartTwo += middlePage
	}

	// Output the results
	fmt.Printf("\nPart One:\n")
	fmt.Printf("Sum of middle page numbers from correctly ordered updates: %d\n\n", sumMiddlePartOne)

	fmt.Printf("Part Two:\n")
	fmt.Printf("Sum of middle page numbers from corrected incorrectly ordered updates: %d\n", sumMiddlePartTwo)
}
