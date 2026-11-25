<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OpsHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_ops_check_passes_with_sync_queue_and_optional_mail(): void
    {
        Mail::fake();

        $this->artisan('justice:ops-check', ['--mail' => true])
            ->assertSuccessful();
    }
}
