<?php

namespace App\Support;

class Search
{
    /**
     * Bentuk pola LIKE yang aman: karakter khusus (% _ \) dari input user
     * di-escape supaya dianggap teks biasa, lalu dibungkus %...%.
     */
    public static function like(string $term): string
    {
        return '%' . addcslashes(trim($term), '%_\\') . '%';
    }
}
