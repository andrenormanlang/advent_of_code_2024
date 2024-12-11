using System;
using System.Collections.Generic;
using System.IO;

namespace day_10
{
    /// <summary>
    /// Represents a position on the map with row and column indices.
    /// </summary>
    struct Position
    {
        public int Row;
        public int Col;

        public Position(int row, int col)
        {
            Row = row;
            Col = col;
        }

        /// <summary>
        /// Overrides Equals to compare positions.
        /// </summary>
        public override bool Equals(object obj)
        {
            if (!(obj is Position))
                return false;

            Position other = (Position)obj;
            return this.Row == other.Row && this.Col == other.Col;
        }

        /// <summary>
        /// Overrides GetHashCode to ensure Position can be used in hash-based collections.
        /// </summary>
        public override int GetHashCode()
        {
            return Row * 31 + Col;
        }
    }

    class Program
    {
        /// <summary>
        /// Reads the topographic map from the input file.
        /// </summary>
        /// <param name="filename">Name of the input file.</param>
        /// <returns>2D array representing the map.</returns>
        static int[,] ReadMap(string filename)
        {
            var lines = new List<string>();
            using (StreamReader sr = new StreamReader(filename))
            {
                string line;
                while((line = sr.ReadLine()) != null)
                {
                    line = line.Trim();
                    if (line.Length > 0)
                        lines.Add(line);
                }
            }

            if (lines.Count == 0)
                throw new Exception("Input file is empty.");

            int rows = lines.Count;
            int cols = lines[0].Length;

            // Ensure all lines have the same length
            foreach(var line in lines)
            {
                if (line.Length != cols)
                    throw new Exception("All lines in the input file must have the same number of digits.");
            }

            int[,] map = new int[rows, cols];
            for(int r = 0; r < rows; r++)
            {
                for(int c = 0; c < cols; c++)
                {
                    char ch = lines[r][c];
                    if (ch < '0' || ch > '9')
                        throw new Exception($"Invalid character '{ch}' in input file. Only digits 0-9 are allowed.");

                    map[r, c] = ch - '0';
                }
            }

            return map;
        }

        /// <summary>
        /// Identifies all trailheads (positions with height 0) in the map.
        /// </summary>
        /// <param name="map">2D array representing the map.</param>
        /// <returns>List of trailhead positions.</returns>
        static List<Position> GetTrailheads(int[,] map)
        {
            var trailheads = new List<Position>();
            int rows = map.GetLength(0);
            int cols = map.GetLength(1);

            for(int r = 0; r < rows; r++)
            {
                for(int c = 0; c < cols; c++)
                {
                    if(map[r, c] == 0)
                    {
                        trailheads.Add(new Position(r, c));
                    }
                }
            }

            return trailheads;
        }

        /// <summary>
        /// Gets all valid adjacent positions (up, down, left, right) for a given position.
        /// </summary>
        /// <param name="pos">Current position.</param>
        /// <param name="map">2D array representing the map.</param>
        /// <returns>List of adjacent positions.</returns>
        static List<Position> GetAdjacentPositions(Position pos, int[,] map)
        {
            var adjacents = new List<Position>();
            int rows = map.GetLength(0);
            int cols = map.GetLength(1);

            // Up
            if(pos.Row > 0)
                adjacents.Add(new Position(pos.Row - 1, pos.Col));
            // Down
            if(pos.Row < rows -1)
                adjacents.Add(new Position(pos.Row +1, pos.Col));
            // Left
            if(pos.Col >0)
                adjacents.Add(new Position(pos.Row, pos.Col -1));
            // Right
            if(pos.Col < cols -1)
                adjacents.Add(new Position(pos.Row, pos.Col +1));

            return adjacents;
        }

        /// <summary>
        /// Performs BFS from a trailhead to find all reachable 9s via valid hiking trails.
        /// </summary>
        /// <param name="map">2D array representing the map.</param>
        /// <param name="start">Trailhead position.</param>
        /// <returns>Number of unique 9s reachable from the trailhead.</returns>
        static int BFS_For_Score(int[,] map, Position start)
        {
            int rows = map.GetLength(0);
            int cols = map.GetLength(1);

            // Directions: up, down, left, right
            int[] dRows = new int[] { -1, 1, 0, 0 };
            int[] dCols = new int[] { 0, 0, -1, 1 };

            // Initialize visited array
            bool[,] visited = new bool[rows, cols];
            var queue = new Queue<Position>();

            // Start BFS from the trailhead
            queue.Enqueue(start);
            visited[start.Row, start.Col] = true;

            var reachableNines = new HashSet<Position>();

            while(queue.Count >0)
            {
                var currentPos = queue.Dequeue();

                foreach(var adj in GetAdjacentPositions(currentPos, map))
                {
                    if(!visited[adj.Row, adj.Col])
                    {
                        int adjHeight = map[adj.Row, adj.Col];
                        if(adjHeight == map[currentPos.Row, currentPos.Col] +1)
                        {
                            // Valid step
                            visited[adj.Row, adj.Col] = true;
                            queue.Enqueue(adj);

                            if(adjHeight ==9)
                            {
                                reachableNines.Add(adj);
                            }
                        }
                    }
                }
            }

            return reachableNines.Count;
        }

        /// <summary>
        /// Performs Dynamic Programming to calculate the number of distinct hiking trails from each position to any 9.
        /// </summary>
        /// <param name="map">2D array representing the map.</param>
        /// <returns>2D array where each cell contains the number of paths from that position to any 9.</returns>
        static long[,] CalculateTrailheadRatings(int[,] map)
        {
            int rows = map.GetLength(0);
            int cols = map.GetLength(1);
            long[,] pathCounts = new long[rows, cols];

            // Positions with height 9 have one path (the path itself)
            for(int r=0;r<rows;r++)
            {
                for(int c=0;c<cols;c++)
                {
                    if(map[r,c] ==9)
                    {
                        pathCounts[r,c] =1;
                    }
                }
            }

            // Process heights from 8 down to 0
            for(int h=8; h>=0; h--)
            {
                for(int r=0;r<rows;r++)
                {
                    for(int c=0;c<cols;c++)
                    {
                        if(map[r,c] ==h)
                        {
                            // For each adjacent position with height h+1, add its path counts
                            var adjacents = GetAdjacentPositions(new Position(r,c), map);
                            foreach(var adj in adjacents)
                            {
                                if(map[adj.Row, adj.Col] == h+1)
                                {
                                    pathCounts[r,c] += pathCounts[adj.Row, adj.Col];
                                }
                            }
                        }
                    }
                }
            }

            return pathCounts;
        }

        static void Main(string[] args)
        {
            string inputFile = "day_10.in";
            int[,] map;

            try
            {
                map = ReadMap(inputFile);
            }
            catch(Exception ex)
            {
                Console.WriteLine($"Error reading input: {ex.Message}");
                return;
            }

            var trailheads = GetTrailheads(map);

            if(trailheads.Count ==0)
            {
                Console.WriteLine("No trailheads found. Sum of scores is 0.");
                Console.WriteLine("Sum of ratings is 0.");
                return;
            }

            // Part One: Sum of trailhead scores
            long totalScore =0;

            foreach(var trailhead in trailheads)
            {
                int score = BFS_For_Score(map, trailhead);
                totalScore += score;
            }

            Console.WriteLine($"Part One - Sum of the scores of all trailheads: {totalScore}");

            // Part Two: Sum of trailhead ratings
            // Calculate path counts using Dynamic Programming
            long[,] pathCounts = CalculateTrailheadRatings(map);

            long totalRating =0;

            foreach(var trailhead in trailheads)
            {
                totalRating += pathCounts[trailhead.Row, trailhead.Col];
            }

            Console.WriteLine($"Part Two - Sum of the ratings of all trailheads: {totalRating}");
        }
    }
}
