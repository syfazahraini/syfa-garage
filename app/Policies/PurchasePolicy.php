<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    /**
     * Admin boleh melihat semua pembelian.
     * Staff hanya boleh melihat pembelian yang dia buat sendiri.
     */
    public function view(User $user, Purchase $purchase): bool
    {
        return $user->isAdmin() || $purchase->user_id === $user->id;
    }
}
