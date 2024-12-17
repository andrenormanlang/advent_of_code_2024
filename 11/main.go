package main

import (
	"bufio"
	"fmt"
	"log"
	"os"
	"strconv"
	"strings"
)

// splitNumber splits a number with an even number of digits into two numbers.
// It removes leading zeros from both halves. If a half results in an empty string,
// it is treated as 0.
func splitNumber(n int64) (int64, int64) {
	s := strconv.FormatInt(n, 10)
	length := len(s)

	// Ensure the number has an even number of digits
	if length%2 != 0 {
		log.Fatalf("Attempted to split a number with an odd number of digits: %d", n)
	}

	mid := length / 2
	leftStr := s[:mid]
	rightStr := s[mid:]

	// Remove leading zeros
	leftStr = strings.TrimLeft(leftStr, "0")
	rightStr = strings.TrimLeft(rightStr, "0")

	// If trimming results in empty string, treat it as "0"
	if leftStr == "" {
		leftStr = "0"
	}
	if rightStr == "" {
		rightStr = "0"
	}

	left, err := strconv.ParseInt(leftStr, 10, 64)
	if err != nil {
		log.Fatalf("Invalid left split number: %s", leftStr)
	}

	right, err := strconv.ParseInt(rightStr, 10, 64)
	if err != nil {
		log.Fatalf("Invalid right split number: %s", rightStr)
	}

	return left, right
}

// blink processes the current arrangement of stones and returns the new arrangement
// after applying the blinking rules once.
// It uses a map to track the count of each unique stone number.
func blink(currentStones map[int64]int64) map[int64]int64 {
	newStones := make(map[int64]int64)

	for stone, count := range currentStones {
		switch {
		case stone == 0:
			// Rule 1: Replace 0 with 1
			newStones[1] += count
		case len(strconv.FormatInt(stone, 10))%2 == 0:
			// Rule 2: Split into two stones
			left, right := splitNumber(stone)
			newStones[left] += count
			newStones[right] += count
		default:
			// Rule 3: Replace with stone * 2024
			newStone := stone * 2024
			newStones[newStone] += count
		}
	}

	return newStones
}

// readStones reads the initial arrangement of stones from the specified file.
// It expects the file to contain a single line with space-separated integers.
func readStones(filename string) (map[int64]int64, error) {
	file, err := os.Open(filename)
	if err != nil {
		return nil, fmt.Errorf("failed to open file '%s': %v", filename, err)
	}
	defer file.Close()

	stones := make(map[int64]int64)
	scanner := bufio.NewScanner(file)
	for scanner.Scan() {
		line := scanner.Text()
		// Split by any whitespace
		tokens := strings.Fields(line)
		for _, tok := range tokens {
			num, err := strconv.ParseInt(tok, 10, 64)
			if err != nil {
				return nil, fmt.Errorf("invalid number '%s' in input file: %v", tok, err)
			}
			stones[num]++
		}
	}

	if err := scanner.Err(); err != nil {
		return nil, fmt.Errorf("error reading file '%s': %v", filename, err)
	}

	return stones, nil
}

func main() {
	// Read the initial stones from 'day_11.in'
	stones, err := readStones("day_11.in")
	if err != nil {
		log.Fatalf("Error reading stones: %v", err)
	}

	// Part One: Perform 25 blinks
	partOneBlinks := 25
	currentStonesPartOne := make(map[int64]int64)
	for k, v := range stones {
		currentStonesPartOne[k] = v
	}

	for i := 0; i < partOneBlinks; i++ {
		currentStonesPartOne = blink(currentStonesPartOne)
	}

	// Calculate total stones after 25 blinks
	totalStonesPartOne := int64(0)
	for _, count := range currentStonesPartOne {
		totalStonesPartOne += count
	}
	fmt.Printf("Part One - Number of stones after %d blinks: %d\n", partOneBlinks, totalStonesPartOne)

	// Part Two: Perform 75 blinks
	totalBlinks := 75
	currentStonesPartTwo := make(map[int64]int64)
	for k, v := range stones {
		currentStonesPartTwo[k] = v
	}

	for i := 0; i < totalBlinks; i++ {
		currentStonesPartTwo = blink(currentStonesPartTwo)
	}

	// Calculate total stones after 75 blinks
	totalStonesPartTwo := int64(0)
	for _, count := range currentStonesPartTwo {
		totalStonesPartTwo += count
	}
	fmt.Printf("Part Two - Number of stones after %d blinks: %d\n", totalBlinks, totalStonesPartTwo)
}
