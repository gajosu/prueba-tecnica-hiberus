# Prueba Técnica Hiberus

Sistema básico de gestión de pedidos y pagos desarrollado con Symfony 7 y React con arquitectura hexagonal.

## 📑 Índice de Contenidos

- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Comandos Disponibles](#comandos-disponibles)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Acceso a la Aplicación](#acceso-a-la-aplicación)
- [Desarrollo](#desarrollo)
- [Pruebas](#pruebas)
- [API REST](#api-rest)
- [Autenticación y Seguridad](#autenticación-y-seguridad)
- [Roles y Permisos](#roles-y-permisos)
- [Arquitectura](#arquitectura)
- [Tecnologías Utilizadas](#tecnologías-utilizadas)
- [Solución de Problemas](#solución-de-problemas)

---

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

## 🚀 Inicio Rápido

Para levantar la aplicación completa (Backend + Frontend):

```bash
# 1. Levantar contenedores y configurar BD
make setup

# 2. En una terminal, el backend ya está corriendo en el puerto 8777
# 3. En otra terminal, iniciar el servidor de desarrollo de Vite
make dev
# o
npm run dev
```

Luego accede a **http://localhost:8777** en tu navegador.

## Acceso a la Aplicación

- **Aplicación Web (Frontend + Backend):** http://localhost:8777
- **Servidor Vite (Desarrollo):** http://localhost:5173 (usado automáticamente por el frontend)
- **API REST:** http://localhost:8777/api
- **Documentación API (Swagger UI):** http://localhost:8777/api/doc
- **PostgreSQL:** localhost:5433

### Credenciales de Acceso

**Usuarios de prueba:**
- **Admin:** admin@example.com / password
- **Usuario:** customer1@example.com / password
- **Usuario:** customer2@example.com / password

**PostgreSQL:**
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

El frontend está en `assets/` y usa **React 18**, **React Router**, **TailwindCSS** y **shadcn/ui**.

#### Desarrollo

Para trabajar con Hot Module Replacement (HMR), necesitas tener **dos terminales**:

**Terminal 1 - Backend (Symfony):**
```bash
make up  # Los contenedores ya están corriendo
```

**Terminal 2 - Frontend (Vite):**
```bash
make dev
# o
docker-compose exec php npm run dev
```

Esto iniciará el servidor de desarrollo de Vite en http://localhost:5173

⚠️ **Importante:** En desarrollo, debes tener el servidor de Vite corriendo para que el HMR funcione. Si no lo tienes corriendo, los assets se servirán desde `public/build/` (versión de producción).

Luego accede a **http://localhost:8777** (no al puerto 5173, ese es solo para Vite internamente)

#### Producción

Para construir los assets para producción:

```bash
make build
# o
npm run build
```

Los archivos construidos se generan en `public/build/`

#### Estructura del Frontend

```
assets/
├── components/
│   ├── ui/              # Componentes shadcn/ui (Button, Card, Input, etc.)
│   ├── Layout.jsx       # Layout principal con navegación
│   └── ProtectedRoute.jsx  # Protección de rutas autenticadas
├── context/
│   ├── AuthContext.jsx  # Context para autenticación JWT
│   └── CartContext.jsx  # Context para carrito de compras
├── pages/
│   ├── LoginPage.jsx    # Página de inicio de sesión
│   ├── CatalogPage.jsx  # Catálogo de productos con búsqueda y paginación
│   ├── CartPage.jsx     # Carrito de compras
│   └── OrderDetailPage.jsx  # Detalle de pedido con checkout
├── lib/
│   ├── api.js          # Cliente API con axios
│   └── utils.js        # Utilidades (cn para clsx + tailwind-merge)
├── styles/
│   └── app.css         # Estilos globales con TailwindCSS
└── app.jsx             # Punto de entrada con React Router
```

#### Características del Frontend

- **Autenticación JWT**: Login con email y contraseña, token almacenado en localStorage
- **Gestión de estado**: Contexts de React para Auth y Cart
- **Rutas protegidas**: Solo usuarios autenticados pueden acceder al catálogo y carrito
- **Carrito persistente**: El carrito se guarda en localStorage
- **UI moderna**: Componentes de shadcn/ui con TailwindCSS
- **Responsive**: Diseño adaptable a diferentes dispositivos

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
  - React Router 6
  - Vite 5
  - TailwindCSS 3.4
  - shadcn/ui (componentes UI)
  - Axios (cliente HTTP)
  - Lucide React (iconos)
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

### 📚 Documentación OpenAPI (Swagger)

**Accede a la documentación interactiva de la API:**

👉 **http://localhost:8777/api/doc**

La documentación incluye:
- ✅ Especificación completa de todos los endpoints
- ✅ Schemas de request y response bodies
- ✅ Autenticación JWT integrada
- ✅ Probador interactivo (try it out)
- ✅ Ejemplos de requests y responses

También puedes obtener el JSON de OpenAPI en: **http://localhost:8777/api/doc/openapi**

### Endpoints Disponibles

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| **POST** | `/api/login` | Autenticación de usuario | No |
| **GET** | `/api/products` | Listar productos (paginado) | No |
| **POST** | `/api/products` | Crear producto | Admin |
| **POST** | `/api/orders` | Crear pedido | User |
| **GET** | `/api/orders/{id}` | Ver detalle de pedido | User |
| **POST** | `/api/orders/{id}/checkout` | Procesar pago (simulado) | User |

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

### Cobertura de Tests

El proyecto incluye **71 tests con 794 assertions** que cubren:
- **Tests Unitarios**: Lógica de negocio (Handlers, Commands, Queries)
- **Tests de Infraestructura**: Repositorios y persistencia con Doctrine
- **Tests Funcionales**: Endpoints de la API con requests HTTP reales

Todas las pruebas utilizan **Object Mothers** con FakerPHP para generar datos de prueba consistentes.

## Autenticación y Seguridad

### 🔐 JWT (JSON Web Tokens)

El sistema utiliza **JWT real** (no simulado) con `lexik/jwt-authentication-bundle`:

**Flujo de autenticación:**
1. Usuario hace login con email/password en `/api/login`
2. Sistema valida credenciales y genera un JWT token
3. Cliente incluye el token en el header: `Authorization: Bearer {token}`
4. Sistema valida el token en cada request protegido

**Configuración:**
- Claves RSA en `config/jwt/`
- Tiempo de vida del token: 1 hora (configurable)
- Password hashing con `bcrypt`

### 🛡️ Guards y Middleware

Similar a los guards/middleware de Laravel, implementamos:

**Guards personalizados:**
- `AuthGuard`: Verifica que el usuario esté autenticado
- `AdminGuard`: Verifica que el usuario tenga rol de administrador

**Atributos PHP 8:**
```php
#[RequiresAuth]  // Requiere autenticación
#[RequiresRole('ROLE_ADMIN')]  // Requiere rol específico
```

**Event Listener:**
`SecurityAttributeListener` intercepta requests y valida los atributos de seguridad antes de ejecutar los controladores.

**Servicio CurrentUser:**
```php
$this->currentUser->id();      // ID del usuario autenticado
$this->currentUser->email();   // Email
$this->currentUser->isAdmin(); // Verificar si es admin
```

## Roles y Permisos

### 📊 Jerarquía de Roles

El sistema tiene **solo 2 roles** con herencia automática:

```
ROLE_ADMIN (Administrador)
    │
    └─> hereda ──> ROLE_USER (Usuario normal)
```

**Configuración en** `config/packages/security.yaml`:
```yaml
role_hierarchy:
    ROLE_ADMIN: ROLE_USER
```

### 🎯 Permisos por Rol

| Rol | Permisos |
|-----|----------|
| `ROLE_ADMIN` | ✅ Todos los endpoints (crear productos + endpoints de usuario) |
| `ROLE_USER` | ✅ Solo endpoints de usuario (crear/ver pedidos) |

### 👤 Usuarios de Prueba

| Email | Password | Rol |
|-------|----------|-----|
| `admin@example.com` | `password` | `ROLE_ADMIN` |
| `customer1@example.com` | `password` | `ROLE_USER` |
| `customer2@example.com` | `password` | `ROLE_USER` |

## Arquitectura

### 🏗️ Diseño Hexagonal (Ports & Adapters)

El proyecto sigue una arquitectura hexagonal con vertical slicing por bounded context:

```
src/
├── Product/              # Bounded Context: Productos
│   ├── Application/      # Casos de uso (Commands, Queries, Handlers)
│   ├── Domain/          # Lógica de negocio (Entities, Value Objects)
│   └── Infrastructure/  # Adaptadores (Controllers, Repositories, DTOs)
├── Order/               # Bounded Context: Pedidos
├── Customer/            # Bounded Context: Clientes
└── Shared/              # Código compartido entre contextos
```

### 🎯 Principios Aplicados

- **DDD (Domain-Driven Design)**: Bounded Contexts, Entities, Value Objects
- **CQRS**: Separación de Commands y Queries
- **Repository Pattern**: Interfaces para abstracción de persistencia
- **Dependency Inversion**: Dependencias apuntan hacia el dominio
- **SOLID Principles**: Código mantenible y testeable

### 📦 Value Objects

- `Money`: Encapsula precio y moneda
- `OrderStatus`: Estados del pedido (pending, paid, cancelled)
- Custom UUID generation service desacoplado

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

