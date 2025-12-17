#!/bin/bash

set -e

echo "=========================================="
echo "  Setup - Prueba Técnica Hiberus"
echo "=========================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para verificar comandos
check_command() {
    if ! command -v $1 &> /dev/null; then
        echo -e "${RED}Error: $1 no está instalado.${NC}" >&2
        echo "Por favor instala $1 antes de continuar."
        exit 1
    fi
}

# Verificar requisitos
echo "Verificando requisitos..."
check_command docker
check_command docker-compose

echo -e "${GREEN}✓ Docker y Docker Compose están instalados${NC}"
echo ""

# Verificar si Docker está corriendo
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}Error: Docker no está corriendo.${NC}" >&2
    echo "Por favor inicia Docker antes de continuar."
    exit 1
fi

echo -e "${GREEN}✓ Docker está corriendo${NC}"
echo ""

# Levantar contenedores
echo "Levantando contenedores Docker..."
docker-compose up -d

echo "Esperando a que MySQL esté listo..."
sleep 10

# Verificar que MySQL esté listo
until docker-compose exec -T database mysqladmin ping -h localhost --silent; do
    echo "Esperando a MySQL..."
    sleep 2
done

echo -e "${GREEN}✓ MySQL está listo${NC}"
echo ""

# Instalar dependencias de Composer
echo "Instalando dependencias de Composer..."
if [ ! -f "composer.json" ]; then
    echo -e "${RED}Error: composer.json no encontrado${NC}"
    exit 1
fi

composer install --no-interaction
echo -e "${GREEN}✓ Dependencias de Composer instaladas${NC}"
echo ""

# Instalar dependencias de npm
echo "Instalando dependencias de npm..."
if [ ! -f "package.json" ]; then
    echo -e "${YELLOW}⚠ package.json no encontrado, creando uno básico...${NC}"
    npm init -y
    npm install --save-dev vite @vitejs/plugin-react react react-dom
    npm install --save-dev @types/react @types/react-dom
fi

npm install
echo -e "${GREEN}✓ Dependencias de npm instaladas${NC}"
echo ""

# Ejecutar migraciones
echo "Ejecutando migraciones de base de datos..."
php bin/console doctrine:migrations:migrate --no-interaction || echo -e "${YELLOW}⚠ No hay migraciones pendientes${NC}"
echo -e "${GREEN}✓ Migraciones ejecutadas${NC}"
echo ""

# Cargar fixtures (si existen)
if [ -d "src/DataFixtures" ] && [ "$(ls -A src/DataFixtures 2>/dev/null)" ]; then
    echo "Cargando fixtures..."
    php bin/console doctrine:fixtures:load --no-interaction || echo -e "${YELLOW}⚠ No se pudieron cargar fixtures${NC}"
    echo -e "${GREEN}✓ Fixtures cargadas${NC}"
else
    echo -e "${YELLOW}⚠ No hay fixtures para cargar${NC}"
fi
echo ""

# Configurar permisos
echo "Configurando permisos..."
chmod -R 777 var/
echo -e "${GREEN}✓ Permisos configurados${NC}"
echo ""

# Limpiar cache
echo "Limpiando cache..."
php bin/console cache:clear
echo -e "${GREEN}✓ Cache limpiado${NC}"
echo ""

# Construir assets (si es necesario)
if [ -f "vite.config.js" ]; then
    echo "Construyendo assets con Vite..."
    npm run build || echo -e "${YELLOW}⚠ Error al construir assets (puede ejecutarse después con 'make build')${NC}"
    echo -e "${GREEN}✓ Assets construidos${NC}"
    echo ""
fi

# Mostrar información de acceso
echo "=========================================="
echo -e "${GREEN}  Setup completado exitosamente!${NC}"
echo "=========================================="
echo ""
echo "Información de acceso:"
echo "  - Aplicación Symfony: http://localhost:8000"
echo "  - phpMyAdmin: http://localhost:8080"
echo "  - MySQL: localhost:3306"
echo ""
echo "Credenciales MySQL:"
echo "  - Usuario: app"
echo "  - Contraseña: app"
echo "  - Base de datos: app"
echo ""
echo "Comandos útiles:"
echo "  - make up          : Levantar contenedores"
echo "  - make down        : Detener contenedores"
echo "  - make migrate     : Ejecutar migraciones"
echo "  - make fixtures    : Cargar fixtures"
echo "  - make test        : Ejecutar pruebas"
echo "  - make dev         : Iniciar servidor Vite en desarrollo"
echo "  - make build       : Construir assets para producción"
echo "  - make logs        : Ver logs de Docker"
echo ""
echo -e "${GREEN}¡Listo para desarrollar!${NC}"

