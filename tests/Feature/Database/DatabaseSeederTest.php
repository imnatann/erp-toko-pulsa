<?php

namespace Tests\Feature\Database;

use App\Models\DigitalService;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_operational_basics(): void
    {
        $this->seed();

        $this->assertDatabaseHas('outlets', ['code' => 'OUT-MAIN']);
        $this->assertDatabaseHas('users', ['email' => 'operator@erp.local']);
        $this->assertDatabaseHas('digital_services', ['code' => 'DANA-20K']);

        $this->assertSame(1, Outlet::query()->count());
        $this->assertGreaterThanOrEqual(5, User::query()->count());
        $this->assertGreaterThanOrEqual(5, DigitalService::query()->count());
    }
}
