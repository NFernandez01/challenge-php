<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Producto
{
    private const COLUMNAS = 'id, nombre, descripcion, precio, created_at, updated_at';

    private PDO $db;

    // La conexión se puede inyectar (por ejemplo, una base de test); si no, usa la global
    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getConnection();
    }

    /** @return array<int, array<string, mixed>> */
    public function getAll(): array
    {
        $stmt = $this->db->query(
            'SELECT ' . self::COLUMNAS . ' FROM productos ORDER BY created_at DESC, id DESC'
        );

        return $stmt->fetchAll();
    }

    /** @return array<string, mixed>|null */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT ' . self::COLUMNAS . ' FROM productos WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // fetch() devuelve false si no hay filas: lo normalizamos a null
        return $stmt->fetch() ?: null;
    }

    /** @param array{nombre: string, descripcion: ?string, precio: string} $data */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO productos (nombre, descripcion, precio) VALUES (:nombre, :descripcion, :precio)'
        );
        $this->bindDatos($stmt, $data);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }

    /** @param array{nombre: string, descripcion: ?string, precio: string} $data */
    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio WHERE id = :id'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $this->bindDatos($stmt, $data);
        $stmt->execute();
    }

    /** Devuelve true si efectivamente se borró una fila. */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM productos WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    private function bindDatos(\PDOStatement $stmt, array $data): void
    {
        $stmt->bindValue(':nombre', $data['nombre']);
        $stmt->bindValue(
            ':descripcion',
            $data['descripcion'],
            $data['descripcion'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR
        );
        // El precio viaja como string ("1234.50") para no perder precisión en el DECIMAL
        $stmt->bindValue(':precio', $data['precio']);
    }
}
