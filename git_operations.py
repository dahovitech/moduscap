import subprocess
import os

os.chdir('/workspace')

# Check if git repo exists
result = subprocess.run(['git', 'status'], capture_output=True, text=True)
print("=== GIT STATUS ===")
print(result.stdout)
if result.stderr:
    print("STDERR:", result.stderr)

# Check bin/console
result = subprocess.run(['ls', '-la', 'bin/'], capture_output=True, text=True)
print("\n=== BIN DIRECTORY ===")
print(result.stdout)
if result.stderr:
    print("STDERR:", result.stderr)

# Add files
result = subprocess.run(['git', 'add', 'bin/', '.gitignore'], capture_output=True, text=True)
print("\n=== GIT ADD ===")
print("Command executed successfully" if result.returncode == 0 else f"Error: {result.stderr}")

# Commit
result = subprocess.run(['git', 'commit', '-m', 'Add bin directory with Symfony console script'], capture_output=True, text=True)
print("\n=== GIT COMMIT ===")
print(result.stdout)
if result.stderr:
    print("STDERR:", result.stderr)

# Push
result = subprocess.run(['git', 'push', 'origin', 'main'], capture_output=True, text=True)
print("\n=== GIT PUSH ===")
print(result.stdout)
if result.stderr:
    print("STDERR:", result.stderr)
