const fs = require('fs');
const path = require('path');

/**
 * Helper function to generate all unique permutations of a string.
 * @param {string} str - The input string.
 * @returns {string[]} - An array of unique permutations.
 */
function getUniquePermutations(str) {
    const results = new Set();

    function permute(arr, memo = []) {
        if (arr.length === 0) {
            results.add(memo.join(''));
            return;
        }
        for (let i = 0; i < arr.length; i++) {
            // Skip duplicates
            if (i > 0 && arr[i] === arr[i - 1]) continue;
            const curr = arr.slice();
            const next = curr.splice(i, 1);
            permute(curr.slice(), memo.concat(next));
        }
    }

    const sortedStr = str.split('').sort().join('');
    permute(sortedStr.split(''));
    return Array.from(results);
}

/**
 * Helper function to compute the cartesian product of multiple arrays.
 * @param {Array[]} arrays - An array of arrays.
 * @returns {Array[]} - The cartesian product as an array of arrays.
 */
function cartesianProduct(arrays) {
    return arrays.reduce((acc, curr) => {
        const res = [];
        acc.forEach(a => {
            curr.forEach(b => {
                res.push(a.concat([b]));
            });
        });
        return res;
    }, [[]]);
}

/**
 * Reads the input file and returns an array of codes.
 * @param {string} filePath - The path to the input file.
 * @returns {string[]} - An array of codes.
 */
function readInput(filePath) {
    const data = fs.readFileSync(filePath, 'utf8');
    return data.split(/\r?\n/).filter(line => line.trim() !== '');
}

// Define the numeric keypad layout
const numeric_keys = {
    "7": [0, 0],
    "8": [0, 1],
    "9": [0, 2],
    "4": [1, 0],
    "5": [1, 1],
    "6": [1, 2],
    "1": [2, 0],
    "2": [2, 1],
    "3": [2, 2],
    "0": [3, 1],
    "A": [3, 2]
};

// Define the directional keypad layout
const direction_keys = {
    "^": [0, 1],
    "A": [0, 2],
    "<": [1, 0],
    "v": [1, 1],
    ">": [1, 2]
};

// Define the move directions
const dd = {
    ">": [0, 1],
    "v": [1, 0],
    "<": [0, -1],
    "^": [-1, 0]
};

/**
 * Generates all possible ways to press the given code on the specified keypad.
 * @param {string} code - The code to be pressed.
 * @param {Object} keypad - The keypad mapping.
 * @returns {string[]} - An array of valid sequences.
 */
function ways(code, keypad) {
    const parts = [];
    let cur_loc = keypad["A"];

    for (let char of code) {
        const next_loc = keypad[char];
        const di = next_loc[0] - cur_loc[0];
        const dj = next_loc[1] - cur_loc[1];
        let moves = "";

        if (di > 0) {
            moves += 'v'.repeat(di);
        } else if (di < 0) {
            moves += '^'.repeat(-di);
        }

        if (dj > 0) {
            moves += '>'.repeat(dj);
        } else if (dj < 0) {
            moves += '<'.repeat(-dj);
        }

        // Generate all unique permutations of the moves and append 'A'
        const raw_combos = getUniquePermutations(moves).map(p => p + 'A');
        const combos = [];

        // Precompute a Set of valid positions for quick lookup
        const validPositions = new Set(Object.values(keypad).map(coord => coord.join(',')));

        for (let combo of raw_combos) {
            let ci = cur_loc[0];
            let cj = cur_loc[1];
            let valid = true;

            // Iterate through the moves excluding the last 'A'
            for (let i = 0; i < combo.length - 1; i++) {
                const moveChar = combo[i];
                const move = dd[moveChar];
                if (!move) {
                    valid = false;
                    break;
                }
                ci += move[0];
                cj += move[1];
                if (!validPositions.has(`${ci},${cj}`)) {
                    valid = false;
                    break;
                }
            }

            if (valid) {
                combos.push(combo);
            }
        }

        // If no valid combos found for this character, return empty array
        if (combos.length === 0) {
            return [];
        }

        parts.push(combos);
        cur_loc = next_loc;
    }

    // Compute the cartesian product of all parts
    const all_combinations = cartesianProduct(parts);

    // Concatenate the sequences
    const sequences = all_combinations.map(seq => seq.join(''));
    return sequences;
}

/**
 * Determines the shortest sequence length needed to press the code across three layers of keypads.
 * @param {string} code - The code to be pressed.
 * @returns {number} - The length of the shortest valid sequence.
 */
function shortest3(code) {
    // Step 1: Press the code on the numeric keypad
    const ways1 = ways(code, numeric_keys);
    if (ways1.length === 0) {
        return Infinity;
    }

    // Step 2: Press the sequences obtained from ways1 on the first directional keypad
    let ways2 = [];
    for (let way1 of ways1) {
        const seqs = ways(way1, direction_keys);
        ways2 = ways2.concat(seqs);
    }

    if (ways2.length === 0) {
        return Infinity;
    }

    // Step 3: Press the sequences obtained from ways2 on the second directional keypad
    let ways3 = [];
    for (let way2 of ways2) {
        const seqs = ways(way2, direction_keys);
        ways3 = ways3.concat(seqs);
    }

    if (ways3.length === 0) {
        return Infinity;
    }

    // Find the minimum sequence length
    let minLength = Infinity;
    for (let seq of ways3) {
        if (seq.length < minLength) {
            minLength = seq.length;
        }
    }

    return minLength;
}

/**
 * Main function to process input codes and calculate the total complexity.
 */
function main() {
    const inputPath = path.join(__dirname, 'day_21.in');
    const codes = readInput(inputPath);

    let totalComplexity = 0;

    for (let code of codes) {
        // Extract the numeric part by removing leading zeros and the 'A' at the end
        const match = code.match(/^0*(\d+)A$/);
        if (!match) {
            console.error(`Invalid code format: ${code}`);
            continue;
        }
        const numericPart = parseInt(match[1], 10);

        // Calculate the shortest sequence length
        const seqLength = shortest3(code);

        if (seqLength === Infinity) {
            console.error(`No valid sequence found for code: ${code}`);
            continue;
        }

        // Calculate complexity and add to total
        const complexity = seqLength * numericPart;
        console.log(`Code: ${code}, Sequence Length: ${seqLength}, Numeric Part: ${numericPart}, Complexity: ${complexity}`);
        totalComplexity += complexity;
    }

    console.log(`Total Complexity: ${totalComplexity}`);
}

// Execute the main function
main();
