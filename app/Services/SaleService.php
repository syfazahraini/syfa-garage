<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Logika bisnis penjualan (barang keluar).
 *
 * Aturan penting:
 * - Header, item, dan pengurangan stok disimpan dalam SATU DB::transaction (atomik).
 * - Harga diambil dari database, bukan dari browser.
 * - Stok dicek sebelum dikurangi; penjualan tidak boleh melebihi stok.
 * - Baris produk dikunci (lockForUpdate) untuk mencegah overselling pada transaksi bersamaan.
 * - Jika satu item gagal, seluruh transaksi di-rollback.
 */
class SaleService
{
    /**
     * @param  array{items:array<int, array{product_id:int, quantity:int}>}  $data
     */
    public function create(User $user, array $data): Sale
    {
        return DB::transaction(function () use ($user, $data) {
            $rows = collect($data['items']);

            // Kunci semua produk yang terlibat sekaligus, diurutkan berdasarkan id.
            // Urutan yang sama di setiap transaksi mencegah deadlock.
            $products = Product::whereIn('id', $rows->pluck('product_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Total jumlah yang diminta per produk (jaga-jaga kalau ada produk yang sama di dua baris)
            $requested = $rows
                ->groupBy(fn ($row) => (int) $row['product_id'])
                ->map(fn ($group) => $group->sum(fn ($row) => (int) $row['quantity']));

            // 1) Periksa SEMUA item dulu sebelum menyimpan apa pun
            $problems = [];

            foreach ($requested as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product) {
                    $problems[] = 'Salah satu produk tidak ditemukan atau sudah dihapus.';
                } elseif ($quantity < 1) {
                    $problems[] = "Jumlah untuk \"{$product->name}\" harus lebih dari 0.";
                } elseif ($product->stock < $quantity) {
                    $problems[] = "Stok \"{$product->name}\" tidak cukup: tersedia {$product->stock}, diminta {$quantity}.";
                }
            }

            if ($problems !== []) {
                throw new BusinessRuleException('Penjualan ditolak. ' . implode(' ', $problems));
            }

            // 2) Simpan header, item, dan kurangi stok
            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => $this->generateNumber(),
                'total' => 0,
                'status' => Sale::STATUS_COMPLETED,
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($rows as $row) {
                $product = $products->get((int) $row['product_id']);
                $quantity = (int) $row['quantity'];

                // Harga dari database, bukan dari input browser
                $unitPrice = round((float) $product->selling_price, 2);
                $subtotal = round($quantity * $unitPrice, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Pengurangan bersyarat: hanya berhasil kalau stok masih cukup (pengaman kedua).
                // Hasilnya jumlah baris yang berubah; kalau bukan 1, berarti stok tidak cukup.
                $affected = Product::whereKey($product->id)
                    ->where('stock', '>=', $quantity)
                    ->decrement('stock', $quantity);

                if ($affected !== 1) {
                    throw new BusinessRuleException(
                        "Stok \"{$product->name}\" berubah dan tidak cukup lagi. Silakan coba lagi."
                    );
                }

                $total += $subtotal;
            }

            $sale->update(['total' => round($total, 2)]);

            return $sale;
        });
    }

    /**
     * Batalkan penjualan dan kembalikan stok seperti sebelum transaksi.
     */
    public function cancel(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            // Ambil ulang dengan kunci, lalu cek status lagi (mencegah pembatalan ganda)
            $locked = Sale::whereKey($sale->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== Sale::STATUS_COMPLETED) {
                throw new BusinessRuleException('Penjualan ini sudah dibatalkan sebelumnya.');
            }

            $items = $locked->items()->get();

            // Kunci produk (termasuk yang sudah dihapus/soft delete) sebelum mengubah stok
            Product::withTrashed()
                ->whereIn('id', $items->pluck('product_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($items as $item) {
                // withTrashed: produk yang sudah dihapus tetap harus dikembalikan stoknya
                Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
            }

            $locked->update(['status' => Sale::STATUS_CANCELLED]);
        });
    }

    /** Nomor transaksi unik, contoh: INV-20261023-K7M2Q */
    private function generateNumber(): string
    {
        do {
            $number = 'INV-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (Sale::where('sale_number', $number)->exists());

        return $number;
    }
}
