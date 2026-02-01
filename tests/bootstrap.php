<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap for database package.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Mock global $wpdb
$GLOBALS['wpdb'] = new class {
    public string $prefix = 'wp_';
    public array $last_query = [];
    public array $results = [];

    public function prepare(string $query, ...$args): string
    {
        return vsprintf(str_replace(['%s', '%d'], ["'%s'", '%d'], $query), $args);
    }

    public function get_results(string $query, string $output = OBJECT): array
    {
        $this->last_query[] = $query;
        return $this->results;
    }

    public function get_row(string $query, string $output = OBJECT): ?object
    {
        $this->last_query[] = $query;
        return $this->results[0] ?? null;
    }

    public function insert(string $table, array $data): int
    {
        $this->last_query[] = ['insert' => $table, 'data' => $data];
        return 1;
    }

    public function update(string $table, array $data, array $where): int
    {
        $this->last_query[] = ['update' => $table, 'data' => $data, 'where' => $where];
        return 1;
    }

    public function delete(string $table, array $where): int
    {
        $this->last_query[] = ['delete' => $table, 'where' => $where];
        return 1;
    }
};

if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}
