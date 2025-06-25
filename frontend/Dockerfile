# Base image with Node.js
FROM node:22

# Set working directory
WORKDIR /app

# Copy package files separately to leverage Docker cache
COPY package.json package-lock.json* ./

# Copy entrypoint script and make it executable
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Copy all files
COPY . .

# Expose port used by Nuxt
EXPOSE 3000

# Run script on container start
ENTRYPOINT ["/entrypoint.sh"]