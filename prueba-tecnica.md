1. Prueba Técnica PHP
Requerimiento
La empresa requiere un sistema básico de gestión de pedidos y pagos que permita a un cliente autenticado
(simulado) interactuar con un catálogo de productos, crear pedidos y realizar un proceso de pago
simulado. El objetivo es evaluar las competencias técnicas en desarrollo Backend con PHP/Symfony y
Frontend con React, asegurando buenas prácticas, pruebas unitarias y reproducibilidad.

Construir un MVP funcional compuesto por:
• Backend en PHP/Symfony: API REST para productos, pedidos y checkout.
• Frontend en React: Pantalla de login simulado, catálogo, carrito y detalle de pedido con checkout.
• Pruebas unitarias obligatorias en el backend.
• Docker Compose para levantar el entorno.

Casos de Uso
UC-P01: Listar productos (Catálogo)
• Permite obtener listado paginado con filtros simples.
• Endpoint: GET /products?search=&page=&sort=
UC-P02: Crear producto (Solo Admin simulado)
• Permite crear productos con validaciones y rol ADMIN simulado.
• Endpoint: POST /products
UC-O01: Crear pedido (Cliente simulado)
• Permite crear un pedido validando stock y calculando totales.
• Endpoint: POST /orders
UC-O02: Ver detalle de pedido (Cliente simulado)
• Permite consultar el detalle del pedido validando pertenencia.
• Endpoint: GET /orders/{id}
UC-O03: Checkout (simulado)
• Permite ejecutar el pago simulado y actualizar el estado del pedido.
• Endpoint: POST /orders/{id}/checkout

NOTA: Cuando se habla de Admin y Cliente simulados, quiere decir que debe existir el login en la aplicación
con una validación básica, sin un previo registro de usuario y roles.

2

hiberus.com
2. Consideraciones
Para la implementación del ejercicio planteado, considerar los siguientes puntos
mandatorios:

Backend
• Symfony 6/7, PHP 8.2+.
• Doctrine ORM con migraciones.
• Validación de datos y manejo de errores.
• Pruebas unitarias obligatorias (PHPUnit).
• Docker Compose (App + DB).
Frontend
• React (TypeScript recomendado).
• Pantalla de login simulado (customerId y rol).
• Catálogo, carrito y detalle de pedido con checkout.

3. Entregables
Los entregables esperados para este ejercicio son los siguientes:
Repositorio con:
• Código fuente del backend y frontend.
• Definición del modelo de datos utilizado para el ejercicio
• README con instrucciones claras (instalación, ejecución, pruebas).
• Docker Compose funcional.
• Pruebas unitarias ejecutables y reporte.
• Colección Postman/Insomnia o documentación OpenAPI.