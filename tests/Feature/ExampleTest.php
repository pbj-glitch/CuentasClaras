<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_raiz_lleva_a_los_grupos(): void
    {
        $this->get('/')->assertRedirect(route('grupos.index'));
    }
}
