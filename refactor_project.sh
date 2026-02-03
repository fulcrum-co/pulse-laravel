#!/bin/bash

# Agnostic Refactor Script
echo "🚀 Starting global project refactor..."

# 1. Rename Directories
# Note: Mac 'find' and 'rename' behave differently, so we use a safe loop
find . -type d -name "*Student*" | while read d; do 
    mv "$d" "${d//Student/Learner}" 2>/dev/null
done

# 2. Rename Files
find . -type f -name "*Student*" | while read f; do 
    mv "$f" "${f//Student/Learner}" 2>/dev/null
done

# 3. Global Text Replacement
# On Mac, 'sed -i' requires an empty string argument for the backup extension
find . -type f \( -name "*.php" -o -name "*.json" -o -name "*.md" \) -exec sed -i '' 's/Student/Learner/g' {} +
find . -type f \( -name "*.php" -o -name "*.json" -o -name "*.md" \) -exec sed -i '' 's/student/learner/g' {} +
find . -type f \( -name "*.php" -o -name "*.json" -o -name "*.md" \) -exec sed -i '' 's/School/Organization/g' {} +
find . -type f \( -name "*.php" -o -name "*.json" -o -name "*.md" \) -exec sed -i '' 's/school/organization/g' {} +

echo "✅ Filesystem refactor complete."
echo "⚠️  Reminder: Run 'composer dump-autoload' after this script."
