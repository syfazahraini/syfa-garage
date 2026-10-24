<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /** Halaman awal tidak terbuka untuk tamu: diarahkan ke dashboard lalu ke login. */
    public function test_home_page_redirects_guest_to_login(): void
    {
        $this->get('/')->assertRedirect();
        $this->followingRedirects()->get('/')->assertOk()->assertSee('Masuk');
    }
}
