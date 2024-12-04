package main

import (
	"fmt"
	"os"
)

func parseMemory(text string) int {
	result := 0
	for i := 0; i < len(text); i++ {
		if i <= len(text)-4 && text[i:i+4] == "mul(" {
			i += 4
			num1 := ""
			for i < len(text) && text[i] >= '0' && text[i] <= '9' {
				num1 += string(text[i])
				i++
			}

			if i < len(text) && text[i] == ',' {
				i++
				num2 := ""
				for i < len(text) && text[i] >= '0' && text[i] <= '9' {
					num2 += string(text[i])
					i++
				}

				if i < len(text) && text[i] == ')' && num1 != "" && num2 != "" {
					var n1, n2 int
					fmt.Sscanf(num1, "%d", &n1)
					fmt.Sscanf(num2, "%d", &n2)
					result += n1 * n2
				}
			}
		}
	}
	return result
}

func parseMemoryWithConditions(text string) int {
	result := 0
	enabled := true

	for i := 0; i < len(text); i++ {
		if i <= len(text)-4 && text[i:i+4] == "do()" {
			enabled = true
			i += 3
			continue
		}
		if i <= len(text)-7 && text[i:i+7] == "don't()" {
			enabled = false
			i += 6
			continue
		}
		if i <= len(text)-4 && text[i:i+4] == "mul(" {
			i += 4
			num1 := ""
			for i < len(text) && text[i] >= '0' && text[i] <= '9' {
				num1 += string(text[i])
				i++
			}

			if i < len(text) && text[i] == ',' {
				i++
				num2 := ""
				for i < len(text) && text[i] >= '0' && text[i] <= '9' {
					num2 += string(text[i])
					i++
				}

				if i < len(text) && text[i] == ')' && num1 != "" && num2 != "" {
					if enabled {
						var n1, n2 int
						fmt.Sscanf(num1, "%d", &n1)
						fmt.Sscanf(num2, "%d", &n2)
						result += n1 * n2
					}
				}
			}
		}
	}
	return result
}

func main() {
	data, err := os.ReadFile("day_3_input.txt")
	if err != nil {
		panic(err)
	}

	part1 := parseMemory(string(data))
	part2 := parseMemoryWithConditions(string(data))
	
	fmt.Printf("Part 1 - Sum of all multiplications: %d\n", part1)
	fmt.Printf("Part 2 - Sum of enabled multiplications: %d\n", part2)
}