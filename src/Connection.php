<?php

declare(strict_types=1);

namespace WPZylos\Framework\Database;

use WPZylos\Framework\Core\Contracts\ContextInterface;

/**
 * Database connection wrapper.
 *
 * Wraps $wpdb with plugin-scoped table naming and safe query methods.
 * All queries use $wpdb->prepare() internally.
 *
 * @package WPZylos\Framework\Database
 */
class Connection
{
    /**
     * @var ContextInterface Plugin context
     */
    private ContextInterface $context;

    /**
     * @var \wpdb WordPress database
     */
    private \wpdb $wpdb;

    /**
     * Create connection.
     *
     * @param ContextInterface $context Plugin context
     * @param \wpdb|null $database
     */
    public function __construct(ContextInterface $context, ?\wpdb $database = null)
    {
        $this->context = $context;
        /** @var \wpdb $globalWpdb */
        $globalWpdb = $GLOBALS['wpdb'];
        $this->wpdb = $database ?? $globalWpdb;
    }

    /**
     * Create a query builder for a table.
     *
     * @param string $name Table name (without plugin prefix)
     * @param string $scope 'site' or 'network'
     * @return QueryBuilder
     */
    public function table(string $name, string $scope = 'site'): QueryBuilder
    {
        $fullName = $this->context->tableName($name, $scope);
        return new QueryBuilder($this, $fullName);
    }

    /**
     * Get raw wpdb instance.
     *
     * @return \wpdb
     */
    public function wpdb(): \wpdb
    {
        return $this->wpdb;
    }

    /**
     * Get charset collation for CREATE TABLE.
     *
     * @return string
     */
    public function charsetCollate(): string
    {
        return $this->wpdb->get_charset_collate();
    }

    /**
     * Execute a raw query using preparing.
     *
     * @param string $query SQL query with placeholders
     * @param mixed ...$args Values for placeholders
     * @return bool|int
     */
    public function query(string $query, mixed ...$args): bool|int
    {
        if (!empty($args)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $prepared = $this->wpdb->prepare($query, ...$args);
            if (is_string($prepared)) {
                $query = $prepared;
            }
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->query($query);
    }

    /**
     * Get a single row.
     *
     * @param string $query SQL query
     * @param mixed ...$args Query arguments
     * @return object|null
     */
    public function getRow(string $query, mixed ...$args): ?object
    {
        if (!empty($args)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $prepared = $this->wpdb->prepare($query, ...$args);
            if (is_string($prepared)) {
                $query = $prepared;
            }
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_row($query);
    }

    /**
     * Get multiple rows.
     *
     * @param string $query SQL query
     * @param mixed ...$args Query arguments
     * @return array<object>
     */
    public function getResults(string $query, mixed ...$args): array
    {
        if (!empty($args)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $prepared = $this->wpdb->prepare($query, ...$args);
            if (is_string($prepared)) {
                $query = $prepared;
            }
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_results($query) ?: [];
    }

    /**
     * Get a single variable.
     *
     * @param string $query SQL query
     * @param mixed ...$args Query arguments
     * @return string|null
     */
    public function getVar(string $query, mixed ...$args): ?string
    {
        if (!empty($args)) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $prepared = $this->wpdb->prepare($query, ...$args);
            if (is_string($prepared)) {
                $query = $prepared;
            }
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        return $this->wpdb->get_var($query);
    }

    /**
     * Insert a row.
     *
     * @param string $table Full table name
     * @param array<string, mixed> $data Column => value pairs
     * @param string[]|null $format Data formats
     * @return int|false Insert ID or false
     */
    public function insert(string $table, array $data, ?array $format = null): int|false
    {
        $result = $this->wpdb->insert($table, $data, $format);
        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Update rows.
     *
     * @param string $table Full table name
     * @param array<string, mixed> $data Column => value pairs
     * @param array<string, mixed> $where Where conditions
     * @param string[]|null $format Data formats
     * @param string[]|null $whereFormat Where formats
     * @return int|false Rows affected or false
     */
    public function update(
        string $table,
        array $data,
        array $where,
        ?array $format = null,
        ?array $whereFormat = null
    ): int|false {
        return $this->wpdb->update($table, $data, $where, $format, $whereFormat);
    }

    /**
     * Delete rows.
     *
     * @param string $table Full table name
     * @param array<string, mixed> $where Where conditions
     * @param string[]|null $whereFormat Where formats
     * @return int|false Rows affected or false
     */
    public function delete(string $table, array $where, ?array $whereFormat = null): int|false
    {
        return $this->wpdb->delete($table, $where, $whereFormat);
    }

    /**
     * Get last insert ID.
     *
     * @return int
     */
    public function lastInsertId(): int
    {
        return $this->wpdb->insert_id;
    }

    /**
     * Get last error.
     *
     * @return string
     */
    public function lastError(): string
    {
        return $this->wpdb->last_error;
    }

    /**
     * Begin transaction.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return (bool) $this->wpdb->query('START TRANSACTION');
    }

    /**
     * Commit transaction.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return (bool) $this->wpdb->query('COMMIT');
    }

    /**
     * Rollback transaction.
     *
     * @return bool
     */
    public function rollback(): bool
    {
        return (bool) $this->wpdb->query('ROLLBACK');
    }
}
