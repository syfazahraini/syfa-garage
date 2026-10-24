<?php

namespace App\Services;

use App\Exceptions\BusinessRuleException;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Logika bisnis pembelian (barang masuk).
 * Semua perubahan data dan stok dilakukan dalam satu DB::transaction:
 * kalau ada satu langkah gagal, semuanya dibatalkan (rollback).
 */
class PurchaseService
{
    /**
     * Simpan pembelian baru dan tambah stok.
     *
     * @param  array{supplier_id:int, items:array<int, array{product_id:int, quantity:int, unit_cost:numeric}>}  $data
     */
    public function create(User $user, array $data): Purchase
    {
        return DB::transaction(function () use ($user, $data) {
            $rows = collect($data['items']);

            // Kunci baris produk selama transaksi berjalan supaya stok konsisten
            $products = Product::whereIn('id', $rows->pluck('product_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $user->id,
                'purchase_number' => $this->generateNumber(),
                'total' => 0,
                'status' => Purchase::STATUS_COMPLETED,
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($rows as $row) {
                $product = $products->get((int) $row['product_id']);

                if (! $product) {
                    throw new BusinessRuleException('Salah satu produk tidak ditemukan atau sudah dihapus.');
                }

                $quantity = (int) $row['quantity'];
                $unitCost = round((float) $row['unit_cost'], 2);

                // Subtotal dan total dihitung server, bukan dipercaya dari browser
                $subtotal = round($quantity * $unitCost, 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // Stok bertambah hanya setelah item berhasil disimpan
                $product->increment('stock', $quantity);

                $total += $subtotal;
            }

            $purchase->update(['total' => round($total, 2)]);

            return $purchase;
        });
    }

    /**
     * Batalkan pembelian dan kembalikan stok seperti sebelum transaksi.
     * Ditolak jika stok sekarang tidak cukup untuk dikurangi (barang sudah terjual).
     */
    public function cancel(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            // Ambil ulang dengan kunci, lalu cek status lagi (mencegah pembatalan ganda)
            $locked = Purchase::whereKey($purchase->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== Purchase::STATUS_COMPLETED) {
                throw new BusinessRuleException('Pembelian ini sudah dibatalkan sebelumnya.');
            }

            $items = $locked->items()->get();

            $products = Product::withTrashed()
                ->whereIn('id', $items->pluck('product_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            // Periksa SEMUA item dulu sebelum mengurangi stok apa pun
            foreach ($items as $item) {
                $product = $products->get($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw new BusinessRuleException(
                        "Pembelian tidak bisa dibatalkan: stok \"{$product->name}\" sekarang {$product->stock}, "
                        . "kurang dari jumlah yang dibeli ({$item->quantity}). Sebagian barang kemungkinan sudah terjual."
                    );
                }
            }

            foreach ($items as $item) {
                // withTrashed: produk yang sudah dihapus (soft delete) tetap harus dikurangi stoknya
                Product::withTrashed()->whereKey($item->product_id)->decrement('stock', $item->quantity);
            }

            $locked->update(['status' => Purchase::STATUS_CANCELLED]);
        });
    }

    /** Nomor transaksi unik, contoh: PO-20261023-K7M2Q */
    private function generateNumber(): string
    {
        do {
            $number = 'PO-' . now()->format('Ymd') . '-' . Str::upper(Str::random(5));
        } while (Purchase::where('purchase_number', $number)->exists());

        return $number;
    }
}
