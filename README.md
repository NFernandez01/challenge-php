# Challenge PHP - Gestión de Productos

Proyecto realizado con PHP nativo y JavaScript vanilla.

## Levantar el proyecto

Teniendo Docker y Docker Compose instalados:

```bash
docker-compose up -d --build
```

Una vez levantado:

- Frontend: http://localhost:3000
- API: http://localhost:8080/api/productos
- MySQL: localhost:3306

Credenciales de MySQL:

- Usuario: root
- Contraseña: root

## Configuración

Para modificar la cotización del dólar utilizada para calcular los precios en USD, editar el archivo que se encuentra en backend/.env

Modificando el valor: PRECIO_USD=1300

Despues reiniciar el backend:

```bash
docker-compose restart backend
```

## Endpoints disponibles

Obtener todos los productos:

```http
GET /api/productos
```

Obtener un producto por ID:

```http
GET /api/productos/{id}
```

Crear un producto:

```http
POST /api/productos
```

Actualizar un producto:

```http
PUT /api/productos/{id}
```

Eliminar un producto:

```http
DELETE /api/productos/{id}
```

Ejemplo de creación:

```bash
curl -X POST http://localhost:8080/api/productos \
-H "Content-Type: application/json" \
-d '{"nombre":"Notebook","descripcion":"Gaming","precio":1300000}'
```

## Base de datos

La aplicación crea automáticamente la tabla `productos` si no existe.

Campos:

- id
- nombre
- descripcion
- precio
- created_at
- updated_at

El precio se almacena en pesos argentinos y el valor en USD se calcula utilizando la cotización configurada en el archivo `.env`.

## Tecnologías utilizadas

- PHP 8.2
- Apache
- MySQL 8
- JavaScript Vanilla
- Docker

## Consideraciones

- Se utilizó una estructura MVC para organizar el código.
- La conexión a la base de datos se maneja mediante un Singleton.
- Todas las consultas utilizan Prepared Statements para evitar inyecciones SQL.