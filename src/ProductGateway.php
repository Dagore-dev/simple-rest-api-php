<?php

class ProductGateway
{
    private PDO $connection;

    public function __construct (Database $database)
    {
        $this->connection = $database->getConnection();
    }

    public function getAll (): array
    {
        $sql = 'SELECT * FROM product';
        $query = $this->connection->query($sql);

        $data = [];

        while ($row = $query->fetch(PDO::FETCH_ASSOC))
        {
            $row['is_available'] = (bool) $row['is_available'];
            $data[] = $row;
        }

        return $data;
    }

    public function create (array $data): string
    {
        $sql = 'INSERT INTO product (name, size, is_available) VALUES (:name, :size, :is_available)';
        $query = $this->connection->prepare($sql);

        $query->bindValue(':name', $data['name']);
        $query->bindValue(':size', $data['size'] ?? 0, PDO::PARAM_INT);
        // For some reason I don't understand, the following line throws an error when using the Null Coalesce Operator
        $query->bindValue(':is_available', isset($data['is_available']) && $data['is_available'], PDO::PARAM_BOOL);

        $query->execute();

        return $this->connection->lastInsertId();
    }

    public function get (string $id): array|false
    {
        $sql = 'SELECT * FROM product WHERE id = :id';

        $query = $this->connection->prepare($sql);

        $query->bindValue(':id', $id, PDO::PARAM_INT);

        $query->execute();
        $data = $query->fetch(PDO::FETCH_ASSOC);
        if ($data !== false)
        {
            $data['is_available'] = (bool) $data['is_available'];
        }

        return $data;
    }

    public function update (array $current, array $new): int
    {
        $sql = 'UPDATE product
                SET 
                    name = :name,
                    size = :size,
                    is_available = :is_available
                WHERE id = :id';
        $query = $this->connection->prepare($sql);

        $query->bindValue(':name', $new['name'] ?? $current['name']);
        $query->bindValue(':size', $new['size'] ?? $current['size'], PDO::PARAM_INT);
        $query->bindValue(':is_available', $new['is_available'] ?? $current['is_available'], PDO::PARAM_BOOL);
        $query->bindValue(':id', $current['id'], PDO::PARAM_INT);

        $query->execute();

        return $query->rowCount();
    }

    public function delete (string $id): int
    {
        $sql = 'DELETE FROM product WHERE id = :id';
        $query = $this->connection->prepare($sql);

        $query->bindValue(':id', $id, PDO::PARAM_INT);

        $query->execute();

        return $query->rowCount();
    }
}