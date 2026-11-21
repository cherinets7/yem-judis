<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class PortalSettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::current();
        PermissionCatalog::seedDefaults();
    }
}
