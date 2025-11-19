#!/bin/bash
cd /workspace

echo "=== CHECKING GIT REPO ==="
if [ -d .git ]; then
    echo "Git repository exists"
else
    echo "No git repository found"
    exit 1
fi

echo ""
echo "=== CHECKING BIN DIRECTORY ==="
if [ -d bin ]; then
    echo "bin/ directory exists"
    ls -la bin/
else
    echo "bin/ directory does not exist"
fi

echo ""
echo "=== GIT STATUS BEFORE ==="
git status --short

echo ""
echo "=== ADDING FILES TO GIT ==="
git add bin/ .gitignore

echo ""
echo "=== GIT STATUS AFTER ADD ==="
git status --short

echo ""
echo "=== COMMITTING ==="
git commit -m "Add bin directory with Symfony console script"

echo ""
echo "=== PUSHING TO ORIGIN ==="
git push origin main

echo ""
echo "=== DONE ==="
