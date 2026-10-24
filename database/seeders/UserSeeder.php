<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun DEMO saja (bukan kredensial asli).
     * admin@syfagarage.test / password
     * staff@syfagarage.test / password
     * user@syfagarage.test / password   (pelanggan: hanya melihat produk & dashboard)
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Admin Syfa Garage', 'email' => 'admin@syfagarage.test', 'role' => User::ROLE_ADMIN],
            ['name' => 'Staff Syfa Garage', 'email' => 'staff@syfagarage.test', 'role' => User::ROLE_STAFF],
            ['name' => 'Pelanggan Demo', 'email' => 'user@syfagarage.test', 'role' => User::ROLE_USER],
        ];

        foreach ($accounts as $account) {
            // role tidak ada di $fillable, jadi diisi lewat forceFill
            $user = User::firstOrNew(['email' => $account['email']]);
            $user->forceFill([
                'name' => $account['name'],
                'password' => 'password', // di-hash otomatis oleh cast 'hashed'
                'role' => $account['role'],
            ])->save();
        }
    }
}
