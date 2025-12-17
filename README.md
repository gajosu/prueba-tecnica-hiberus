# Prueba Técnica Hiberus

Sistema básico de gestión de pedidos y pagos desarrollado con Symfony 7 y React.

## Requisitos

- Docker y Docker Compose
- Composer (opcional, se puede usar dentro del contenedor)
- Node.js 18+ (opcional, se puede usar dentro del contenedor)

## Instalación

### Opción 1: Setup Automatizado (Recomendado)

Ejecuta el script de setup que instalará todas las dependencias y configurará el entorno:

```bash
make setup
```

O directamente:

```bash
chmod +x setup.sh
./setup.sh
```

### Opción 2: Setup Manual

1. **Levantar contenedores Docker:**
   ```bash
   make up
   # o
   docker-compose up -d
   ```

2. **Instalar dependencias:**
   ```bash
   make install
   # o manualmente:
   composer install && npm install
   ```

3. **Configurar base de datos:**
   
   Asegúrate de que el archivo `.env` tenga la siguiente configuración:
   ```
   DATABASE_URL=postgresql://app:app@database:5432/app?serverVersion=16&charset=utf8
   ```
   
   **Nota:** El puerto externo de PostgreSQL es 5433 para evitar conflictos. Internamente en Docker usa el puerto 5432.

4. **Ejecutar migraciones:**
   ```bash
   make migrate
   # o
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

5. **Cargar fixtures (si existen):**
   ```bash
   make fixtures
   ```

## Comandos Disponibles

Usa `make help` para ver todos los comandos disponibles:

- `make setup` - Ejecutar setup completo
- `make up` - Levantar contenedores Docker
- `make down` - Detener contenedores Docker
- `make install` - Instalar dependencias (composer + npm)
- `make migrate` - Ejecutar migraciones
- `make fixtures` - Cargar fixtures
- `make test` - Ejecutar pruebas unitarias
- `make build` - Construir assets con Vite
- `make dev` - Iniciar servidor Vite en desarrollo
- `make clean` - Limpiar cache y logs
- `make logs` - Ver logs de Docker
- `make shell` - Abrir shell en contenedor PHP
- `make db-shell` - Abrir shell de PostgreSQL

## Estructura del Proyecto

```
prueba-tecnica-hiberus/
├── assets/              # React + Vite
├── config/              # Configuración Symfony
├── src/                 # Código fuente PHP
├── public/              # Punto de entrada web
├── docker/              # Configuración Docker
├── docker-compose.yml   # Servicios Docker
├── Makefile            # Comandos automatizados
└── setup.sh            # Script de setup
```

## Acceso a la Aplicación

- **Aplicación Symfony:** http://localhost:8000
- **PostgreSQL:** localhost:5433

### Credenciales PostgreSQL

- Usuario: `app`
- Contraseña: `app`
- Base de datos: `app`

## Desarrollo

### Backend (Symfony)

Para ejecutar comandos de Symfony:

```bash
# Desde la raíz del proyecto
php bin/console [comando]

# O desde el contenedor
docker-compose exec php php bin/console [comando]
```

### Frontend (React + Vite)

El frontend está en `assets/`. Para desarrollo:

```bash
make dev
# o
npm run dev
```

Esto iniciará el servidor de desarrollo de Vite en http://localhost:5173

Para producción:

```bash
make build
# o
npm run build
```

## Pruebas

El proyecto cuenta con una suite completa de tests dividida en:
- **Unit Tests**: Tests unitarios sin dependencias externas
- **Infrastructure Tests**: Tests de integración con base de datos

### Configurar Base de Datos de Test

Antes de ejecutar tests de infraestructura, crear la BD de test:

```bash
# Crear base de datos de test
make test-db-create

# Ejecutar migraciones en test
make test-db-migrate

# O resetear completamente (drop + create + migrate)
make test-db-reset
```

### Ejecutar Tests

```bash
# Ejecutar todos los tests
make test

# Ejecutar solo tests unitarios (rápidos, sin BD)
make test-unit

# Ejecutar solo tests de infraestructura (con BD)
make test-infrastructure

# Ejecutar con coverage
make test-coverage
```

### Estructura de Tests

```
tests/
├── Shared/
│   ├── UnitTestCase.php           # Clase base para tests unitarios
│   ├── InfrastructureTestCase.php # Clase base para tests con BD
│   └── Mother/                     # Object Mothers (datos fake)
│       ├── ProductMother.php
│       ├── OrderMother.php
│       ├── CustomerMother.php
│       └── ...
├── Unit/                           # Tests unitarios
│   ├── Product/
│   ├── Order/
│   └── Customer/
└── Infrastructure/                 # Tests de integración
    ├── Product/
    ├── Order/
    └── Customer/
```

### Object Mothers

El proyecto usa Object Mothers para generar datos de test:

```php
// Crear un producto aleatorio
$product = ProductMother::random();

// Crear un producto con datos específicos
$product = ProductMother::create(
    name: 'Laptop',
    stock: 10
);

// Usar métodos helper
$product = ProductMother::withoutStock();
$customer = CustomerMother::admin();
$order = OrderMother::withItems(3);
```

## Tecnologías Utilizadas

- **Backend:**
  - Symfony 7
  - PHP 8.2+
  - Doctrine ORM
  - PostgreSQL 16

- **Frontend:**
  - React 18
  - Vite 5
  - TypeScript (opcional)

- **Infraestructura:**
  - Docker & Docker Compose
  - PHP-CLI
  - PostgreSQL 16

## Notas

- El proyecto usa un monorepo donde Symfony y React están integrados
- Vite está configurado para servir los assets de React
- Las migraciones de Doctrine se ejecutan automáticamente en el setup
- El entorno de desarrollo está completamente containerizado

## API REST

La API está disponible en: `http://localhost:8777/api`

### Endpoints Disponibles

- **POST** `/api/login` - Autenticación simulada
- **GET** `/api/products` - Listar productos (con paginación y búsqueda)
- **POST** `/api/products` - Crear producto (Admin)
- **POST** `/api/orders` - Crear pedido
- **GET** `/api/orders/{id}` - Ver detalle de pedido
- **POST** `/api/orders/{id}/checkout` - Checkout (pago simulado)

### Documentación Completa

- Ver `docs/API.md` para documentación detallada de cada endpoint
- Importar `docs/Insomnia_Collection.json` en Postman/Insomnia para probar la API

### Ejemplos Rápidos

```bash
# Health check
curl http://localhost:8777/api/health

# Login
curl -X POST http://localhost:8777/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "customer1@example.com", "password": "password"}'

# Listar productos
curl "http://localhost:8777/api/products?page=1&limit=5"

# Crear pedido
curl -X POST http://localhost:8777/api/orders \
  -H "Content-Type: application/json" \
  -d '{"customerId": "customer-001", "items": [{"productId": "product-xxx", "quantity": 1}]}'
```

### Usuarios de Prueba

Ver detalles completos en `docs/CREDENTIALS.md`

- **Admin**: `admin@example.com` / `password`
- **Cliente 1**: `customer1@example.com` / `password`
- **Cliente 2**: `customer2@example.com` / `password`

## Pruebas

El proyecto cuenta con una suite completa de tests:

### Tipos de Pruebas

#### Tests Unitarios
Prueban la lógica de negocio de forma aislada sin dependencias externas:

```bash
make test-unit
```

#### Tests de Infraestructura
Prueban la integración con la base de datos (repositorios):

```bash
make test-infrastructure
```

#### Tests Funcionales (Feature Tests)
Prueban los endpoints de la API con requests HTTP reales:

```bash
make test-feature
```

#### Ejecutar Todas las Pruebas

```bash
make test
```

### Base de Datos de Test

Los tests de infraestructura y funcionales utilizan una base de datos separada (`app_test`). Para resetearla:

```bash
make test-db-reset
```

## Solución de Problemas

### Los contenedores no inician

```bash
docker-compose down
docker-compose up -d
```

### Error de permisos

```bash
chmod -R 777 var/
```

### Limpiar todo y empezar de nuevo

```bash
make reset
```

Esto detendrá los contenedores, eliminará los volúmenes, limpiará el cache y reinstalará todo.

## Licencia

Este proyecto es parte de una prueba técnica.

