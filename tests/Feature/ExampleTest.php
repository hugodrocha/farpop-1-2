<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_home_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_shows_a_friendly_error_when_mysql_is_unavailable(): void
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '1',
            'database.connections.mysql.database' => 'farpop',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);

        DB::purge('mysql');

        $response = $this->from('/login')->post('/login', [
            'usuario' => 'super.admin',
            'senha' => 'test-only-password',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors([
                'database' => 'Não foi possível acessar o banco de dados do FARPOP. Verifique se o MySQL está iniciado e tente novamente.',
            ]);
    }
}
