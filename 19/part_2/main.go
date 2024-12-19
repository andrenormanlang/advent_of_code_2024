package main

import (
    "bufio"
    "fmt"
    "log"
    "os"
    "strings"
)

// Function to check if the design can be segmented and return the number of ways
func countArrangements(design string, patterns []string) int64 {
    n := len(design)
    dp := make([]int64, n+1)
    dp[0] = 1 // Base case: empty string

    for i := 1; i <= n; i++ {
        for _, pattern := range patterns {
            pLen := len(pattern)
            if i >= pLen && design[i-pLen:i] == pattern {
                dp[i] += dp[i-pLen]
            }
        }
    }

    return dp[n]
}

func main() {
    inputFileName := "day_19.in"

    // Open the input file
    file, err := os.Open(inputFileName)
    if err != nil {
        log.Fatalf("Error opening file %s: %v", inputFileName, err)
    }
    defer file.Close()

    scanner := bufio.NewScanner(file)
    var patternsLine string
    var patterns []string
    var designs []string
    isPatternSection := true

    for scanner.Scan() {
        line := strings.TrimSpace(scanner.Text())

        // Check for blank line to switch sections
        if line == "" {
            isPatternSection = false
            continue
        }

        if isPatternSection {
            if patternsLine == "" {
                patternsLine = line
                // Split patterns by comma and trim spaces
                rawPatterns := strings.Split(patternsLine, ",")
                for _, p := range rawPatterns {
                    trimmed := strings.TrimSpace(p)
                    if trimmed != "" {
                        patterns = append(patterns, trimmed)
                    }
                }
            }
        } else {
            // Collect designs
            designs = append(designs, line)
        }
    }

    if err := scanner.Err(); err != nil {
        log.Fatalf("Error reading file: %v", err)
    }

    // Check if patterns were found
    if len(patterns) == 0 {
        fmt.Println("0")
        return
    }

    // Iterate over each design and count arrangements
    totalArrangements := int64(0)
    for _, design := range designs {
        arrangements := countArrangements(design, patterns)
        totalArrangements += arrangements
    }

    // Output the total number of arrangements
    fmt.Println(totalArrangements)
}
