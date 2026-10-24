<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    /**
     * Admin boleh melihat semua penjualan.
     * Staff hanya boleh melihat penjualan yang dia buat sendiri.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $user->isAdmin() || $sale->user_id === $user->id;
    }
}
