<?php

// Read the input file
$input = file_get_contents('day_17.in');

// Parse initial register values and program
// Assuming input is in a format like:
// Register A: 729
// Register B: 0
// Register C: 0
//
// Program: 0,1,5,4,3,0

$lines = array_map('trim', explode("\n", $input));

$A = 0; $B = 0; $C = 0;
$program = [];

// Parse registers
foreach ($lines as $line) {
    if (preg_match('/^Register A:\s+(\d+)/', $line, $m)) {
        $A = (int)$m[1];
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

// If program is empty or registers not set, you may need to adjust parsing
if (empty($program)) {
    die("No program found. Check your day_17.in format.\n");
}

// Define a helper function to get combo operand value:
function comboValue($operand, $A, $B, $C) {
    // Combo operands 0-3 = literal values 0-3
    // 4 = value in A
    // 5 = value in B
    // 6 = value in C
    // 7 is reserved and won't appear
    if ($operand <= 3) {
        return $operand;
    } else {
        switch ($operand) {
            case 4: return $A;
            case 5: return $B;
            case 6: return $C;
            default:
                // Operand 7 should never appear in a valid program.
                throw new Exception("Invalid combo operand encountered: $operand");
        }
    }
}

// The instructions work on 3-bit opcodes and operands (0-7)
$outputValues = [];
$ip = 0; // instruction pointer

while (true) {
    if ($ip < 0 || $ip >= count($program)) {
        // Past end of program - halt
        break;
    }
    $opcode = $program[$ip];
    $operand = null;
    if ($ip + 1 < count($program)) {
        $operand = $program[$ip + 1];
    } else {
        // If there's no operand for the next opcode, we halt at this point.
        // (Though problem states it halts if opcode read past end, so maybe break.)
        break;
    }

    // Process instruction based on opcode:
    switch ($opcode) {
        case 0: 
            // adv: A = floor(A / (2^(combo operand)))
            $val = comboValue($operand, $A, $B, $C);
            $denominator = pow(2, $val);
            $A = intdiv($A, $denominator);
            $ip += 2;
            break;

        case 1: 
            // bxl: B = B XOR (literal operand)
            $B = $B ^ $operand;
            $ip += 2;
            break;

        case 2:
            // bst: B = (combo operand value) mod 8
            $val = comboValue($operand, $A, $B, $C);
            $B = $val % 8;
            $ip += 2;
            break;

        case 3:
            // jnz: if A != 0 then ip = literal operand else ip +=2
            if ($A != 0) {
                $ip = $operand; 
            } else {
                $ip += 2;
            }
            break;

        case 4:
            // bxc: B = B XOR C (operand is ignored)
            $B = $B ^ $C;
            $ip += 2;
            break;

        case 5:
            // out: output (combo operand value % 8)
            $val = comboValue($operand, $A, $B, $C);
            $outVal = $val % 8;
            $outputValues[] = $outVal;
            $ip += 2;
            break;

        case 6:
            // bdv: B = floor(A / (2^(combo operand)))
            $val = comboValue($operand, $A, $B, $C);
            $denominator = pow(2, $val);
            $B = intdiv($A, $denominator);
            $ip += 2;
            break;

        case 7:
            // cdv: C = floor(A / (2^(combo operand)))
            $val = comboValue($operand, $A, $B, $C);
            $denominator = pow(2, $val);
            $C = intdiv($A, $denominator);
            $ip += 2;
            break;

        default:
            // Invalid opcode or past end => halt
            break 2;
    }
}

// Print the output values joined by commas
echo implode(",", $outputValues) . "\n";

