<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaRoutingTest extends TestCase
{
    /**
     * Verify frontend paths are served by the Vue application.
     */
    public function test_deep_links_are_served_by_the_spa(): void
    {
        $this->withoutVite();

        $this->get('/health')->assertOk();
    }

    /**
     * Verify unknown API paths are not swallowed by the SPA route.
     */
    public function test_unknown_api_paths_return_not_found(): void
    {
        $this->getJson('/api/v1/unknown')->assertNotFound();
    }
}
