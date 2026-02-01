<?php

declare(strict_types=1);

namespace WPZylos\Framework\Database;

use InvalidArgumentException;

/**
 * Minimal query builder.
 *
 * Fluent interface for building SELECT/INSERT/UPDATE/DELETE queries.
 * All queries use $wpdb->prepare() internally.
 *
 * @package WPZylos\Framework\Database
 */
class QueryBuilder
{
    /**
     * Valid table/column name pattern (alphanumeric and underscore, must start with letter/underscore).
     */
    private const IDENTIFIER_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

    private Connection $connection;
    private string $table;
    private array $select = ['*'];
    private array $wheres = [];
    private array $bindings = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private array $orderBy = [];

    /**
     * Create a query builder.
     *
     * @param Connection $connection Database connection
     * @param string $table Full table name
     *
     * @throws \InvalidArgumentException If the table name is invalid
     */
    public function __construct(Connection $connection, string $table)
    {
        $this->validateIdentifier($table, 'table');
        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * Validate a SQL identifier (table or column name).
     *
     * @param string $identifier Identifier to validate
     * @param string $type Type for the error message ('table' or 'column')
     *
     * @throws \InvalidArgumentException If the identifier is invalid
     */
    private function validateIdentifier(string $identifier, string $type = 'identifier'): void
    {
        // Handle prefixed table names (e.g., wp_myplugin_users)
        $parts = explode('.', $identifier);
        foreach ($parts as $part) {
            if (!preg_match(self::IDENTIFIER_PATTERN, $part)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid %s name: %s. Must match pattern: %s', $type, $identifier, self::IDENTIFIER_PATTERN)
                );
            }
        }
    }

    /**
     * Set columns to select.
     *
     * @param string|string[] $columns Columns
     *
     * @return static
     */
    public function select(string|array $columns = ['*']): static
    {
        $this->select = is_array($columns) ? $columns : func_get_args();

        return $this;
    }

    /**
     * Add where clause.
     *
     * @param string $column Column name
     * @param mixed $operator Operator or value (if 2 args)
     * @param mixed $value Value (if 3 args)
     *
     * @return static
     */
    public function where(string $column, mixed $operator, mixed $value = null): static
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [$column, $operator, count($this->bindings)];
        $this->bindings[] = $value;

        return $this;
    }

    /**
     * Add where IN clause.
     *
     * @param string $column Column name
     * @param array $values Values
     *
     * @return static
     */
    public function whereIn(string $column, array $values): static
    {
        if (empty($values)) {
            // Force no results
            $this->wheres[] = ['1', '=', count($this->bindings)];
            $this->bindings[] = 0;

            return $this;
        }

        $placeholders = [];
        foreach ($values as $value) {
            $placeholders[] = count($this->bindings);
            $this->bindings[] = $value;
        }

        $this->wheres[] = [$column, 'IN', $placeholders];

        return $this;
    }

    /**
     * Set limit.
     *
     * @param int $limit Limit
     *
     * @return static
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Set offset.
     *
     * @param int $offset Offset
     *
     * @return static
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Add order by clause.
     *
     * @param string $column Column name
     * @param string $direction 'ASC' or 'DESC'
     *
     * @return static
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBy[] = "`{$column}` {$direction}";

        return $this;
    }

    /**
     * Get all results.
     *
     * @return array<object>
     */
    public function get(): array
    {
        $sql = $this->buildSelect();

        return $this->connection->getResults($sql, ...$this->bindings);
    }

    /**
     * Get the first result.
     *
     * @return object|null
     */
    public function first(): ?object
    {
        $this->limit(1);
        $sql = $this->buildSelect();

        return $this->connection->getRow($sql, ...$this->bindings);
    }

    /**
     * Get count.
     *
     * @return int
     */
    public function count(): int
    {
        $this->select = ['COUNT(*) as aggregate'];
        $sql = $this->buildSelect();
        $result = $this->connection->getVar($sql, ...$this->bindings);

        return (int) ($result ?? 0);
    }

    /**
     * Insert a row.
     *
     * @param array<string, mixed> $data Column => value pairs
     *
     * @return int|false Insert ID or false
     */
    public function insert(array $data): int|false
    {
        return $this->connection->insert($this->table, $data);
    }

    /**
     * Update rows matching current conditions.
     *
     * @param array<string, mixed> $data Column => value pairs
     *
     * @return int|false Rows affected or false
     */
    public function update(array $data): int|false
    {
        $where = $this->buildWhereArray();

        return $this->connection->update($this->table, $data, $where);
    }

    /**
     * Delete rows matching current conditions.
     *
     * @return int|false Rows affected or false
     */
    public function delete(): int|false
    {
        $where = $this->buildWhereArray();

        return $this->connection->delete($this->table, $where);
    }

    /**
     * Build SELECT query.
     *
     * @return string SQL query
     */
    private function buildSelect(): string
    {
        $columns = implode(', ', $this->select);
        $sql = "SELECT {$columns} FROM `{$this->table}`";

        $sql .= $this->buildWhere();

        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orderBy);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT %d';
            $this->bindings[] = $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET %d';
            $this->bindings[] = $this->offset;
        }

        return $sql;
    }

    /**
     * Get the SQL query string (for debugging/testing).
     *
     * @return string SQL query
     */
    public function toSql(): string
    {
        return $this->buildSelect();
    }

    /**
     * Build WHERE clause.
     *
     * @return string SQL where
     */
    private function buildWhere(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $clauses = [];

        foreach ($this->wheres as $where) {
            [$column, $operator, $bindingIndex] = $where;

            if ($operator === 'IN') {
                $placeholders = array_map(static fn($i) => '%s', (array) $bindingIndex);
                $clauses[] = "`{$column}` IN (" . implode(', ', $placeholders) . ')';
            } else {
                $clauses[] = "`{$column}` {$operator} %s";
            }
        }

        return ' WHERE ' . implode(' AND ', $clauses);
    }

    /**
     * Build where as an array for update/delete.
     *
     * @return array<string, mixed>
     */
    private function buildWhereArray(): array
    {
        $where = [];

        foreach ($this->wheres as $index => $whereClause) {
            [$column, $operator, $bindingIndex] = $whereClause;
            if ($operator === '=' && is_int($bindingIndex)) {
                $where[$column] = $this->bindings[$bindingIndex];
            }
        }

        return $where;
    }
}
