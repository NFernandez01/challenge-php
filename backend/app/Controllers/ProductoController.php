<?php

namespace App\Controllers;

use App\Models\Producto;

class ProductoController {
    private $model;

    public function __construct()
    {
        $this->model = new Producto();
    }

    /** 
     * Obtener una lista de todos los productos, mostrando tanto el precio en Pesos Argentinos (precio) 
     * como el precio calculado en Dólares (precio_usd). 
    **/
    public function index()
    {
        $productos = $this->model->getAll();

        foreach($productos as &$producto){
            $producto['precio_usd'] = round(
                $producto['precio'] / $_ENV['PRECIO_USD'],
                2
            );
        }

        echo json_encode($productos);
    }

    /**
     *  Obtener los detalles de un producto específico, incluyendo ambos precios.
    **/
    public function show($id)
    {
        $producto = $this->model->getById($id);

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            return;
        }

        $producto['precio_usd'] = round(
            $producto['precio'] / $_ENV['PRECIO_USD'],
            2
        );

        echo json_encode($producto);
    }
    
    /**
     * Crear un nuevo producto.
     */
    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['nombre']) || !isset($data['precio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y precio son requeridos']);
            return;
        }

        if (!is_numeric($data['precio']) || $data['precio'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El precio debe ser un número positivo']);
            return;
        }

        $insertData = [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? '',
            'precio' => $data['precio']
        ];

        try {
            $id = $this->model->create($insertData);
            $producto = $this->model->getById($id);
            $producto['precio_usd'] = round(
                $producto['precio'] / $_ENV['PRECIO_USD'],
                2
            );

            http_response_code(201);
            echo json_encode($producto);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear el producto']);
        }
    }

    /**
     * Actualizar un producto existente.
     */
    public function update($id)
    {
        $producto = $this->model->getById($id);

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['nombre']) || !isset($data['precio'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nombre y precio son requeridos']);
            return;
        }

        if (!is_numeric($data['precio']) || $data['precio'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'El precio debe ser un número positivo']);
            return;
        }

        $updateData = [
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? '',
            'precio' => $data['precio']
        ];

        try {
            $this->model->update($id, $updateData);
            $producto = $this->model->getById($id);
            $producto['precio_usd'] = round(
                $producto['precio'] / $_ENV['PRECIO_USD'],
                2
            );

            echo json_encode($producto);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar el producto']);
        }
    }

    /**
     * Eliminar un producto existente.
     */
    public function delete($id)
    {
        $producto = $this->model->getById($id);

        if (!$producto) {
            http_response_code(404);
            echo json_encode(['error' => 'Producto no encontrado']);
            return;
        }

        try {
            $this->model->delete($id);
            http_response_code(200);
            echo json_encode(['message' => 'Producto eliminado exitosamente']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar el producto']);
        }
    }
}