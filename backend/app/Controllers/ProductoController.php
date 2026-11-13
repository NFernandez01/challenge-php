<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Producto;

class ProductoController
{
    private const NOMBRE_MAX = 255;         // VARCHAR(255)
    private const DESCRIPCION_MAX = 1000;
    private const PRECIO_MAX = 99999999.99; // DECIMAL(10,2)

    private Producto $model;

    public function __construct(?Producto $model = null)
    {
        $this->model = $model ?? new Producto();
    }

    /**
     * Lista todos los productos con el precio en ARS (precio) y en USD (precio_usd).
     */
    public function index(): void
    {
        $productos = array_map(
            fn (array $p): array => $this->conPrecioUsd($p),
            $this->model->getAll()
        );

        $this->json($productos);
    }

    /**
     * Detalle de un producto, con ambos precios.
     */
    public function show(int $id): void
    {
        $producto = $this->model->getById($id);

        if ($producto === null) {
            $this->json(['error' => 'Producto no encontrado'], 404);
            return;
        }

        $this->json($this->conPrecioUsd($producto));
    }

    /**
     * Crea un producto nuevo.
     */
    public function create(): void
    {
        $datos = $this->leerYValidar();
        if ($datos === null) {
            return; // la respuesta de error ya se envió
        }

        $id = $this->model->create($datos);

        $this->json($this->conPrecioUsd($this->model->getById($id)), 201);
    }

    /**
     * Reemplaza un producto existente (PUT: se envían todos los campos).
     */
    public function update(int $id): void
    {
        if ($this->model->getById($id) === null) {
            $this->json(['error' => 'Producto no encontrado'], 404);
            return;
        }

        $datos = $this->leerYValidar();
        if ($datos === null) {
            return;
        }

        $this->model->update($id, $datos);

        $this->json($this->conPrecioUsd($this->model->getById($id)));
    }

    /**
     * Elimina un producto.
     */
    public function delete(int $id): void
    {
        if (!$this->model->delete($id)) {
            $this->json(['error' => 'Producto no encontrado'], 404);
            return;
        }

        $this->json(['message' => 'Producto eliminado exitosamente']);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Lee el body JSON y lo valida. Si algo falla, responde el error y devuelve null.
     *
     * @return array{nombre: string, descripcion: ?string, precio: string}|null
     */
    private function leerYValidar(): ?array
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $this->json(['error' => 'El cuerpo de la petición no es un JSON válido'], 400);
            return null;
        }

        if (!is_array($data)) {
            $this->json(['error' => 'Se esperaba un objeto JSON'], 400);
            return null;
        }

        [$datos, $errores] = $this->validar($data);

        if ($errores !== []) {
            // "error" se mantiene para el frontend; "errors" trae el detalle por campo
            $this->json([
                'error' => implode('. ', $errores),
                'errors' => $errores,
            ], 422);
            return null;
        }

        return $datos;
    }

    /**
     * Valida y normaliza los datos de un producto. Devuelve todos los errores juntos.
     *
     * @return array{0: array{nombre: string, descripcion: ?string, precio: string}, 1: array<string, string>}
     */
    private function validar(array $data): array
    {
        $errores = [];

        // nombre: string obligatorio, sin espacios sobrantes, máx. 255
        $nombre = $data['nombre'] ?? null;
        if (!is_string($nombre) || trim($nombre) === '') {
            $errores['nombre'] = 'El nombre es obligatorio';
        } elseif (mb_strlen(trim($nombre)) > self::NOMBRE_MAX) {
            $errores['nombre'] = 'El nombre no puede superar los ' . self::NOMBRE_MAX . ' caracteres';
        }

        // descripcion: opcional; string o null
        $descripcion = $data['descripcion'] ?? null;
        if ($descripcion !== null && !is_string($descripcion)) {
            $errores['descripcion'] = 'La descripción debe ser texto';
        } elseif (is_string($descripcion) && mb_strlen(trim($descripcion)) > self::DESCRIPCION_MAX) {
            $errores['descripcion'] = 'La descripción no puede superar los ' . self::DESCRIPCION_MAX . ' caracteres';
        }

        // precio: número positivo, máx. 2 decimales y dentro del rango del DECIMAL(10,2)
        $precio = $this->normalizarPrecio($data['precio'] ?? null);
        if ($precio === null) {
            $errores['precio'] = 'El precio debe ser un número con hasta 2 decimales';
        } elseif ((float) $precio <= 0 || (float) $precio > self::PRECIO_MAX) {
            $errores['precio'] = 'El precio debe estar entre 0.01 y ' . number_format(self::PRECIO_MAX, 2, '.', '');
        }

        $datos = [
            'nombre' => is_string($nombre) ? trim($nombre) : '',
            'descripcion' => is_string($descripcion) && trim($descripcion) !== '' ? trim($descripcion) : null,
            'precio' => $precio ?? '0',
        ];

        return [$datos, $errores];
    }

    /**
     * Acepta 1500, 1500.5 o "1500.50" y lo devuelve como string "1500.50".
     * Devuelve null si no es un número válido (texto, bool, más de 2 decimales, notación 1e5...).
     */
    private function normalizarPrecio(mixed $valor): ?string
    {
        if (is_int($valor)) {
            return $valor . '.00';
        }

        if (is_float($valor)) {
            if (!is_finite($valor) || round($valor, 2) !== $valor) {
                return null;
            }
            return number_format($valor, 2, '.', '');
        }

        if (is_string($valor) && preg_match('/^\d+(\.\d{1,2})?$/', $valor)) {
            return number_format((float) $valor, 2, '.', '');
        }

        return null;
    }

    /** Agrega precio_usd según la cotización configurada en PRECIO_USD. */
    private function conPrecioUsd(array $producto): array
    {
        $producto['precio_usd'] = round((float) $producto['precio'] / (float) $_ENV['PRECIO_USD'], 2);

        return $producto;
    }

    private function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
