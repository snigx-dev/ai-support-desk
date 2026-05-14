<?php

namespace App\Actions\Tickets;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTickets
{
    public function handle(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->tickets()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
