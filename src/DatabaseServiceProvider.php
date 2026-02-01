<?php

declare(strict_types=1);

namespace WPZylos\Framework\Database;

use WPZylos\Framework\Core\Contracts\ApplicationInterface;
use WPZylos\Framework\Core\ServiceProvider;

/**
 * Database service provider.
 *
 * @package WPZylos\Framework\Database
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * {@inheritDoc}
     */
    public function register(ApplicationInterface $app): void
    {
        parent::register($app);

        $this->singleton(Connection::class, fn() => new Connection($app->context()));
        $this->singleton('db', fn() => $this->make(Connection::class));
    }
}
