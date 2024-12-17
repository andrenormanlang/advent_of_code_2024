using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;

namespace WarehouseWoes
{
    // Struct to represent a position in the warehouse
    struct Position
    {
        public int X { get; set; }
        public int Y { get; set; }

        public Position(int x, int y)
        {
            X = x;
            Y = y;
        }

        // Operator overloading for adding direction deltas
        public static Position operator +(Position a, (int dx, int dy) delta)
        {
            return new Position(a.X + delta.dx, a.Y + delta.dy);
        }

        // Override Equals and GetHashCode for correct HashSet functionality
        public override bool Equals(object obj)
        {
            if (!(obj is Position))
                return false;

            Position other = (Position)obj;
            return this.X == other.X && this.Y == other.Y;
        }

        public override int GetHashCode()
        {
            return (X, Y).GetHashCode();
        }
    }

    class Program
    {
        // Define movement directions
        static readonly Dictionary<char, (int dx, int dy)> Directions = new Dictionary<char, (int dx, int dy)>
        {
            { '^', (0, -1) }, // Up
            { 'v', (0, 1) },  // Down
            { '<', (-1, 0) }, // Left
            { '>', (1, 0) }    // Right
        };

        static void Main(string[] args)
        {
            // Path to the input file
            string inputFile = "day_15.in";

            if (!File.Exists(inputFile))
            {
                Console.WriteLine($"Input file '{inputFile}' not found.");
                return;
            }

            // Read all lines from the input file
            var allLines = File.ReadAllLines(inputFile).ToList();

            // Separate map and move sequence
            // Assume that a blank line separates the map and the moves
            int separatorIndex = allLines.FindIndex(line => string.IsNullOrWhiteSpace(line));

            List<string> originalMapLines;
            List<string> moveLines;

            if (separatorIndex == -1)
            {
                // No blank line found; assume all lines are map lines except the last lines with move characters
                // Find the first line that contains only move characters
                separatorIndex = allLines.FindIndex(line => line.All(c => Directions.ContainsKey(c)));
                if (separatorIndex == -1)
                {
                    // All lines are map lines; no moves
                    originalMapLines = allLines;
                    moveLines = new List<string>();
                }
                else
                {
                    originalMapLines = allLines.Take(separatorIndex).ToList();
                    moveLines = allLines.Skip(separatorIndex).ToList();
                }
            }
            else
            {
                // Split map and move lines at the separator
                originalMapLines = allLines.Take(separatorIndex).ToList();
                moveLines = allLines.Skip(separatorIndex + 1).ToList();
            }

            // Combine move lines into a single move sequence
            string moveSequence = string.Concat(moveLines.Where(line => !string.IsNullOrWhiteSpace(line)).Select(line => line.Trim()));

            // Part One: Original Map Processing
            long gpsSumPartOne = ProcessPartOne(originalMapLines, moveSequence);
            Console.WriteLine($"Part One GPS Sum: {gpsSumPartOne}");

            // Part Two: Scaled-Up Map Processing
            long gpsSumPartTwo = ProcessPartTwo(originalMapLines, moveSequence);
            Console.WriteLine($"Part Two GPS Sum: {gpsSumPartTwo}");
        }

        static long ProcessPartOne(List<string> mapLines, string moveSequence)
        {
            // Initialize sets for walls and boxes
            HashSet<Position> walls = new HashSet<Position>();
            HashSet<Position> boxes = new HashSet<Position>();
            Position robot = new Position(-1, -1);

            // Parse the original map
            for (int y = 0; y < mapLines.Count; y++)
            {
                string line = mapLines[y];
                for (int x = 0; x < line.Length; x++)
                {
                    char c = line[x];
                    switch (c)
                    {
                        case '#':
                            walls.Add(new Position(x, y));
                            break;
                        case 'O':
                            boxes.Add(new Position(x, y));
                            break;
                        case '@':
                            robot = new Position(x, y);
                            break;
                        // Ignore other characters (e.g., '.', etc.)
                    }
                }
            }

            if (robot.X == -1 && robot.Y == -1)
            {
                Console.WriteLine("Robot position '@' not found in the map for Part One.");
                return 0;
            }

            // Simulate the robot movements for Part One
            foreach (char move in moveSequence)
            {
                if (!Directions.ContainsKey(move))
                {
                    // Ignore invalid move characters
                    continue;
                }

                var (dx, dy) = Directions[move];
                Position targetPos = robot + (dx, dy);

                if (walls.Contains(targetPos))
                {
                    // Movement blocked by a wall; do nothing
                    continue;
                }
                else if (boxes.Contains(targetPos))
                {
                    // Attempt to push the box
                    Position newBoxPos = targetPos + (dx, dy);

                    if (walls.Contains(newBoxPos) || boxes.Contains(newBoxPos))
                    {
                        // Cannot push the box into a wall or another box; do nothing
                        continue;
                    }
                    else
                    {
                        // Push the box
                        boxes.Remove(targetPos);
                        boxes.Add(newBoxPos);
                        // Move the robot
                        robot = targetPos;
                    }
                }
                else
                {
                    // Move the robot to the target position
                    robot = targetPos;
                }
            }

            // Calculate the sum of all boxes' GPS coordinates for Part One
            long gpsSum = boxes.Select(box => 100L * box.Y + box.X).Sum();
            return gpsSum;
        }

        static long ProcessPartTwo(List<string> originalMapLines, string moveSequence)
        {
            // Scale up the map according to Part Two rules
            List<string> scaledMapLines = new List<string>();

            foreach (var line in originalMapLines)
            {
                string scaledLine = "";
                foreach (char c in line)
                {
                    switch (c)
                    {
                        case '#':
                            scaledLine += "##";
                            break;
                        case 'O':
                            scaledLine += "[]";
                            break;
                        case '.':
                            scaledLine += "..";
                            break;
                        case '@':
                            scaledLine += "@.";
                            break;
                        default:
                            // Handle unexpected characters by doubling them as empty spaces
                            scaledLine += "..";
                            break;
                    }
                }
                scaledMapLines.Add(scaledLine);
            }

            // Initialize sets for walls and boxes
            HashSet<Position> walls = new HashSet<Position>();
            HashSet<Position> boxes = new HashSet<Position>();
            Position robot = new Position(-1, -1);

            // Parse the scaled-up map
            for (int y = 0; y < scaledMapLines.Count; y++)
            {
                string line = scaledMapLines[y];
                for (int x = 0; x < line.Length; x += 2)
                {
                    if (x + 1 >= line.Length)
                    {
                        // Incomplete tile, skip
                        continue;
                    }

                    char c1 = line[x];
                    char c2 = line[x + 1];

                    if (c1 == '#' && c2 == '#')
                    {
                        walls.Add(new Position(x, y));
                        walls.Add(new Position(x + 1, y));
                    }
                    else if (c1 == '[' && c2 == ']')
                    {
                        boxes.Add(new Position(x, y));
                    }
                    else if (c1 == '.' && c2 == '.')
                    {
                        // Empty space, do nothing
                    }
                    else if (c1 == '@' && c2 == '.')
                    {
                        robot = new Position(x, y);
                    }
                    else
                    {
                        // Unexpected tile, treat as empty
                        // Alternatively, handle errors or other entities if necessary
                        // For now, we'll ignore them
                    }
                }
            }

            if (robot.X == -1 && robot.Y == -1)
            {
                Console.WriteLine("Robot position '@' not found in the scaled-up map for Part Two.");
                return 0;
            }

            // Simulate the robot movements for Part Two
            foreach (char move in moveSequence)
            {
                if (!Directions.ContainsKey(move))
                {
                    // Ignore invalid move characters
                    continue;
                }

                var (dx, dy) = Directions[move];
                Position targetPos = robot + (dx, dy);

                if (walls.Contains(targetPos))
                {
                    // Movement blocked by a wall; do nothing
                    continue;
                }
                else
                {
                    // Check if targetPos is part of any box
                    bool isBoxAtTarget = boxes.Contains(targetPos);
                    bool isBoxAtTargetRight = false;

                    // Since boxes are two units wide, check if the robot is trying to push the right part of a box
                    if (!isBoxAtTarget && (targetPos.X - 1) >= 0)
                    {
                        Position possibleLeftBox = new Position(targetPos.X - 1, targetPos.Y);
                        if (boxes.Contains(possibleLeftBox))
                        {
                            isBoxAtTargetRight = true;
                            targetPos = possibleLeftBox;
                        }
                    }

                    if (isBoxAtTarget || isBoxAtTargetRight)
                    {
                        // Collect all consecutive boxes in the movement direction
                        List<Position> boxesToPush = new List<Position>();
                        Position currentBox = targetPos;

                        while (boxes.Contains(currentBox))
                        {
                            boxesToPush.Add(currentBox);
                            currentBox = currentBox + (dx, dy);
                        }

                        // Check if all destination positions for the boxes are free
                        bool canPushAll = true;
                        foreach (var box in boxesToPush)
                        {
                            Position newBoxPosLeft = box + (dx, dy);
                            Position newBoxPosRight = new Position(newBoxPosLeft.X + 1, newBoxPosLeft.Y);

                            if (walls.Contains(newBoxPosLeft) || walls.Contains(newBoxPosRight) ||
                                boxes.Contains(newBoxPosLeft) || boxes.Contains(newBoxPosRight))
                            {
                                canPushAll = false;
                                break;
                            }
                        }

                        if (canPushAll)
                        {
                            // Push all boxes one step in the direction
                            foreach (var box in boxesToPush.AsEnumerable().Reverse())
                            {
                                boxes.Remove(box);
                                Position newBoxPosLeft = box + (dx, dy);
                                boxes.Add(newBoxPosLeft);
                            }

                            // Move the robot into the target position
                            robot = robot + (dx, dy);
                        }
                        else
                        {
                            // Cannot push boxes; do nothing
                            continue;
                        }
                    }
                    else
                    {
                        // Move the robot to the target position
                        robot = targetPos;
                    }
                }
            }

            // Calculate the sum of all boxes' GPS coordinates for Part Two
            // GPS = 100 * y + (x / 2)
            long gpsSum = boxes.Select(box => 100L * box.Y + (box.X / 2)).Sum();
            return gpsSum;
        }
    }
}
