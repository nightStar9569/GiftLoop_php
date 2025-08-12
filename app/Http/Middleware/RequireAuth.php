<?php
namespace App\Http\Middleware;

class RequireAuth
{
    public function handle(): void
    {
        require_auth();
    }
} 