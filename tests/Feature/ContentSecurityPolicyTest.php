<?php

namespace Tests\Feature;

use Tests\TestCase;

class ContentSecurityPolicyTest extends TestCase
{
    public function test_csp_headers_are_present(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('Content-Security-Policy');
    }
}
