<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

class PortalSettingSeeder extends Seeder
{
    public function run(): void
    {
        $setting = Setting::current();

        if (! filled($setting->about_text) || ! filled($setting->services_text)) {
            $setting->fill([
                'about_text' => $setting->about_text ?: 'የየም ዞን ፍትህ መምሪያ ለዜጎች የፍትህ አገልግሎት፣ የጉዳይ ክትትል፣ የህግ መረጃ እና የሰነድ ምዝገባ በዲጂታል መንገድ ያቀርባል።',
                'services_text' => $setting->services_text ?: "የጉዳይ መከታተያ\nየህግ ማውጫና ማውረድ\nየህዝብ አስተያየትና ማስታወቂያ\nየውክልናና የውል ምዝገባ (ለሰራተኛ)\nየዕቅድና አፈጻጸም ሪፖርት (ለሰራተኛ)",
            ])->save();
        }

        PermissionCatalog::seedDefaults();
    }
}
