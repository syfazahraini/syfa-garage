<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Dilempar saat aturan bisnis dilanggar (contoh: stok tidak cukup untuk membatalkan pembelian).
 * Pesannya aman ditampilkan langsung ke user.
 */
class BusinessRuleException extends RuntimeException
{
}
