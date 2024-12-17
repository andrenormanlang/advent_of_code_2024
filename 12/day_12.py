from collections import deque

def parse_input(file_path):
    """
    Reads the input file and converts it into a 2D grid.
    """
    grid = []
    with open(file_path, 'r') as f:
        for line in f:
            stripped = line.strip()
            if stripped:
                grid.append(list(stripped))
    return grid

def get_neighbors(x, y, rows, cols):
    """
    Generates the four adjacent neighbors (up, down, left, right) of a cell.
    """
    directions = [(-1,0),(1,0),(0,-1),(0,1)]
    for dx, dy in directions:
        nx, ny = x + dx, y + dy
        if 0 <= nx < rows and 0 <= ny < cols:
            yield nx, ny

def bfs(grid, visited, start_x, start_y, plant_type):
    """
    Performs BFS to find all cells in the same region.
    """
    queue = deque()
    queue.append((start_x, start_y))
    visited[start_x][start_y] = True
    region_cells = []
    rows, cols = len(grid), len(grid[0])
    while queue:
        x, y = queue.popleft()
        region_cells.append((x, y))
        for nx, ny in get_neighbors(x, y, rows, cols):
            if not visited[nx][ny] and grid[nx][ny] == plant_type:
                visited[nx][ny] = True
                queue.append((nx, ny))
    return region_cells

def calculate_perimeter(grid, region_cells):
    """
    Calculates the perimeter of a region for Part 1.
    """
    perimeter = 0
    rows, cols = len(grid), len(grid[0])
    for x, y in region_cells:
        for dx, dy in [(-1,0),(1,0),(0,-1),(0,1)]:
            nx, ny = x + dx, y + dy
            if not (0 <= nx < rows and 0 <= ny < cols) or grid[nx][ny] != grid[x][y]:
                perimeter += 1
    return perimeter

def get_perimeter_cells(grid, region_cells):
    """
    Retrieves the set of perimeter cells for a region.
    """
    perimeter = set()
    rows, cols = len(grid), len(grid[0])
    region_set = set(region_cells)
    for x, y in region_cells:
        for dx, dy in [(-1,0),(1,0),(0,-1),(0,1)]:
            nx, ny = x + dx, y + dy
            if not (0 <= nx < rows and 0 <= ny < cols) or grid[nx][ny] != grid[x][y]:
                perimeter.add((x, y))
                break
    return perimeter

def calculate_number_of_sides(grid, region_cells):
    """
    Calculates the number of distinct straight fence sides for a region (Part 2).
    """
    region_set = set(region_cells)
    rows, cols = len(grid), len(grid[0])
    horizontal_sides = 0
    vertical_sides = 0

    # Count horizontal fence sides (top and bottom)
    for x in range(rows):
        in_run_top = False
        in_run_bottom = False
        for y in range(cols):
            if (x, y) in region_set:
                # Top side
                if x == 0 or grid[x-1][y] != grid[x][y]:
                    if not in_run_top:
                        horizontal_sides += 1
                        in_run_top = True
                else:
                    in_run_top = False
                # Bottom side
                if x == rows -1 or grid[x+1][y] != grid[x][y]:
                    if not in_run_bottom:
                        horizontal_sides += 1
                        in_run_bottom = True
                else:
                    in_run_bottom = False
            else:
                in_run_top = False
                in_run_bottom = False

    # Count vertical fence sides (left and right)
    for y in range(cols):
        in_run_left = False
        in_run_right = False
        for x in range(rows):
            if (x, y) in region_set:
                # Left side
                if y == 0 or grid[x][y-1] != grid[x][y]:
                    if not in_run_left:
                        vertical_sides += 1
                        in_run_left = True
                else:
                    in_run_left = False
                # Right side
                if y == cols -1 or grid[x][y+1] != grid[x][y]:
                    if not in_run_right:
                        vertical_sides += 1
                        in_run_right = True
                else:
                    in_run_right = False
            else:
                in_run_left = False
                in_run_right = False

    number_of_sides = horizontal_sides + vertical_sides
    return number_of_sides

def main():
    # Read the input file
    file_path = 'day_12.in'
    grid = parse_input(file_path)
    if not grid:
        print("Input file is empty or not properly formatted.")
        return

    rows, cols = len(grid), len(grid[0])
    visited = [[False for _ in range(cols)] for _ in range(rows)]

    total_price_part1 = 0
    total_price_part2 = 0

    for x in range(rows):
        for y in range(cols):
            if not visited[x][y]:
                plant_type = grid[x][y]
                region_cells = bfs(grid, visited, x, y, plant_type)
                area = len(region_cells)
                perimeter = calculate_perimeter(grid, region_cells)
                # For Part 2, calculate number of sides
                number_of_sides = calculate_number_of_sides(grid, region_cells)
                # Update total prices
                total_price_part1 += area * perimeter
                total_price_part2 += area * number_of_sides

    print(f"Total Price for Part 1: {total_price_part1}")
    print(f"Total Price for Part 2: {total_price_part2}")

if __name__ == "__main__":
    main()
