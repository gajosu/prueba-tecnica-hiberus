#!/bin/bash

set -e

echo "=========================================="
echo "  Complete Setup - Technical Test"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to check commands
check_command() {
    if ! command -v $1 &> /dev/null; then
        echo -e "${RED}Error: $1 is not installed.${NC}" >&2
        echo "Please install $1 before continuing."
        exit 1
    fi
}

# Check requirements
echo -e "${BLUE}[1/11] Checking requirements...${NC}"
check_command docker
check_command docker-compose

echo -e "${GREEN}✓ Docker and Docker Compose are installed${NC}"
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Error: Docker is not running.${NC}" >&2
    echo "Please start Docker before continuing."
    exit 1
fi

echo -e "${GREEN}✓ Docker is running${NC}"
echo ""

# Stop and clean existing containers
echo -e "${BLUE}[2/11] Cleaning existing containers...${NC}"
docker-compose down -v 2>/dev/null || true
echo -e "${GREEN}✓ Cleaned existing containers${NC}"
echo ""

# Create .env file if it doesn't exist
echo -e "${BLUE}[3/11] Configuring environment...${NC}"
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cat > .env << 'EOF'
APP_ENV=dev
APP_SECRET=$(openssl rand -hex 32)
DATABASE_URL="mysql://app:app@database:3306/app?serverVersion=8.0&charset=utf8mb4"
EOF
fi

# Generate APP_SECRET if not exists or empty
if ! grep -q "APP_SECRET=" .env || grep -q "APP_SECRET=$" .env || grep -q "APP_SECRET=\"\"" .env; then
    echo "Generating APP_SECRET..."
    APP_SECRET=$(openssl rand -hex 32)
    if grep -q "APP_SECRET=" .env; then
        sed -i.bak "s/APP_SECRET=.*/APP_SECRET=${APP_SECRET}/" .env && rm -f .env.bak
    else
        echo "APP_SECRET=${APP_SECRET}" >> .env
    fi
fi

echo -e "${GREEN}✓ Environment configured${NC}"
echo ""

# Create necessary directories
echo -e "${BLUE}[4/11] Creating directories...${NC}"
mkdir -p var/cache var/log var/coverage
echo -e "${GREEN}✓ Directories created${NC}"
echo ""

# Install Composer dependencies using Docker
echo -e "${BLUE}[5/11] Installing PHP dependencies with Composer...${NC}"
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json not found${NC}"
    exit 1
fi

docker run --rm -v $(pwd):/app -w /app composer:latest install --ignore-platform-reqs --no-interaction --prefer-dist
echo -e "${GREEN}✓ Composer dependencies installed${NC}"
echo ""

# Start database first
echo -e "${BLUE}[6/11] Starting database...${NC}"
docker-compose up -d database
echo "Waiting for MySQL to be ready..."
sleep 10

# Wait for MySQL to be ready
until docker-compose exec -T database mysqladmin ping -h localhost --silent 2>/dev/null; do
    echo "Waiting for MySQL..."
    sleep 2
done

echo -e "${GREEN}✓ MySQL is ready${NC}"
echo ""

# Start all containers
echo -e "${BLUE}[7/11] Starting all containers...${NC}"
docker-compose up -d
sleep 5
echo -e "${GREEN}✓ All containers started${NC}"
echo ""

# Install npm dependencies
echo -e "${BLUE}[8/11] Installing npm dependencies...${NC}"
if [ ! -f "package.json" ]; then
    echo -e "${YELLOW}⚠ package.json not found, creating a basic one...${NC}"
    docker-compose exec -T php npm init -y
    docker-compose exec -T php npm install --save-dev vite @vitejs/plugin-react react react-dom
    docker-compose exec -T php npm install --save-dev @types/react @types/react-dom
fi

docker-compose exec -T php npm install
echo -e "${GREEN}✓ npm dependencies installed${NC}"
echo ""

# Set permissions
echo -e "${BLUE}[9/11] Setting permissions...${NC}"
docker-compose exec -T php chmod -R 777 var/ 2>/dev/null || sudo chmod -R 777 var/
echo -e "${GREEN}✓ Permissions configured${NC}"
echo ""

# Run migrations
echo -e "${BLUE}[10/11] Running database migrations...${NC}"
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists --no-interaction || true
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction || echo -e "${YELLOW}⚠ No pending migrations${NC}"
echo -e "${GREEN}✓ Migrations executed${NC}"
echo ""

# Load fixtures (if they exist)
echo -e "${BLUE}[11/11] Loading fixtures and building assets...${NC}"
if docker-compose exec -T php test -d "src/DataFixtures" 2>/dev/null; then
    echo "Loading fixtures..."
    docker-compose exec -T php php bin/console doctrine:fixtures:load --no-interaction 2>/dev/null || echo -e "${YELLOW}⚠ No fixtures to load${NC}"
    echo -e "${GREEN}✓ Fixtures loaded${NC}"
else
    echo -e "${YELLOW}⚠ No fixtures directory found${NC}"
fi

# Clear cache
echo "Clearing cache..."
docker-compose exec -T php php bin/console cache:clear --no-warmup
docker-compose exec -T php php bin/console cache:warmup
echo -e "${GREEN}✓ Cache cleared${NC}"

# Build assets with Vite
if [ -f "vite.config.js" ]; then
    echo "Building assets with Vite..."
    docker-compose exec -T php npm run build || echo -e "${YELLOW}⚠ Error building assets (you can run 'make build' later)${NC}"
    echo -e "${GREEN}✓ Assets built${NC}"
fi
echo ""

# Show access information
echo "=========================================="
echo -e "${GREEN}  Setup completed successfully!${NC}"
echo "=========================================="
echo ""
echo "Access Information:"
echo "  - Symfony Application: http://localhost:8000"
echo "  - phpMyAdmin: http://localhost:8080"
echo "  - MySQL: localhost:3307 (host) / database:3306 (container)"
echo ""
echo "MySQL Credentials:"
echo "  - User: app"
echo "  - Password: app"
echo "  - Database: app"
echo ""
echo "Useful commands:"
echo "  - make up          : Start containers"
echo "  - make down        : Stop containers"
echo "  - make migrate     : Run migrations"
echo "  - make fixtures    : Load fixtures"
echo "  - make test        : Run tests"
echo "  - make dev         : Start Vite dev server"
echo "  - make build       : Build assets for production"
echo "  - make logs        : View Docker logs"
echo "  - make shell       : Open shell in PHP container"
echo ""
echo -e "${GREEN}Ready to develop!${NC}"

