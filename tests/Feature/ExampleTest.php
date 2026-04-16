<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Vérifie que l'endpoint de santé de Laravel répond bien.
     */
    public function test_l_endpoint_de_sante_repond_correctement(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}
