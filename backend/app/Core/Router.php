<?php

namespace App\Core;

use App\Controllers\ProductoController;

class Router {
   public function run()
   {
      $method = $_SERVER['REQUEST_METHOD'];

      $uri = parse_url(
         $_SERVER['REQUEST_URI'],
         PHP_URL_PATH
      );

      // Normalizar la URI
      $uri = rtrim($uri, '/');
      
      // Parsear la ruta para obtener el ID si existe
      if (preg_match('#^/api/productos/(\d+)$#', $uri, $matches)) {
         $id = $matches[1];
         
         $controller = new ProductoController();
         
         switch($method) {
               case 'GET':
                  $controller->show($id);
                  break;
               case 'PUT':
                  $controller->update($id);
                  break;
               case 'DELETE':
                  $controller->delete($id);
                  break;
               default:
                  http_response_code(405);
                  echo json_encode(['error' => 'Método no permitido']);
                  break;
         }
         return;
      }

      // Rutas sin ID
      if ($uri === '/api/productos') {
         $controller = new ProductoController();
         
         switch($method) {
               case 'GET':
                  $controller->index();
                  break;
               case 'POST':
                  $controller->create();
                  break;
               default:
                  http_response_code(405);
                  echo json_encode(['error' => 'Método no permitido']);
                  break;
         }
         return;
      }

      // Si la ruta no es encontrada, entonces retornar un error 404
      http_response_code(404);
      header('Content-Type: application/json');
      echo json_encode(['error' => 'Ruta no encontrada']);
   }
}