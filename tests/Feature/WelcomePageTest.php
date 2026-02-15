<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomePageTest extends TestCase
{
    public function test_welcome_page_loads(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Laravel');
    }
}
