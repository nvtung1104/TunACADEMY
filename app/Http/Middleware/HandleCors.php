<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\HandleCors as Middleware;

class HandleCors extends Middleware
{
    /**
     * The method to handle CORS requests.
     */
    protected array $paths = ['api/*', 'sanctum/csrf-cookie'];

    protected array $allowed_origins = [];

    protected array $allowed_methods = ['*'];

    protected array $allowed_headers = ['*'];

    protected array $exposed_headers = [];

    protected ?int $max_age = 0;

    protected bool $supports_credentials = true;
}
