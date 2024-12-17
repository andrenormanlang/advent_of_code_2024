from collections import Counter

# Read the puzzle input from the file
with open('day_1.in', 'r') as file:
    puzzle_input = file.read()

# Step 1: Parse the input into two separate lists
left_list = []
right_list = []

# Split the input into lines and process each line
for line in puzzle_input.strip().split('\n'):
    parts = line.strip().split()
    if len(parts) >= 2:
        left, right = parts[0], parts[1]
        left_list.append(int(left))
        right_list.append(int(right))

# Step 2: Sort both lists
left_sorted = sorted(left_list)
right_sorted = sorted(right_list)

# Step 3: Pair the numbers and calculate the total distance
total_distance = 0
for left, right in zip(left_sorted, right_sorted):
    distance = abs(left - right)
    total_distance += distance

# Step 4: Output the total distance
print(f"The total distance between the two lists is: {total_distance}")

# Step 5: Count the occurrences of each number in the right list
right_counter = Counter(right_list)

# Step 6: Calculate the total similarity score
similarity_score = 0
for number in left_list:
    count = right_counter.get(number, 0)
    similarity_score += number * count

# Step 7: Output the similarity score
print(f"The similarity score between the two lists is: {similarity_score}")
