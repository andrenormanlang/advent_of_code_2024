const fs = require('fs');
const path = require('path');

/**
 * Parses the input file and returns an array of machine configurations.
 * Each configuration includes Button A's movements, Button B's movements, and Prize location.
 * @param {string} filePath - Path to the input file.
 * @returns {Array} Array of machine configurations.
 */
function parseInput(filePath) {
    const input = fs.readFileSync(filePath, 'utf8');
    const machines = [];
    const machineBlocks = input.trim().split(/\n\s*\n/); // Split by blank lines, handling possible spaces

    machineBlocks.forEach((block, index) => {
        const lines = block.split('\n').map(line => line.trim());
        if (lines.length < 3) {
            console.warn(`Machine ${index + 1}: Incomplete configuration. Skipping.`);
            return;
        }

        // Extract Button A movement
        const buttonARegex = /Button\s+A:\s*X([+-]?\d+),\s*Y([+-]?\d+)/i;
        const buttonAMatch = lines[0].match(buttonARegex);
        if (!buttonAMatch) {
            console.warn(`Machine ${index + 1}: Invalid Button A format: "${lines[0]}"`);
            return;
        }
        const A_x = parseInt(buttonAMatch[1], 10);
        const A_y = parseInt(buttonAMatch[2], 10);

        // Extract Button B movement
        const buttonBRegex = /Button\s+B:\s*X([+-]?\d+),\s*Y([+-]?\d+)/i;
        const buttonBMatch = lines[1].match(buttonBRegex);
        if (!buttonBMatch) {
            console.warn(`Machine ${index + 1}: Invalid Button B format: "${lines[1]}"`);
            return;
        }
        const B_x = parseInt(buttonBMatch[1], 10);
        const B_y = parseInt(buttonBMatch[2], 10);

        // Extract Prize location
        const prizeRegex = /Prize:\s*X\s*=\s*([+-]?\d+),\s*Y\s*=\s*([+-]?\d+)/i;
        const prizeMatch = lines[2].match(prizeRegex);
        if (!prizeMatch) {
            console.warn(`Machine ${index + 1}: Invalid Prize format: "${lines[2]}"`);
            return;
        }
        const Prize_x = parseInt(prizeMatch[1], 10);
        const Prize_y = parseInt(prizeMatch[2], 10);

        // Log the parsed data for debugging
        console.log(`Machine ${index + 1}:`);
        console.log(`  Button A: X${A_x}, Y${A_y}`);
        console.log(`  Button B: X${B_x}, Y${B_y}`);
        console.log(`  Prize: X=${Prize_x}, Y=${Prize_y}`);

        machines.push({
            A: { x: A_x, y: A_y },
            B: { x: B_x, y: B_y },
            Prize: { x: Prize_x, y: Prize_y }
        });
    });

    return machines;
}

/**
 * For a given machine, finds the minimum token cost to reach the prize using brute-force (Part One).
 * @param {Object} machine - The machine configuration.
 * @returns {number|null} Minimum token cost or null if impossible within 100 presses.
 */
function findMinimumTokenCostPart1(machine) {
    const { A, B, Prize } = machine;
    const maxPresses = 100;
    let minCost = null;

    for (let a = 0; a <= maxPresses; a++) {
        for (let b = 0; b <= maxPresses; b++) {
            const totalX = a * A.x + b * B.x;
            const totalY = a * A.y + b * B.y;

            if (totalX === Prize.x && totalY === Prize.y) {
                const cost = a * 3 + b * 1;
                if (minCost === null || cost < minCost) {
                    minCost = cost;
                }
            }
        }
    }

    return minCost;
}

/**
 * For a given machine, finds the minimum token cost to reach the prize using mathematical solution (Part Two).
 * @param {Object} machine - The machine configuration.
 * @returns {number|null} Minimum token cost or null if impossible.
 */
function findMinimumTokenCostPart2(machine) {
    const { A, B, Prize } = machine;
    const det = A.x * B.y - A.y * B.x;

    if (det === 0) {
        // Check if the system has infinitely many solutions
        if (A.x * Prize.y === A.y * Prize.x) {
            // Infinitely many solutions; find the one with minimal cost
            // We can iterate through possible values of a or b to find minimal cost
            // Here, we'll iterate a from 0 to a reasonable limit to find minimal cost
            const maxPresses = 1000000; // Adjust as needed
            let minCost = null;
            for (let a = 0; a <= maxPresses; a++) {
                if ((Prize.x - A.x * a) % B.x !== 0) continue;
                const b = (Prize.x - A.x * a) / B.x;
                if (b < 0 || !Number.isInteger(b)) continue;
                // Verify Y coordinate
                if (A.y * a + B.y * b !== Prize.y) continue;
                const cost = 3 * a + b;
                if (minCost === null || cost < minCost) {
                    minCost = cost;
                }
                // Early exit if cost is minimal possible
                if (cost === 0) break;
            }
            return minCost;
        } else {
            // No solutions
            return null;
        }
    } else {
        const a_num = Prize.x * B.y - Prize.y * B.x;
        const b_num = A.x * Prize.y - A.y * Prize.x;

        // Check if a_num and b_num are divisible by det
        if (a_num % det !== 0 || b_num % det !== 0) {
            return null;
        }

        const a = a_num / det;
        const b = b_num / det;

        if (a < 0 || b < 0) {
            return null;
        }

        // Verify that a and b are integers
        if (!Number.isInteger(a) || !Number.isInteger(b)) {
            return null;
        }

        // Calculate token cost
        const cost = 3 * a + b;
        return cost;
    }
}

/**
 * Main function to process all machines and calculate total tokens for both Part One and Part Two.
 */
function main() {
    // Read the input file
    const inputFilePath = path.join(__dirname, 'day_13.in');
    let machines;
    try {
        machines = parseInput(inputFilePath);
    } catch (error) {
        console.error(`Error reading or parsing the input file: ${error.message}`);
        return;
    }

    if (machines.length === 0) {
        console.log('No valid machine configurations found.');
        return;
    }

    // Part One
    console.log('\n--- Part One ---');
    let totalTokensPart1 = 0;
    let prizesWonPart1 = 0;
    const failedMachinesPart1 = [];

    machines.forEach((machine, index) => {
        const minCost = findMinimumTokenCostPart1(machine);
        if (minCost !== null) {
            totalTokensPart1 += minCost;
            prizesWonPart1 += 1;
            console.log(`Machine ${index + 1}: Can win prize with minimum cost of ${minCost} tokens.`);
        } else {
            failedMachinesPart1.push(index + 1);
            console.log(`Machine ${index + 1}: Cannot win the prize.`);
        }
    });

    console.log(`\nTotal Prizes Won (Part 1): ${prizesWonPart1}`);
    console.log(`Total Tokens Spent (Part 1): ${totalTokensPart1}`);

    if (failedMachinesPart1.length > 0) {
        console.log(`\nMachines that cannot win prizes (Part 1): ${failedMachinesPart1.join(', ')}`);
    }

    // Part Two
    console.log('\n--- Part Two ---');
    let totalTokensPart2 = 0;
    let prizesWonPart2 = 0;
    const failedMachinesPart2 = [];

    machines.forEach((machine, index) => {
        // Adjust prize coordinates by adding 10000000000000 to both X and Y
        const adjustedPrize = {
            x: machine.Prize.x + 10000000000000,
            y: machine.Prize.y + 10000000000000
        };

        // Create a new machine object with adjusted prize
        const adjustedMachine = {
            A: machine.A,
            B: machine.B,
            Prize: adjustedPrize
        };

        const minCost = findMinimumTokenCostPart2(adjustedMachine);
        if (minCost !== null) {
            totalTokensPart2 += minCost;
            prizesWonPart2 += 1;
            console.log(`Machine ${index + 1}: Can win prize with minimum cost of ${minCost} tokens.`);
        } else {
            failedMachinesPart2.push(index + 1);
            console.log(`Machine ${index + 1}: Cannot win the prize.`);
        }
    });

    console.log(`\nTotal Prizes Won (Part 2): ${prizesWonPart2}`);
    console.log(`Total Tokens Spent (Part 2): ${totalTokensPart2}`);

    if (failedMachinesPart2.length > 0) {
        console.log(`\nMachines that cannot win prizes (Part 2): ${failedMachinesPart2.join(', ')}`);
    }
}

// Execute the main function
main();
