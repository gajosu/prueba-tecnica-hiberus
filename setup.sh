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
echo -e "${BLUE}[2/12] Cleaning existing containers...${NC}"
docker-compose down -v 2>/dev/null || true
echo -e "${GREEN}✓ Cleaned existing containers${NC}"
echo ""

# Build Docker images
echo -e "${BLUE}[3/12] Building Docker images...${NC}"
docker-compose build --no-cache
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to build Docker images${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Docker images built${NC}"
echo ""

# Create .env file if it doesn't exist
echo -e "${BLUE}[4/12] Configuring environment...${NC}"
if [ ! -f ".env" ]; then
    echo "Creating .env file..."
    cat > .env << 'EOF'
APP_ENV=dev
APP_SECRET=$(openssl rand -hex 32)
DATABASE_URL="postgresql://app:app@database:5432/app?serverVersion=16&charset=utf8"
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
echo -e "${BLUE}[5/12] Creating directories...${NC}"
mkdir -p var/cache var/log var/coverage
echo -e "${GREEN}✓ Directories created${NC}"
echo ""

# Install Composer dependencies using Docker
echo -e "${BLUE}[6/12] Installing PHP dependencies with Composer...${NC}"
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json not found${NC}"
    exit 1
fi

docker run --rm -v $(pwd):/app -w /app composer:latest install --ignore-platform-reqs --no-interaction --prefer-dist
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to install Composer dependencies${NC}"
    exit 1
fi
echo -e "${GREEN}✓ Composer dependencies installed${NC}"
echo ""

# Start database first
echo -e "${BLUE}[7/12] Starting database...${NC}"
docker-compose up -d database
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to start database${NC}"
    exit 1
fi
echo "Waiting for PostgreSQL to be ready..."
sleep 10

# Wait for PostgreSQL to be ready
MAX_RETRIES=30
RETRY_COUNT=0
until docker-compose exec -T database pg_isready -U app -d app 2>/dev/null; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
        echo -e "${RED}Error: PostgreSQL failed to start after ${MAX_RETRIES} attempts${NC}"
        exit 1
    fi
    echo "Waiting for PostgreSQL... (attempt $RETRY_COUNT/$MAX_RETRIES)"
    sleep 2
done

echo -e "${GREEN}✓ PostgreSQL is ready${NC}"
echo ""

# Start all containers
echo -e "${BLUE}[8/12] Starting all containers...${NC}"
docker-compose up -d
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to start containers${NC}"
    exit 1
fi
sleep 5
echo -e "${GREEN}✓ All containers started${NC}"
echo ""

# Install npm dependencies
echo -e "${BLUE}[9/12] Installing npm dependencies...${NC}"
if [ ! -f "package.json" ]; then
    echo -e "${YELLOW}⚠ package.json not found, creating a basic one...${NC}"
    docker-compose exec -T php npm init -y
    docker-compose exec -T php npm install --save-dev vite @vitejs/plugin-react react react-dom
    docker-compose exec -T php npm install --save-dev @types/react @types/react-dom
fi

docker-compose exec -T php npm install
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to install npm dependencies${NC}"
    exit 1
fi
echo -e "${GREEN}✓ npm dependencies installed${NC}"
echo ""

# Set permissions
echo -e "${BLUE}[10/12] Setting permissions...${NC}"
docker-compose exec -T php chmod -R 777 var/ 2>/dev/null || sudo chmod -R 777 var/
echo -e "${GREEN}✓ Permissions configured${NC}"
echo ""

# Run migrations
echo -e "${BLUE}[11/12] Running database migrations...${NC}"
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists --no-interaction
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to create database${NC}"
    exit 1
fi

docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
if [ $? -ne 0 ]; then
    echo -e "${YELLOW}⚠ No pending migrations or migration failed${NC}"
fi
echo -e "${GREEN}✓ Migrations executed${NC}"
echo ""

# Load fixtures and build assets
echo -e "${BLUE}[12/12] Loading fixtures and building assets...${NC}"
if docker-compose exec -T php test -d "src/DataFixtures" 2>/dev/null; then
    echo "Loading fixtures..."
    docker-compose exec -T php php bin/console doctrine:fixtures:load --no-interaction 2>/dev/null || echo -e "${YELLOW}⚠ No fixtures to load${NC}"
    echo -e "${GREEN}✓ Fixtures loaded${NC}"
else
    echo -e "${YELLOW}⚠ No fixtures directory found${NC}"
fi

# Setup test database
echo ""
echo -e "${BLUE}Setting up test database...${NC}"
docker-compose exec -T php php bin/console doctrine:database:create --env=test --if-not-exists --no-interaction
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Test database created${NC}"
else
    echo -e "${YELLOW}⚠ Test database already exists or failed to create${NC}"
fi

echo "Running test migrations..."
docker-compose exec -T php php bin/console doctrine:migrations:migrate --env=test --no-interaction
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Test migrations executed${NC}"
else
    echo -e "${YELLOW}⚠ Test migrations failed or no pending migrations${NC}"
fi

echo "Loading test fixtures..."
docker-compose exec -T php php bin/console doctrine:fixtures:load --env=test --no-interaction 2>/dev/null
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ Test fixtures loaded${NC}"
else
    echo -e "${YELLOW}⚠ Test fixtures failed to load${NC}"
fi

# Generate JWT keys
echo "Generating JWT keys..."
docker-compose exec -T php php bin/console lexik:jwt:generate-keypair --skip-if-exists 2>/dev/null || echo -e "${YELLOW}⚠ JWT keys already exist or failed to generate${NC}"
echo -e "${GREEN}✓ JWT keys ready${NC}"

# Clear cache
echo "Clearing cache..."
docker-compose exec -T php php bin/console cache:clear --no-warmup
if [ $? -ne 0 ]; then
    echo -e "${RED}Error: Failed to clear cache${NC}"
    exit 1
fi
docker-compose exec -T php php bin/console cache:warmup
echo -e "${GREEN}✓ Cache cleared${NC}"

# Build assets with Vite
if [ -f "vite.config.js" ]; then
    echo "Building assets with Vite..."
    docker-compose exec -T php npm run build
    if [ $? -ne 0 ]; then
        echo -e "${YELLOW}⚠ Error building assets (you can run 'make build' later)${NC}"
    else
        echo -e "${GREEN}✓ Assets built${NC}"
    fi
fi
echo ""

# Show access information
APP_PORT=${APP_PORT:-8777}

echo "=========================================="
echo -e "${GREEN}  Setup completed successfully!${NC}"
echo "=========================================="
echo ""
echo "Access Information:"
echo "  - Symfony Application: http://localhost:${APP_PORT}"
echo "  - PostgreSQL: localhost:5433 (host) / database:5432 (container)"
echo ""
echo "PostgreSQL Credentials:"
echo "  - User: app"
echo "  - Password: app"
echo "  - Database: app"
echo ""
echo "Useful commands:"
echo "  - make up             : Start containers"
echo "  - make down           : Stop containers"
echo "  - make migrate        : Run migrations"
echo "  - make fixtures       : Load fixtures"
echo "  - make test           : Run all tests"
echo "  - make test-unit      : Run unit tests"
echo "  - make test-feature   : Run feature tests"
echo "  - make test-db-reset  : Reset test database"
echo "  - make dev            : Start Vite dev server"
echo "  - make build          : Build assets for production"
echo "  - make logs           : View Docker logs"
echo "  - make shell          : Open shell in PHP container"
echo ""
echo "Access:"
echo "  - Application: http://localhost:${APP_PORT}"
echo "  - API Docs:    http://localhost:${APP_PORT}/api/doc"
echo ""
echo "Test Credentials:"
echo "  - Admin: admin@example.com / password"
echo "  - User:  customer1@example.com / password"
echo ""
echo -e "${GREEN}Ready to develop!${NC}"

