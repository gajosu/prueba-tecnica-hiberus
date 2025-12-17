# Prueba Técnica Hiberus

E-commerce completo con **Symfony 7** (backend) y **React 18** (frontend), siguiendo **Arquitectura Hexagonal** y **Domain-Driven Design**.

## 📑 Índice

- [🚀 Inicio Rápido](#-inicio-rápido)
- [🏗️ Arquitectura](#️-arquitectura)
- [💻 Frontend](#-frontend)
- [🔐 Autenticación y Seguridad](#-autenticación-y-seguridad)
- [📚 API REST](#-api-rest)
- [🧪 Testing](#-testing)
- [⚙️ Comandos y Desarrollo](#️-comandos-y-desarrollo)
- [🛠️ Solución de Problemas](#️-solución-de-problemas)

---

## 🚀 Inicio Rápido

### Requisitos

- Docker y Docker Compose
- Composer (opcional, se puede usar dentro del contenedor)
- Node.js 18+ (opcional, se puede usar dentro del contenedor)

### Opción 1: Instalación Automática (Recomendado)

```bash
make setup
```

O directamente:

```bash
chmod +x setup.sh
./setup.sh
```

**Este comando configura:**
- ✅ Contenedores Docker (PHP 8.2 + PostgreSQL 16)
- ✅ Dependencias (Composer + NPM)
- ✅ Base de datos (desarrollo + test) con migraciones y fixtures
- ✅ Claves JWT para autenticación
- ✅ Frontend compilado y listo

### Opción 2: Instalación Manual

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
   docker-compose exec php composer install
   docker-compose exec php npm install
   ```

3. **Configurar base de datos:**
   
   Asegúrate de que el archivo `.env` tenga la siguiente configuración:
   ```
   DATABASE_URL=postgresql://app:app@database:5432/app?serverVersion=16&charset=utf8
   ```
   
   **Nota:** El puerto externo de PostgreSQL es 5433 para evitar conflictos. Internamente en Docker usa el puerto 5432.

4. **Generar claves JWT:**
   ```bash
   make jwt-keys
   # o
   docker-compose exec php php bin/console lexik:jwt:generate-keypair
   ```

5. **Ejecutar migraciones (desarrollo):**
   ```bash
   make migrate
   # o
   docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
   ```

6. **Cargar fixtures (desarrollo):**
   ```bash
   make fixtures
   # o
   docker-compose exec php php bin/console doctrine:fixtures:load --no-interaction
   ```

7. **Configurar base de datos de test:**
   ```bash
   # Crear base de datos de test
   docker-compose exec php php bin/console doctrine:database:create --env=test --if-not-exists
   
   # Ejecutar migraciones en test
   docker-compose exec php php bin/console doctrine:migrations:migrate --env=test --no-interaction
   
   # Cargar fixtures en test
   docker-compose exec php php bin/console doctrine:fixtures:load --env=test --no-interaction
   
   # O usar el comando todo-en-uno:
   make test-db-reset
   ```

8. **Construir assets del frontend:**
   ```bash
   make build
   # o
   docker-compose exec php npm run build
   ```

### Acceso a la Aplicación

- **Aplicación Web:** http://localhost:8777
- **API REST:** http://localhost:8777/api
- **API Docs (Swagger):** http://localhost:8777/api/doc
- **Vite Dev Server:** http://localhost:5173 (HMR para desarrollo)
- **PostgreSQL:** localhost:5433

### Credenciales de Prueba

| Usuario | Email | Password | Rol |
|---------|-------|----------|-----|
| Admin | `admin@example.com` | `password` | Crear productos + acceso completo |
| Usuario 1 | `customer1@example.com` | `password` | Comprar productos |
| Usuario 2 | `customer2@example.com` | `password` | Comprar productos |

---

## 🏗️ Arquitectura

### Diseño Hexagonal (Ports & Adapters) + DDD

El proyecto sigue **Arquitectura Hexagonal** con **vertical slicing por bounded context**:

```
src/
├── Product/              # Bounded Context: Productos
│   ├── Application/      # 📋 Use Cases (Commands, Queries, Handlers)
│   │   ├── CreateProduct/
│   │   └── ListProducts/
│   ├── Domain/          # 💎 Business Logic (Entities, Value Objects)
│   │   ├── Entity/Product.php
│   │   ├── Repository/ProductRepository.php (interface)
│   │   └── Exception/
│   └── Infrastructure/  # 🔌 Adapters (HTTP, DB, DTOs)
│       ├── Controller/
│       ├── Persistence/DoctrineProductRepository.php
│       └── Http/        # DTOs de request/response
│
├── Order/               # Bounded Context: Pedidos
│   ├── Application/     # CreateOrder, CheckoutOrder, GetOrderDetail
│   ├── Domain/         # Order entity, OrderItem, OrderStatus VO
│   └── Infrastructure/ # Controllers, Doctrine repositories
│
├── Customer/            # Bounded Context: Clientes/Auth
│   ├── Application/     # Login, Register
│   ├── Domain/         # Customer entity (UserInterface)
│   └── Infrastructure/ # LoginController, DoctrineCustomerRepository
│
└── Shared/              # Código compartido
    ├── Domain/         # UuidGenerator, Money VO
    └── Infrastructure/ # Security (JWT, Guards), Exception handling
```

### Principios Aplicados

- ✅ **DDD**: Bounded Contexts, Entities, Value Objects (`Money`, `OrderStatus`)
- ✅ **CQRS**: Separación Commands/Queries con Handlers
- ✅ **Repository Pattern**: Interfaces en Domain, implementaciones en Infrastructure
- ✅ **Dependency Inversion**: Domain no depende de Infrastructure
- ✅ **SOLID**: Single Responsibility, Open/Closed, Dependency Inversion
- ✅ **Inmutabilidad**: Value Objects inmutables, DTOs readonly

### Flujo de una Request

```
HTTP Request
    ↓
Controller (Infrastructure)
    ↓
DTO Validation
    ↓
Command/Query Creation
    ↓
Handler (Application) ← usa → Repository Interface (Domain)
    ↓                              ↓
Domain Logic              Repository Impl (Infrastructure)
    ↓                              ↓
Response DTO              Doctrine/PostgreSQL
    ↓
JSON Response
```

### Gestión de Stock

**Validación en Checkout (no en creación):**
1. Usuario crea orden → Se guarda sin validar stock
2. Usuario hace checkout → Se valida stock disponible
3. Si hay stock → Se procesa pago y se reduce stock
4. Si no hay stock → Error `400 Bad Request` con mensaje claro

Esto permite:
- Carritos que no bloquean stock
- Validación en tiempo real al pagar
- Mejor UX (mensaje claro de error)

---

## 💻 Frontend

### Stack Tecnológico

- **React 18** con Hooks modernos
- **React Router 6** para navegación SPA
- **Vite 5** para build y HMR ultrarrápido
- **TailwindCSS 3.4** para estilos utility-first
- **shadcn/ui** componentes accesibles y personalizables
- **Axios** cliente HTTP con interceptors
- **Lucide React** iconos modernos

### Estructura

```
assets/
├── app.jsx              # 🚪 Entry point con React Router
├── components/
│   ├── ui/              # 🎨 shadcn/ui (Button, Card, Input, Badge...)
│   ├── Layout.jsx       # 📐 Layout con header sticky y navegación
│   ├── ProtectedRoute.jsx  # 🔒 Guard para rutas autenticadas
│   └── AdminRoute.jsx   # 🛡️ Guard para rutas de admin
├── context/
│   ├── AuthContext.jsx  # 🔐 Estado global de autenticación (JWT)
│   └── CartContext.jsx  # 🛒 Estado global del carrito (localStorage)
├── pages/
│   ├── LoginPage.jsx    # 🔑 Login con validación
│   ├── CatalogPage.jsx  # 📦 Catálogo con búsqueda, filtros y paginación
│   ├── CartPage.jsx     # 🛍️ Carrito con ajuste de cantidades
│   ├── OrderDetailPage.jsx  # 💳 Detalle de orden + checkout
│   └── AdminProductsPage.jsx  # ⚙️ Panel admin para crear productos
├── lib/
│   ├── api.js          # 🌐 Cliente Axios con interceptors JWT
│   └── utils.js        # 🔧 Helpers (cn para Tailwind)
└── styles/
    └── app.css         # 🎨 Tailwind + estilos globales
```

### Características Clave

#### 🔐 Autenticación
- JWT real con refresh automático
- Token en `localStorage` + Context API
- Rutas protegidas con `<ProtectedRoute>` y `<AdminRoute>`
- Auto-redirect al login si no autenticado

#### 🛒 Carrito
- Persistencia en `localStorage`
- Context global accesible desde toda la app
- Actualización reactiva de cantidades
- Badge con contador en header

#### 🎨 UI/UX
- **Diseño responsive** (mobile-first)
- **Header sticky** con animaciones suaves
- **Feedback visual** en botones (pulse, scale)
- **Manejo de errores** con mensajes claros
- **Loading states** en todas las acciones
- **Imágenes con fallback** en productos

#### ⚡ Performance
- **Code splitting** automático con Vite
- **Lazy loading** de imágenes
- **HMR (Hot Module Replacement)** en desarrollo
- **Tree shaking** en producción

### Desarrollo

**Modo Desarrollo (HMR):**
```bash
make dev  # Terminal separada para Vite
```
Acceder a http://localhost:8777 (Vite se conecta automáticamente)

**Modo Producción:**
```bash
make build  # Compila assets optimizados en public/build/
```

---

## 🔐 Autenticación y Seguridad

### JWT Real (lexik/jwt-authentication-bundle)

**Flujo:**
1. Login en `/api/login` con email/password
2. Backend valida con bcrypt y genera JWT firmado con RS256
3. Cliente guarda token y lo envía en header: `Authorization: Bearer {token}`
4. Backend valida firma y expira en cada request

**Configuración:**
- Claves RSA en `config/jwt/` (generadas automáticamente)
- Expiración: 1 hora (configurable en `.env`)
- Algoritmo: RS256 (asimétrico)

### Guards Personalizados

**Atributos PHP 8:**
```php
#[RequiresAuth]                     // Requiere estar autenticado
#[RequiresRole('ROLE_ADMIN')]       // Requiere rol específico
```

**Listener:**
`SecurityAttributeListener` intercepta requests antes del controlador y valida permisos.

**Servicio CurrentUser:**
```php
$this->currentUser->id();       // ID del usuario JWT
$this->currentUser->email();    // Email
$this->currentUser->isAdmin();  // Verificar rol
```

### Jerarquía de Roles

```
ROLE_ADMIN  →  ROLE_USER
     ↓             ↓
  Crear       Comprar
 productos    productos
```

**Configurado en** `security.yaml`:
```yaml
role_hierarchy:
    ROLE_ADMIN: ROLE_USER  # Admin hereda permisos de User
```

---

## 📚 API REST

### Documentación Interactiva

👉 **http://localhost:8777/api/doc** (Swagger UI)

- ✅ Probador integrado (Try it out)
- ✅ Schemas detallados de requests/responses
- ✅ Autenticación JWT desde la UI
- ✅ Especificación OpenAPI 3.0

### Endpoints Principales

| Método | Endpoint | Descripción | Auth |
|--------|----------|-------------|------|
| `POST` | `/api/login` | Autenticación | No |
| `GET` | `/api/products` | Listar productos (paginado + búsqueda) | No |
| `POST` | `/api/products` | Crear producto | Admin |
| `POST` | `/api/orders` | Crear orden (carrito) | User |
| `GET` | `/api/orders/{id}` | Ver detalle de orden | User (owner) |
| `POST` | `/api/orders/{id}/checkout` | Procesar pago | User (owner) |


### Manejo de Errores

El sistema devuelve errores estructurados:

**Stock insuficiente (400):**
```json
{
  "error": "Insufficient stock for Product X. Available: 1, Required: 5",
  "type": "insufficient_stock"
}
```

**Validación (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Email is required"]
  }
}
```

---

## 🧪 Testing

### Cobertura

**73 tests** con **408 assertions** divididos en:

- ✅ **Unit Tests** (Application layer) - Lógica de negocio aislada
- ✅ **Infrastructure Tests** (Persistence) - Repositorios con BD real
- ✅ **Feature Tests** (HTTP) - Endpoints completos end-to-end

### Ejecutar Tests

```bash
make test              # Todos los tests
make test-unit         # Solo unitarios (rápidos)
make test-infrastructure  # Con base de datos
make test-feature      # API endpoints
```

### Object Mothers

Generación de datos de test con Faker:

```php
$product = ProductMother::random();
$product = ProductMother::withStock(50);
$customer = CustomerMother::admin();
$order = OrderMother::withItems(3);
```

### Base de Datos de Test

```bash
make test-db-reset  # Resetear BD test (drop+create+migrate+fixtures)
```

---

## ⚙️ Comandos y Desarrollo

### Comandos Principales

```bash
# Setup inicial
make setup           # Todo en uno (¡recomendado!)

# Contenedores
make up              # Levantar Docker
make down            # Detener Docker
make logs            # Ver logs
make shell           # Shell en contenedor PHP

# Base de datos
make migrate         # Ejecutar migraciones
make fixtures        # Cargar fixtures
make test-db-reset   # Resetear BD test

# Frontend
make build           # Build producción
make dev             # Dev server con HMR

# Tests
make test            # Todos los tests
make test-unit       # Solo unitarios
```

Usa `make help` para ver todos los comandos.


---

## 🛠️ Solución de Problemas

### Contenedores no inician
```bash
docker-compose down && docker-compose up -d
```

### Error 500 al login (claves JWT faltantes)
```bash
make jwt-keys  # Genera las claves RSA
docker-compose restart
```

### Frontend no se actualiza en dev
```bash
make dev  # Asegurar que Vite está corriendo
```

### Tests fallan
```bash
make test-db-reset  # Resetear BD de test
```

### Reset completo
```bash
make down
docker volume prune -f
make setup
```

### Ver logs detallados
```bash
make logs
# o
docker-compose logs -f php
```

---

## 🚀 Stack Completo

**Backend:**
- Symfony 7 + PHP 8.2
- PostgreSQL 16
- Doctrine ORM
- JWT (lexik/jwt-authentication-bundle)
- NelmioApiDoc (OpenAPI/Swagger)

**Frontend:**
- React 18 + React Router 6
- Vite 5
- TailwindCSS 3.4 + shadcn/ui
- Axios + Lucide Icons

**Infrastructure:**
- Docker + Docker Compose
- Make (automatización)
- PHPUnit (testing)

---

**Licencia:** Prueba técnica - Uso educativo

