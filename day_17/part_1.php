<?php

// Read input data
$filename = 'day_17.in';
if (!file_exists($filename)) {
    die("Input file $filename not found.\n");
}

$data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (count($data) < 4) {
    die("Input file must contain at least four lines.\n");
}

// Parse initial register values
$A = (int)$data[0];
$B = (int)$data[1];
$C = (int)$data[2];

// Parse the program
$programLine = trim($data[3]);
$program = array_map('intval', explode(',', $programLine));
$programLength = count($program);

// Helper function to get combo operand value
function getComboValue($operand, $A, $B, $C) {
    // Combo mapping:
    // 0-3 => literal values 0-3
    // 4 => A
    // 5 => B
    // 6 => C
    // 7 => reserved (should not appear)
    if ($operand < 4) {
        return $operand;
    }
    switch ($operand) {
        case 4: return $A;
        case 5: return $B;
        case 6: return $C;
        default:
            // Should never happen if input is valid
            throw new Exception("Invalid combo operand: 7 encountered.");
    }
}

// Interpret instructions
// Instruction pointer starts at 0
$ip = 0;
$outputs = [];

// Instruction set reminder:
// 0 adv (combo): A = floor(A / (2^(comboVal)))
// 1 bxl (literal): B = B XOR literal
// 2 bst (combo): B = (comboVal % 8)
// 3 jnz (literal): if A != 0 then ip = literal else continue
// 4 bxc (ignored operand): B = B XOR C
// 5 out (combo): output (comboVal % 8)
// 6 bdv (combo): B = floor(A / (2^(comboVal)))
// 7 cdv (combo): C = floor(A / (2^(comboVal)))

while (true) {
    if ($ip >= $programLength) {
        // Past end of program - halt
        break;
    }

    $opcode = $program[$ip];
    $operand = null;
    if ($ip + 1 < $programLength) {
        $operand = $program[$ip + 1];
    } else {
        // No operand means we halt, as no valid next instruction
        break;
    }

    // By default, after an instruction, we move ip by 2
    $advanceIP = true;

    switch ($opcode) {
        case 0: // adv
            // combo operand, A = floor(A / (2^(comboVal)))
            $val = getComboValue($operand, $A, $B, $C);
            if ($val < 0) {
                // If val is negative, 2^(negative) is not meaningful in this context; 
                // problem doesn't mention negatives as combo values derived from registers,
                // but registers can hold any integer. If negative, 2^(negative) => fraction.
                // We'll handle as per definition: 2^(val). If val is negative, 
                // integer division by fraction would be effectively multiply by something. 
                // The puzzle doesn't forbid negatives, but let's assume A/2^(val) means integer division rounding toward zero.
                // To handle negative exponent correctly, 2^(negative) = 1/(2^(abs(val))).
                // This would potentially multiply A by something, not dividing. 
                // The puzzle does not clarify negative exponent scenario explicitly.
                // We'll assume val >= 0 based on puzzle examples. If encountered, handle gracefully:
                // Just compute 2^(val) normally:
                $power = pow(2, $val);
            } else {
                $power = pow(2, $val);
            }
            if ($power == 0) {
                // If val is negative and big, could cause a fraction. Let's just handle normal as int division:
                // If power is somehow zero (very large negative exponent?), treat as division by 1:
                $power = 1;
            }
            $A = (int)($A / $power);
            break;

        case 1: // bxl (literal)
            $B = $B ^ $operand;
            break;

        case 2: // bst (combo)
            $val = getComboValue($operand, $A, $B, $C);
            $B = $val % 8;
            break;

        case 3: // jnz (literal)
            if ($A != 0) {
                $ip = $operand;
                $advanceIP = false;
            }
            break;

        case 4: // bxc (ignore operand)
            $B = $B ^ $C;
            break;

        case 5: // out (combo)
            $val = getComboValue($operand, $A, $B, $C);
            $outVal = $val % 8;
            $outputs[] = $outVal;
            break;

        case 6: // bdv (combo)
            $val = getComboValue($operand, $A, $B, $C);
            $power = pow(2, $val < 0 ? 0 : $val); // same handling as adv
            $B = (int)($A / $power);
            break;

        case 7: // cdv (combo)
            $val = getComboValue($operand, $A, $B, $C);
            $power = pow(2, $val < 0 ? 0 : $val);
            $C = (int)($A / $power);
            break;

        default:
            // Invalid opcode
            // According to the problem, opcode should be 0-7
            break 2;
    }

    if ($advanceIP) {
        $ip += 2;
    }
}

// Once halted, print the outputs joined by commas
echo implode(',', $outputs) . "\n";
