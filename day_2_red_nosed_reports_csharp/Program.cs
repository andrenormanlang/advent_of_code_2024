using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;

namespace AdventOfCode
{
    class Program
    {
        /// <summary>
        /// Determines if a report is safe based on two criteria:
        /// 1. The levels are either strictly increasing or strictly decreasing.
        /// 2. The difference between any two adjacent levels is between 1 and 3 (inclusive).
        /// </summary>
        /// <param name="report">A list of integers representing the levels.</param>
        /// <returns>True if the report is safe, False otherwise.</returns>
        static bool IsSafe(List<int> levels)
        {
            if (levels == null || levels.Count < 2)
            {
                // A single level can't determine safety
                return false;
            }

            // Determine if strictly increasing or strictly decreasing
            bool isIncreasing = true;
            bool isDecreasing = true;

            for (int i = 1; i < levels.Count; i++)
            {
                if (levels[i] <= levels[i - 1])
                {
                    isIncreasing = false;
                }
                if (levels[i] >= levels[i - 1])
                {
                    isDecreasing = false;
                }
            }

            if (!isIncreasing && !isDecreasing)
            {
                // Not monotonic
                return false;
            }

            // Check adjacent differences
            for (int i = 1; i < levels.Count; i++)
            {
                int diff = Math.Abs(levels[i] - levels[i - 1]);
                if (diff < 1 || diff > 3)
                {
                    return false;
                }
            }

            return true;
        }

        /// <summary>
        /// Determines if a report can be made safe by removing a single level.
        /// </summary>
        /// <param name="report">A string of space-separated integers representing the levels.</param>
        /// <returns>True if the report can be made safe by removing one level, False otherwise.</returns>
        static bool IsSafeWithDampener(string report)
        {
            if (string.IsNullOrWhiteSpace(report))
                return false;

            // Split the report into integer levels
            List<int> levels;
            try
            {
                levels = report.Trim()
                               .Split(new char[] { ' ', '\t' }, StringSplitOptions.RemoveEmptyEntries)
                               .Select(int.Parse)
                               .ToList();
            }
            catch
            {
                // If parsing fails, consider the report unsafe
                return false;
            }

            // If the original report is safe, return true
            if (IsSafe(levels))
            {
                return true;
            }

            // Try removing each level to see if it becomes safe
            for (int i = 0; i < levels.Count; i++)
            {
                // Create a new list with one element removed
                var modifiedLevels = new List<int>(levels);
                modifiedLevels.RemoveAt(i);

                // Check if the modified list is safe
                if (IsSafe(modifiedLevels))
                {
                    return true;
                }
            }

            // No way to make the report safe by removing a single level
            return false;
        }

        static void Main(string[] args)
        {
            // Path to the input file
            string inputFilePath = "day_2_input.txt";

            if (!File.Exists(inputFilePath))
            {
                Console.WriteLine($"Input file '{inputFilePath}' not found.");
                return;
            }

            try
            {
                // Read all lines from the input file
                var reports = File.ReadAllLines(inputFilePath)
                                  .Where(line => !string.IsNullOrWhiteSpace(line))
                                  .ToList();

                // Part 1: Count the number of safe reports without the Problem Dampener
                int safeReportsCount = reports.Count(report => IsSafe(report.Split(' ').Select(int.Parse).ToList()));

                // Part 2: Count the number of safe reports with the Problem Dampener
                int safeReportsWithDampenerCount = reports.Count(report => IsSafeWithDampener(report));

                // Output the results
                Console.WriteLine($"The number of safe reports is: {safeReportsCount}");
                Console.WriteLine($"The number of safe reports with the Problem Dampener is: {safeReportsWithDampenerCount}");
            }
            catch (Exception ex)
            {
                Console.WriteLine($"An error occurred while processing the input file: {ex.Message}");
            }
        }
    }
}
