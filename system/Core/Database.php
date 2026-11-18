<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $cfg = (array)config('db');
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $cfg['host'] ?? 'localhost',
                (int)($cfg['port'] ?? 3306),
                $cfg['name'] ?? ''
            );
            try {
                self::$pdo = new PDO($dsn, (string)($cfg['user'] ?? ''), (string)($cfg['pass'] ?? ''), [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                if (config('app.debug')) {
                    throw $e;
                }
                http_response_code(500);
                echo 'Не удалось подключиться к базе данных. Проверьте config/config.php.';
                exit;
            }
        }
        return self::$pdo;
    }

    public static function q(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $row = self::q($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::q($sql, $params)->fetchAll();
    }

    public static function value(string $sql, array $params = [], mixed $default = null): mixed
    {
        $value = self::q($sql, $params)->fetchColumn();
        return $value === false ? $default : $value;
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', array_fill(0, count($columns), '?'))
        );
        self::q($sql, array_values($data));
        return (int)self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): void
    {
        $sets = implode(', ', array_map(static fn (string $c): string => $c . ' = ?', array_keys($data)));
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, $sets, $where);
        self::q($sql, array_merge(array_values($data), $whereParams));
    }

    public static function delete(string $table, string $where, array $params = []): void
    {
        self::q(sprintf('DELETE FROM %s WHERE %s', $table, $where), $params);
    }
}
