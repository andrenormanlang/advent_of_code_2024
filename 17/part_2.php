<?php

// Read the input file
$input = file_get_contents('day_17.in');

// Parse initial register values and program
$lines = array_map('trim', explode("\n", $input));

$A_original = 0; $B = 0; $C = 0; $program = [];

foreach ($lines as $line) {
    if (preg_match('/^Register A:\s+(\d+)/', $line, $m)) {
        $A_original = (int)$m[1]; // Original A, but we won't use it directly
    } elseif (preg_match('/^Register B:\s+(\d+)/', $line, $m)) {
        $B = (int)$m[1];
    } elseif (preg_match('/^Register C:\s+(\d+)/', $line, $m)) {
        $C = (int)$m[1];
    } elseif (preg_match('/^Program:\s+(.*)/', $line, $m)) {
        $programStr = $m[1];
        $program = array_map('trim', explode(',', $programStr));
        $program = array_map('intval', $program);
    }
}

if (empty($program)) {
    die("No program found. Check your day_17.in format.\n");
}

// Helper function to get combo operand value
function comboValue($operand, $A, $B, $C) {
    // Combo operands 0-3 = literal values 0-3
    // 4 = value in A
    // 5 = value in B
    // 6 = value in C
    // 7 is reserved
    if ($operand <= 3) {
        return $operand;
    }
    switch ($operand) {
        case 4: return $A;
        case 5: return $B;
        case 6: return $C;
        default:
            throw new Exception("Invalid combo operand encountered: $operand");
    }
}

// Function to run the program with given initial A, B, C
function runProgram($program, $A, $B, $C) {
    $ip = 0;
    $outputValues = [];

    while (true) {
        if ($ip < 0 || $ip >= count($program)) {
            // Past end = halt
            break;
        }
        $opcode = $program[$ip];
        if ($ip + 1 >= count($program)) {
            // No operand available - halt
            break;
        }
        $operand = $program[$ip + 1];

        switch ($opcode) {
            case 0: // adv: A = floor(A / (2^(combo operand)))
                $val = comboValue($operand, $A, $B, $C);
                $denominator = pow(2, $val);
                $A = intdiv($A, $denominator);
                $ip += 2;
                break;

            case 1: // bxl: B = B XOR (literal operand)
                $B = $B ^ $operand;
                $ip += 2;
                break;

            case 2: // bst: B = combo operand value % 8
                $val = comboValue($operand, $A, $B, $C);
                $B = $val % 8;
                $ip += 2;
                break;

            case 3: // jnz: if A != 0, ip = literal operand else ip+=2
                if ($A != 0) {
                    $ip = $operand;
                } else {
                    $ip += 2;
                }
                break;

            case 4: // bxc: B = B XOR C (operand ignored)
                $B = $B ^ $C;
                $ip += 2;
                break;

            case 5: // out: output combo operand value % 8
                $val = comboValue($operand, $A, $B, $C);
                $outVal = $val % 8;
                $outputValues[] = $outVal;
                $ip += 2;
                break;

            case 6: // bdv: B = floor(A / 2^(combo operand))
                $val = comboValue($operand, $A, $B, $C);
                $denominator = pow(2, $val);
                $B = intdiv($A, $denominator);
                $ip += 2;
                break;

            case 7: // cdv: C = floor(A / 2^(combo operand))
                $val = comboValue($operand, $A, $B, $C);
                $denominator = pow(2, $val);
                $C = intdiv($A, $denominator);
                $ip += 2;
                break;

            default:
                // Invalid opcode => halt
                break 2;
        }
    }

    return $outputValues;
}

// We need to find the lowest positive A that causes the program's output
// to match the program itself.
//
// The program itself is a sequence of integers (e.g. 0,3,5,4,3,0).
// The output is also a sequence of integers (mod 8).
// We must confirm that the output matches every element of $program in order and count.
//
// Key point: The output instructions produce values mod 8, but the program's instructions
// are 3-bit numbers (0-7), so the program instructions are naturally in the range 0-7.
// So we must check if output == program exactly.

$targetOutput = $program; // We want the output to match the program

// Since we are told to find the lowest positive initial A, start at 1 and go upwards
// Note: If this search takes too long, consider break conditions or a max search limit.
$A_candidate = 1;
while (true) {
    $output = runProgram($program, $A_candidate, $B, $C);
    if ($output === $targetOutput) {
        echo $A_candidate, "\n";
        break;
    }
    $A_candidate++;
    // Add a safeguard if needed (e.g., if $A_candidate gets too large)
    // if ($A_candidate > 10000000) { break; }
}
