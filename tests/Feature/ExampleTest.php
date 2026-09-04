<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Halaman "/" sengaja mengarahkan ke halaman login (lihat routes/web.php),
     * bukan menampilkan halaman langsung -- jadi yang benar diuji di sini
     * adalah redirect-nya, bukan status 200.
     */
    public function test_the_application_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
