#!/bin/bash

# Pulse Laravel - Setup Stubs Script
# Run this after Laravel is installed to copy all custom files into place

echo "🚀 Setting up Pulse Laravel application..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan not found. Run this from the Laravel root directory."
    exit 1
fi

# Check if stubs directory exists
if [ ! -d "stubs" ]; then
    echo "❌ Error: stubs directory not found."
    exit 1
fi

echo "📁 Copying model files..."
cp -r stubs/app/Models/* app/Models/ 2>/dev/null || mkdir -p app/Models && cp -r stubs/app/Models/* app/Models/

echo "📁 Copying service files..."
mkdir -p app/Services
cp -r stubs/app/Services/* app/Services/

echo "📁 Copying middleware files..."
cp -r stubs/app/Http/Middleware/* app/Http/Middleware/

echo "📁 Copying configuration files..."
cp stubs/config/services.php config/services.php
cp stubs/config/pulse.php config/pulse.php

echo "📝 Registering middleware in bootstrap/app.php..."
# Note: In Laravel 11, middleware is registered differently
# This is a placeholder - manual registration may be needed

echo "✅ Stubs copied successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Register middleware in bootstrap/app.php"
echo "2. Run: php artisan config:clear"
echo "3. Update config/database.php for MongoDB"
echo "4. Run: php artisan migrate (if using any SQL tables)"
echo ""
echo "🎉 Setup complete!"
