<?php

namespace App\Support;

use App\Enums\ProsecutorReportType;

/**
 * Section layout and the shared formula language for the three prosecutor
 * weekly matrices. PHP and the form JavaScript both evaluate these steps.
 */
class ProsecutorWeeklyMatrix
{
    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            ProsecutorReportType::Civil->value => 'የፍትሐ ብሔር ጉዳይ ሳምንታዊ ሪፖርት',
            ProsecutorReportType::Criminal->value => 'የወንጀል ጉዳይ ሳምንታዊ ሪፖርት',
            ProsecutorReportType::DocumentRegistration->value => 'የሰነድ ምዝገባ፣ ጠበቆችና ሲቪክ ማህበራት ሳምንታዊ ሪፖርት',
        ];
    }

    public static function title(string $type): string
    {
        return self::types()[$type] ?? $type;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function sections(string $type): array
    {
        return match ($type) {
            ProsecutorReportType::Civil->value => [
                self::section('civil_plaintiff', 'መንግስት ከሳሽ', 'judgment', [
                    ['code' => 'woreda', 'label' => 'የወረዳ ፍርድ ቤት'],
                    ['code' => 'high', 'label' => 'ከፍተኛ ፍርድ ቤት'],
                ]),
                self::section('civil_court', 'በፍ/ቤት ያሉ', 'judgment', [
                    ['code' => 'woreda', 'label' => 'የወረዳ ፍርድ ቤት'],
                    ['code' => 'high', 'label' => 'ከፍተኛ ፍርድ ቤት'],
                ]),
                self::section('civil_appeal', 'ይግባኝ', 'judgment', [
                    ['code' => 'high', 'label' => 'ከፍተኛ ፍርድ ቤት'],
                    ['code' => 'supreme', 'label' => 'ጠቅላይ ፍርድ ቤት'],
                ]),
                self::section('civil_execution', 'አፈፃፀም', 'completion', [
                    ['code' => 'money', 'label' => 'ገንዘብ'],
                    ['code' => 'land', 'label' => 'መሬት'],
                ]),
                self::section('civil_contract', 'ውል ምዝገባ', 'decision', [
                    ['code' => 'new', 'label' => 'አዲስ ውል'],
                    ['code' => 'change', 'label' => 'ማሻሻያ'],
                ], [
                    'accepted' => 'የተመዘገበ',
                    'rejected' => 'ያልተመዘገበ',
                ]),
                self::section('civil_advice', 'የህግ ምክር', 'advice', [
                    ['code' => 'institution', 'label' => 'ተቋም'],
                    ['code' => 'individual', 'label' => 'ግለሰብ'],
                ]),
                self::section('civil_complaint', 'አቤቱታ', 'advice', [
                    ['code' => 'written', 'label' => 'ጽሑፍ'],
                    ['code' => 'oral', 'label' => 'ቃል'],
                ], [
                    'answered' => 'የተመለሰ',
                ]),
            ],
            ProsecutorReportType::Criminal->value => [
                self::section('criminal_investigation', 'ም/መዝገብ ውሳኔ', 'investigation', [
                    ['code' => 'person', 'label' => 'በሰው ላይ'],
                    ['code' => 'property', 'label' => 'በንብረት ላይ'],
                    ['code' => 'other', 'label' => 'ሌላ ወንጀል'],
                ]),
                self::section('criminal_court', 'በፍ/ቤት ያሉ', 'judgment', [
                    ['code' => 'woreda', 'label' => 'የወረዳ ፍርድ ቤት'],
                    ['code' => 'high', 'label' => 'ከፍተኛ ፍርድ ቤት'],
                ]),
                self::section('criminal_appeal', 'ይግባኝ', 'judgment', [
                    ['code' => 'high', 'label' => 'ከፍተኛ ፍርድ ቤት'],
                    ['code' => 'supreme', 'label' => 'ጠቅላይ ፍርድ ቤት'],
                ]),
                self::section('criminal_detention', 'ማረፊያ ቤት 3x/ሳምንት & ማረሚያ ቤት 1x/ወር ሰብአዊ መብት ጉብኝት', 'visit', [
                    ['code' => 'police', 'label' => 'ማረፊያ ቤት (3 ጊዜ በሳምንት)'],
                    ['code' => 'prison', 'label' => 'ማረሚያ ቤት (1 ጊዜ በወር)'],
                ]),
                self::section('criminal_petition', 'አቤቱታና ህግ ምክር', 'advice', [
                    ['code' => 'complaint', 'label' => 'አቤቱታ'],
                    ['code' => 'advice', 'label' => 'የህግ ምክር'],
                ], [
                    'answered' => 'የተመለሰ/የተሰጠ',
                ]),
            ],
            ProsecutorReportType::DocumentRegistration->value => [
                self::section('doc_legality', 'የሰነዶች ህጋዊነት', 'decision', [
                    ['code' => 'poa', 'label' => 'ውክልና'],
                    ['code' => 'revocation', 'label' => 'የውክልና ስረዛ'],
                    ['code' => 'contract', 'label' => 'ውል'],
                ], [
                    'accepted' => 'ህጋዊ የሆነ',
                    'rejected' => 'ህጋዊ ያልሆነ',
                ]),
                self::section('doc_civic', 'ሲቪክ ማህበራት', 'decision', [
                    ['code' => 'new', 'label' => 'አዲስ ምዝገባ'],
                    ['code' => 'renewal', 'label' => 'እድሳት'],
                ], [
                    'accepted' => 'የተመዘገበ',
                    'rejected' => 'ያልተመዘገበ',
                ]),
                self::section('doc_professionals', 'ጠበቆችና ህግ ጉዳይ ፀሐፊዎች', 'license', [
                    ['code' => 'attorney', 'label' => 'ጠበቃ'],
                    ['code' => 'secretary', 'label' => 'የህግ ጸሐፊ'],
                ]),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, array<string, mixed>>  $posted
     * @return array{sections: list<array<string, mixed>>, summary: array<string, float|null>}
     */
    public static function calculate(string $type, array $posted): array
    {
        $sections = [];
        $weights = [
            'clearance_rate' => [0, 0],
            'win_rate' => [0, 0],
            'conviction_rate' => [0, 0],
        ];

        foreach (self::sections($type) as $section) {
            $profile = self::profiles()[$section['profile']];
            $rows = [];

            foreach ($section['rows'] as $row) {
                if ($row['total'] ?? false) {
                    continue;
                }

                $metrics = self::apply($profile, $posted[$section['code']][$row['code']] ?? []);
                $rows[] = ['code' => $row['code'], 'label' => $row['label'], 'total' => false, 'metrics' => $metrics];
                self::accumulate($weights, $profile, $metrics);
            }

            $sum = [];

            foreach (array_keys($profile['inputs']) as $key) {
                $sum[$key] = array_sum(array_map(
                    fn (array $row) => (int) ($row['metrics'][$key] ?? 0),
                    $rows,
                ));
            }

            $rows[] = [
                'code' => 'total',
                'label' => 'ድምር',
                'total' => true,
                'metrics' => self::apply($profile, $sum),
            ];

            $sections[] = [
                'code' => $section['code'],
                'title' => $section['title'],
                'profile' => $section['profile'],
                'columns' => self::columns($profile, $section['labels'] ?? []),
                'rows' => $rows,
            ];
        }

        $summary = [];

        foreach ($weights as $rate => [$numerator, $denominator]) {
            $summary[$rate] = $denominator > 0 ? round($numerator / $denominator * 100, 1) : null;
        }

        return ['sections' => $sections, 'summary' => $summary];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @param  array<string, mixed>  $inputs
     * @return array<string, int|float|null>
     */
    public static function apply(array $profile, array $inputs): array
    {
        $values = [];

        foreach (array_keys($profile['inputs']) as $key) {
            $values[$key] = max(0, (int) ($inputs[$key] ?? 0));
        }

        foreach ($profile['steps'] as $key => $expression) {
            $values[$key] = self::evalExpr($expression, $values);
        }

        return $values;
    }

    /**
     * @param  array<string, int|float|null>  $values
     */
    public static function evalExpr(mixed $expression, array $values): int|float|null
    {
        if (is_string($expression)) {
            $value = $values[$expression] ?? 0;

            return is_numeric($value) ? $value + 0 : 0;
        }

        if (is_int($expression) || is_float($expression)) {
            return $expression;
        }

        if (! is_array($expression)) {
            return 0;
        }

        if (isset($expression['add'])) {
            $sum = 0;

            foreach ($expression['add'] as $part) {
                $sum += (float) (self::evalExpr($part, $values) ?? 0);
            }

            return (int) $sum;
        }

        if (isset($expression['sub'])) {
            return (int) ((float) (self::evalExpr($expression['sub'][0], $values) ?? 0)
                - (float) (self::evalExpr($expression['sub'][1], $values) ?? 0));
        }

        if (isset($expression['percent'])) {
            $denominator = (float) (self::evalExpr($expression['percent'][1], $values) ?? 0);

            if ($denominator <= 0) {
                return null;
            }

            $numerator = (float) (self::evalExpr($expression['percent'][0], $values) ?? 0);

            return round($numerator / $denominator * 100, 1);
        }

        return 0;
    }

    /**
     * Definition the browser uses to repeat the same formulas.
     *
     * @return array<string, mixed>
     */
    public static function clientDefinition(string $type): array
    {
        $sections = [];

        foreach (self::sections($type) as $section) {
            $profile = self::profiles()[$section['profile']];
            $sections[] = [
                'code' => $section['code'],
                'rows' => array_merge(
                    array_map(fn (array $row) => $row['code'], $section['rows']),
                    ['total'],
                ),
                'inputs' => array_keys($profile['inputs']),
                'steps' => $profile['steps'],
                'rates' => $profile['rates'],
            ];
        }

        return ['sections' => $sections];
    }

    /**
     * @param  array<string, array{0: float, 1: float}>  $weights
     * @param  array<string, mixed>  $profile
     * @param  array<string, int|float|null>  $metrics
     */
    private static function accumulate(array &$weights, array $profile, array $metrics): void
    {
        foreach ($profile['rates'] as $rate => $parts) {
            $numerator = (float) (self::evalExpr($parts['num'], $metrics) ?? 0);
            $denominator = (float) (self::evalExpr($parts['den'], $metrics) ?? 0);
            $weights[$rate][0] += $numerator;
            $weights[$rate][1] += $denominator;
        }
    }

    /**
     * @param  list<array{code: string, label: string}>  $rows
     * @param  array<string, string>  $labels
     * @return array<string, mixed>
     */
    private static function section(string $code, string $title, string $profile, array $rows, array $labels = []): array
    {
        return [
            'code' => $code,
            'title' => $title,
            'profile' => $profile,
            'rows' => $rows,
            'labels' => $labels,
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @param  array<string, string>  $overrides
     * @return list<array{key: string, label: string, input: bool}>
     */
    private static function columns(array $profile, array $overrides): array
    {
        $columns = [];

        foreach ($profile['inputs'] as $key => $label) {
            $columns[] = ['key' => $key, 'label' => $overrides[$key] ?? $label, 'input' => true];
        }

        foreach ($profile['computed'] as $key => $label) {
            $columns[] = ['key' => $key, 'label' => $overrides[$key] ?? $label, 'input' => false];
        }

        return $columns;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function profiles(): array
    {
        return [
            'judgment' => [
                'inputs' => [
                    'brought_forward' => 'ቀሪ',
                    'new_intake' => 'አዲስ የገባ',
                    'won' => 'በድል የተወሰነ',
                    'lost' => 'በሽንፈት የተወሰነ',
                    'closed' => 'የተዘጋ',
                    'punished' => 'የተቀጣ',
                ],
                'computed' => [
                    'total' => 'ድምር',
                    'pending' => 'ያልተወሰነ',
                    'clearance_rate' => 'የማጥራት ምጣኔ',
                    'win_rate' => 'የመርታት ምጣኔ',
                    'conviction_rate' => 'የማስቀጣት ምጣኔ',
                ],
                'steps' => [
                    'total' => ['add' => ['brought_forward', 'new_intake']],
                    'pending' => ['sub' => ['total', ['add' => ['won', 'lost', 'closed']]]],
                    'clearance_rate' => ['percent' => [['add' => ['won', 'lost', 'closed']], 'total']],
                    'win_rate' => ['percent' => ['won', ['add' => ['won', 'lost']]]],
                    'conviction_rate' => ['percent' => ['punished', 'won']],
                ],
                'rates' => [
                    'clearance_rate' => ['num' => ['add' => ['won', 'lost', 'closed']], 'den' => 'total'],
                    'win_rate' => ['num' => 'won', 'den' => ['add' => ['won', 'lost']]],
                    'conviction_rate' => ['num' => 'punished', 'den' => 'won'],
                ],
            ],
            'investigation' => [
                'inputs' => [
                    'brought_forward' => 'ቀሪ',
                    'new_intake' => 'አዲስ የገባ',
                    'charged' => 'ክስ የተመሰረተ',
                    'closed' => 'የተዘጋ',
                    'returned' => 'የተመለሰ',
                ],
                'computed' => [
                    'total' => 'ድምር',
                    'pending' => 'ያልተወሰነ',
                    'clearance_rate' => 'የማጥራት ምጣኔ',
                    'win_rate' => 'የመርታት ምጣኔ',
                    'conviction_rate' => 'የማስቀጣት ምጣኔ',
                ],
                'steps' => [
                    'total' => ['add' => ['brought_forward', 'new_intake']],
                    'pending' => ['sub' => ['total', ['add' => ['charged', 'closed', 'returned']]]],
                    'clearance_rate' => ['percent' => [['add' => ['charged', 'closed', 'returned']], 'total']],
                    'win_rate' => ['percent' => ['charged', ['add' => ['charged', 'closed']]]],
                ],
                'rates' => [
                    'clearance_rate' => ['num' => ['add' => ['charged', 'closed', 'returned']], 'den' => 'total'],
                    'win_rate' => ['num' => 'charged', 'den' => ['add' => ['charged', 'closed']]],
                ],
            ],
            'completion' => [
                'inputs' => [
                    'brought_forward' => 'ቀሪ',
                    'new_intake' => 'አዲስ የገባ',
                    'completed' => 'የተፈጸመ',
                ],
                'computed' => [
                    'total' => 'ድምር',
                    'pending' => 'ያልተፈጸመ',
                    'clearance_rate' => 'የማጥራት ምጣኔ',
                    'win_rate' => 'የመርታት ምጣኔ',
                    'conviction_rate' => 'የማስቀጣት ምጣኔ',
                ],
                'steps' => [
                    'total' => ['add' => ['brought_forward', 'new_intake']],
                    'pending' => ['sub' => ['total', 'completed']],
                    'clearance_rate' => ['percent' => ['completed', 'total']],
                ],
                'rates' => [
                    'clearance_rate' => ['num' => 'completed', 'den' => 'total'],
                ],
            ],
            'decision' => [
                'inputs' => [
                    'received' => 'የቀረበ',
                    'accepted' => 'የተቀበለ',
                    'rejected' => 'ያልተቀበለ',
                ],
                'computed' => [
                    'pending' => 'ያልተወሰነ',
                    'clearance_rate' => 'የማጥራት ምጣኔ',
                    'win_rate' => 'የመርታት ምጣኔ',
                    'conviction_rate' => 'የማስቀጣት ምጣኔ',
                ],
                'steps' => [
                    'pending' => ['sub' => ['received', ['add' => ['accepted', 'rejected']]]],
                    'clearance_rate' => ['percent' => [['add' => ['accepted', 'rejected']], 'received']],
                    'win_rate' => ['percent' => ['accepted', ['add' => ['accepted', 'rejected']]]],
                ],
                'rates' => [
                    'clearance_rate' => ['num' => ['add' => ['accepted', 'rejected']], 'den' => 'received'],
                    'win_rate' => ['num' => 'accepted', 'den' => ['add' => ['accepted', 'rejected']]],
                ],
            ],
            'advice' => [
                'inputs' => [
                    'received' => 'የቀረበ',
                    'answered' => 'የተሰጠ',
                ],
                'computed' => [
                    'pending' => 'ያልተሰጠ',
                    'clearance_rate' => 'የማጥራት ምጣኔ',
                    'win_rate' => 'የመርታት ምጣኔ',
                    'conviction_rate' => 'የማስቀጣት ምጣኔ',
                ],
                'steps' => [
                    'pending' => ['sub' => ['received', 'answered']],
                    'clearance_rate' => ['percent' => ['answered', 'received']],
                ],
                'rates' => [
                    'clearance_rate' => ['num' => 'answered', 'den' => 'received'],
                ],
            ],
            'visit' => [
                'inputs' => [
                    'planned' => 'የታቀደ ጉብኝት',
                    'conducted' => 'የተደረገ ጉብኝት',
                    'seen' => 'የታዩ እስረኞች',
                    'complaints' => 'የቀረበ አቤቱታ',
                    'resolved' => 'የተፈታ አቤቱታ',
                ],
                'computed' => [
                    'pending' => 'ያልተፈታ',
                    'clearance_rate' => 'የማጥራት ምጣኔ',
                    'win_rate' => 'የመርታት ምጣኔ',
                    'conviction_rate' => 'የማስቀጣት ምጣኔ',
                ],
                'steps' => [
                    'pending' => ['sub' => ['complaints', 'resolved']],
                    'clearance_rate' => ['percent' => ['resolved', 'complaints']],
                    'win_rate' => ['percent' => ['conducted', 'planned']],
                ],
                'rates' => [
                    'clearance_rate' => ['num' => 'resolved', 'den' => 'complaints'],
                    'win_rate' => ['num' => 'conducted', 'den' => 'planned'],
                ],
            ],
            'license' => [
                'inputs' => [
                    'received' => 'የቀረበ ማመልከቻ',
                    'accepted' => 'ፈቃድ የተሰጠ',
                    'rejected' => 'ፈቃድ ያልተሰጠ',
                    'punished' => 'ፈቃድ የተሰረዘ',
                ],
                'computed' => [
                    'pending' => 'ያልተወሰነ',
                    'clearance_rate' => 'የማጥራት ምጣኔ',
                    'win_rate' => 'የመርታት ምጣኔ',
                    'conviction_rate' => 'የማስቀጣት ምጣኔ',
                ],
                'steps' => [
                    'pending' => ['sub' => ['received', ['add' => ['accepted', 'rejected']]]],
                    'clearance_rate' => ['percent' => [['add' => ['accepted', 'rejected']], 'received']],
                    'win_rate' => ['percent' => ['accepted', ['add' => ['accepted', 'rejected']]]],
                    'conviction_rate' => ['percent' => ['punished', 'accepted']],
                ],
                'rates' => [
                    'clearance_rate' => ['num' => ['add' => ['accepted', 'rejected']], 'den' => 'received'],
                    'win_rate' => ['num' => 'accepted', 'den' => ['add' => ['accepted', 'rejected']]],
                    'conviction_rate' => ['num' => 'punished', 'den' => 'accepted'],
                ],
            ],
        ];
    }
}
