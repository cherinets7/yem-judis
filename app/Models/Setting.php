<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'department_name',
        'address',
        'phone',
        'email',
        'footer_note',
        'about_text',
        'services_text',
        'updated_by',
    ];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        $existing = static::query()->first();

        if ($existing !== null) {
            return $existing;
        }

        return static::query()->create([
            'department_name' => 'የየም ዞን ፍትህ መምሪያ',
            'address' => 'ሳጃ፣ የየም ዞን፣ ማዕከላዊ ኢትዮጵያ',
            'phone' => null,
            'email' => null,
            'footer_note' => 'የህዝብ መረጃ በር ብቻ። የጉዳይ ዝርዝር የሚታየው ከታተመ በኋላ ነው።',
            'about_text' => 'የየም ዞን ፍትህ መምሪያ ለዜጎች የፍትህ አገልግሎት፣ የጉዳይ ክትትል፣ የህግ መረጃ እና የሰነድ ምዝገባ በዲጂታል መንገድ ያቀርባል።',
            'services_text' => "የጉዳይ መከታተያ\nየህግ ማውጫና ማውረድ\nየህዝብ አስተያየትና ማስታወቂያ\nየውክልናና የውል ምዝገባ (ለሰራተኛ)\nየዕቅድና አፈጻጸም ሪፖርት (ለሰራተኛ)",
        ]);
    }
}
