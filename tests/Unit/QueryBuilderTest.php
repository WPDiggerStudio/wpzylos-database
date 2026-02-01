<?php

declare(strict_types=1);

namespace WPZylos\Framework\Database\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Database\Connection;
use WPZylos\Framework\Database\QueryBuilder;

/**
 * Tests for QueryBuilder class.
 */
class QueryBuilderTest extends TestCase
{
    private function createQueryBuilder(string $table): QueryBuilder
    {
        $connection = $this->createMock(Connection::class);
        return new QueryBuilder($connection, $table);
    }

    public function testSelectBuildsQuery(): void
    {
        $builder = $this->createQueryBuilder('wp_test_users');
        $builder->select('id', 'name');

        $sql = $builder->toSql();

        $this->assertStringContainsString('SELECT', $sql);
        $this->assertStringContainsString('id', $sql);
        $this->assertStringContainsString('name', $sql);
    }

    public function testWhereAddsCondition(): void
    {
        $builder = $this->createQueryBuilder('wp_test_users');
        $builder->where('status', 'active');

        $sql = $builder->toSql();

        $this->assertStringContainsString('WHERE', $sql);
        $this->assertStringContainsString('status', $sql);
    }

    public function testOrderByAddsOrdering(): void
    {
        $builder = $this->createQueryBuilder('wp_test_users');
        $builder->orderBy('created_at', 'DESC');

        $sql = $builder->toSql();

        $this->assertStringContainsString('ORDER BY', $sql);
        $this->assertStringContainsString('DESC', $sql);
    }

    public function testLimitAddsLimit(): void
    {
        $builder = $this->createQueryBuilder('wp_test_users');
        $builder->limit(10);

        $sql = $builder->toSql();

        $this->assertStringContainsString('LIMIT', $sql);
    }

    public function testMethodChainingWorks(): void
    {
        $builder = $this->createQueryBuilder('wp_test_posts');

        $result = $builder
            ->select('id', 'title')
            ->where('status', 'published')
            ->orderBy('created_at', 'DESC')
            ->limit(5);

        $this->assertInstanceOf(QueryBuilder::class, $result);
    }
}
