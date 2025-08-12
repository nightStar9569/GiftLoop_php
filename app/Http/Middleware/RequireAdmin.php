<?php
namespace App\Http\Middleware;

class RequireAdmin
{
    public function handle(): void
    {
        if (!is_admin()) {
            json_response(['error' => 'Forbidden'], 403);
        }
    }
} 