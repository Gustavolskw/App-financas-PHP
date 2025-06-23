#!/bin/sh

# Check if node_modules is missing, then install dependencies
if [ ! -d "node_modules" ]; then
  echo "📦 Installing dependencies..."
  npm install
else
  echo "✅ Dependencies already installed."
fi

# Start Nuxt in dev mode
echo "🚀 Starting Nuxt..."
exec npm run dev
