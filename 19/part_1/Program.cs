using System;
using System.Collections.Generic;
using System.IO;

class LinenLayout
{
    static void Main()
    {
        string inputFileName = "day_19.in";

        if (!File.Exists(inputFileName))
        {
            Console.WriteLine($"Input file '{inputFileName}' not found.");
            return;
        }

        // Read all lines from the input file
        List<string> inputLines = new List<string>(File.ReadAllLines(inputFileName));

        if (inputLines.Count == 0)
        {
            Console.WriteLine(0);
            return;
        }

        // Find the index of the first blank line
        int firstBlank = inputLines.IndexOf(string.Empty);

        if (firstBlank == -1)
        {
            // No blank line found; assume all lines are patterns, no designs
            Console.WriteLine(0);
            return;
        }

        // Parse available towel patterns from the first line
        string patternsLine = inputLines[0];
        string[] patterns = patternsLine.Split(new char[] { ',' }, StringSplitOptions.RemoveEmptyEntries);
        HashSet<string> patternSet = new HashSet<string>();
        foreach (var p in patterns)
        {
            string trimmed = p.Trim();
            if (!string.IsNullOrEmpty(trimmed))
                patternSet.Add(trimmed);
        }

        // Read designs starting after the first blank line
        List<string> designs = new List<string>();
        for (int i = firstBlank + 1; i < inputLines.Count; i++)
        {
            string design = inputLines[i].Trim();
            if (!string.IsNullOrEmpty(design))
                designs.Add(design);
        }

        // For each design, check if it can be segmented
        int possibleCount = 0;
        foreach (var design in designs)
        {
            if (CanSegment(design, patternSet))
                possibleCount++;
        }

        // Output the result
        Console.WriteLine(possibleCount);
    }

    /// <summary>
    /// Determines if the string 's' can be segmented into one or more patterns from 'patterns'.
    /// </summary>
    /// <param name="s">The design string to check.</param>
    /// <param name="patterns">A set of available towel patterns.</param>
    /// <returns>True if 's' can be segmented; otherwise, false.</returns>
    static bool CanSegment(string s, HashSet<string> patterns)
    {
        int n = s.Length;
        bool[] dp = new bool[n + 1];
        dp[0] = true; // Empty string can always be formed

        for (int i = 1; i <= n; i++)
        {
            foreach (var pattern in patterns)
            {
                int len = pattern.Length;
                if (i >= len && s.Substring(i - len, len) == pattern)
                {
                    if (dp[i - len])
                    {
                        dp[i] = true;
                        break; // No need to check other patterns if one matches
                    }
                }
            }
        }

        return dp[n];
    }
}
