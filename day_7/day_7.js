// day_7.js

import { readFile } from "fs/promises";

// Represents a single calibration equation.
class Equation {
  constructor(testValue, numbers) {
    this.testValue = testValue;
    this.numbers = numbers;
  }
}

// Reads and parses the input file into an array of Equation instances.
async function readInput(filename) {
  try {
    const data = await readFile(filename, "utf-8");
    const lines = data.split("\n");
    const equations = [];

    lines.forEach((line, index) => {
      line = line.trim();
      if (line === "") return; // Skip empty lines

      const parts = line.split(":");
      if (parts.length !== 2) {
        throw new Error(`Invalid format at line ${index + 1}: "${line}"`);
      }

      const testValueStr = parts[0].trim();
      const testValue = parseInt(testValueStr, 10);
      if (isNaN(testValue)) {
        throw new Error(`Invalid test value at line ${index + 1}: "${testValueStr}"`);
      }

      const numsStr = parts[1].trim();
      const numsParts = numsStr.split(/\s+/);
      const numbers = numsParts.map((numStr) => {
        const num = parseInt(numStr, 10);
        if (isNaN(num)) {
          throw new Error(`Invalid number at line ${index + 1}: "${numStr}"`);
        }
        return num;
      });

      if (numbers.length === 0) {
        throw new Error(`No numbers found at line ${index + 1}`);
      }

      equations.push(new Equation(testValue, numbers));
    });

    return equations;
  } catch (error) {
    throw new Error(`Error reading input: ${error.message}`);
  }
}

// Generates all possible operator combinations for a given set of operators and number of positions.
function generateOperatorCombinations(operatorSet, n) {
  if (n === 0) return [[]]; // No operators to insert

  const combinations = [];

  function backtrack(currentCombo, position) {
    if (position === n) {
      combinations.push([...currentCombo]);
      return;
    }

    for (const op of operatorSet) {
      currentCombo.push(op);
      backtrack(currentCombo, position + 1);
      currentCombo.pop();
    }
  }

  backtrack([], 0);
  return combinations;
}

// Concatenates two numbers as per the '||' operator.
function concatenateNumbers(a, b) {
  // Handle negative numbers if necessary
  const aStr = a.toString();
  const bStr = b.toString();
  return parseInt(aStr + bStr, 10);
}

// Evaluates the expression strictly from left to right given numbers and operators.
function evaluateExpression(numbers, operators) {
  let result = numbers[0];
  for (let i = 0; i < operators.length; i++) {
    const op = operators[i];
    const nextNum = numbers[i + 1];
    if (op === "+") {
      result += nextNum;
    } else if (op === "*") {
      result *= nextNum;
    } else if (op === "||") {
      result = concatenateNumbers(result, nextNum);
    } else {
      throw new Error(`Unsupported operator: "${op}"`);
    }
  }
  return result;
}

// Determines if the equation is valid by checking all operator combinations.
function isEquationValid(eq, operatorSet) {
  const numCount = eq.numbers.length;
  if (numCount === 1) {
    return eq.numbers[0] === eq.testValue;
  }

  const operatorCount = numCount - 1;
  const combinations = generateOperatorCombinations(operatorSet, operatorCount);

  for (const ops of combinations) {
    const evaluated = evaluateExpression(eq.numbers, ops);
    if (evaluated === eq.testValue) {
      return true;
    }
  }

  return false;
}

// Main function to orchestrate reading input, processing equations, and outputting the result.
async function main() {
  const inputFile = "day_7.in";

  try {
    const equations = await readInput(inputFile);

    // Define operator sets for Part One and Part Two
    const operatorsPart1 = ["+", "*"];
    const operatorsPart2 = ["+", "*", "||"];

    // Initialize separate Sets to store indices of valid equations for Part One and Part Two
    const validEquationIndicesPart1 = new Set();
    const validEquationIndicesPart2 = new Set();

    // Process Part One
    equations.forEach((eq, index) => {
      if (isEquationValid(eq, operatorsPart1)) {
        validEquationIndicesPart1.add(index);
      }
    });

    // Process Part Two (only for equations not already valid in Part One)
    equations.forEach((eq, index) => {
      if (!validEquationIndicesPart1.has(index) && isEquationValid(eq, operatorsPart2)) {
        validEquationIndicesPart2.add(index);
      }
    });

    // Calculate the total calibration results
    let totalCalibrationPart1 = 0;
    validEquationIndicesPart1.forEach((index) => {
      totalCalibrationPart1 += equations[index].testValue;
    });

    let totalCalibrationPart2 = 0;
    validEquationIndicesPart2.forEach((index) => {
      totalCalibrationPart2 += equations[index].testValue;
    });

    let totalCalibrationCombined = totalCalibrationPart1 + totalCalibrationPart2;

    console.log(`Part One Calibration Result: ${totalCalibrationPart1}`);
    console.log(`Part Two Calibration Result: ${totalCalibrationPart2}`);
    console.log(`Total Calibration Result: ${totalCalibrationCombined}`);
  } catch (error) {
    console.error(error.message);
    process.exit(1);
  }
}

// Execute the main function.
main();
