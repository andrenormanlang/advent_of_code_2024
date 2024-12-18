from collections import deque

def read_byte_positions(file_path):
    """
    Reads byte positions from the given file.
    Each line in the file should be in the format: x,y
    """
    byte_positions = []
    with open(file_path, 'r') as file:
        for line in file:
            stripped_line = line.strip()
            if stripped_line:  # Ensure the line is not empty
                try:
                    x, y = map(int, stripped_line.split(','))
                    byte_positions.append((x, y))
                except ValueError:
                    print(f"Invalid line format: {stripped_line}")
    return byte_positions

def is_path_blocked(grid, start, end):
    """
    Uses BFS to determine if a path exists from start to end.
    Returns True if no path exists, False otherwise.
    """
    rows, cols = len(grid), len(grid[0])
    queue = deque([start])
    visited = set([start])

    # Define possible movements: Up, Down, Left, Right
    directions = [(-1,0), (1,0), (0,-1), (0,1)]

    while queue:
        current = queue.popleft()
        if current == end:
            return False  # Path exists

        for dx, dy in directions:
            nx, ny = current[0] + dx, current[1] + dy
            if 0 <= nx < rows and 0 <= ny < cols:
                if grid[nx][ny] == '.' and (nx, ny) not in visited:
                    visited.add((nx, ny))
                    queue.append((nx, ny))
    return True  # No path exists

def find_blocking_byte(byte_positions, grid_size=(71,71)):
    """
    Iterates through byte positions, corrupts the grid, and checks for path blockage.
    Returns the coordinates of the first byte that blocks the path.
    """
    rows, cols = grid_size
    grid = [['.' for _ in range(cols)] for _ in range(rows)]
    start, end = (0,0), (rows-1, cols-1)

    # Ensure the start and end are not corrupted initially
    grid[start[0]][start[1]] = '.'
    grid[end[0]][end[1]] = '.'

    for index, byte in enumerate(byte_positions):
        x, y = byte
        if (x, y) == start or (x, y) == end:
            # Skip corrupting start or end positions
            continue
        if 0 <= x < rows and 0 <= y < cols:
            grid[x][y] = '#'  # Corrupt the cell
        else:
            print(f"Byte position out of bounds: {x},{y}")
            continue

        # Check if the path is blocked after this byte
        if is_path_blocked(grid, start, end):
            return f"{x},{y}"  # Return the first blocking byte coordinates

    return "Path remains open after all bytes."

def main():
    input_file = 'day_18.in'  # Ensure this file is in the same directory as the script
    byte_positions = read_byte_positions(input_file)
    blocking_byte = find_blocking_byte(byte_positions, grid_size=(71,71))
    print(blocking_byte)

if __name__ == "__main__":
    main()
