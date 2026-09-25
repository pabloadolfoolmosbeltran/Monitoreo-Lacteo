<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserRouteCleanupTest extends TestCase
{
    // ¿Qué hace? Comprueba que no se exponga el detalle de usuario que no está implementado.
    public function test_unimplemented_user_detail_get_is_rejected(): void
    {
        $this->get('/usuarios/999')->assertStatus(405);
    }
}
