<?php

namespace Tests\Unit;

use App\Support\ProsecutorWeeklyMatrix;
use PHPUnit\Framework\TestCase;

class ProsecutorWeeklyMatrixTest extends TestCase
{
    public function test_judgment_rates_use_the_shared_steps(): void
    {
        $result = ProsecutorWeeklyMatrix::calculate('civil', [
            'civil_plaintiff' => [
                'woreda' => [
                    'brought_forward' => 10,
                    'new_intake' => 10,
                    'won' => 6,
                    'lost' => 2,
                    'closed' => 2,
                    'punished' => 3,
                ],
            ],
        ]);

        $row = $result['sections'][0]['rows'][0]['metrics'];

        $this->assertSame(20, $row['total']);
        $this->assertSame(10, $row['pending']);
        $this->assertSame(50.0, $row['clearance_rate']);
        $this->assertSame(75.0, $row['win_rate']);
        $this->assertSame(50.0, $row['conviction_rate']);
        $this->assertSame(50.0, $result['summary']['clearance_rate']);
        $this->assertSame(75.0, $result['summary']['win_rate']);
        $this->assertSame(50.0, $result['summary']['conviction_rate']);
    }

    public function test_investigation_has_no_conviction_rate(): void
    {
        $result = ProsecutorWeeklyMatrix::calculate('criminal', [
            'criminal_investigation' => [
                'person' => [
                    'brought_forward' => 4,
                    'new_intake' => 6,
                    'charged' => 3,
                    'closed' => 2,
                    'returned' => 1,
                ],
            ],
        ]);

        $row = $result['sections'][0]['rows'][0]['metrics'];

        $this->assertSame(10, $row['total']);
        $this->assertSame(4, $row['pending']);
        $this->assertSame(60.0, $row['clearance_rate']);
        $this->assertSame(60.0, $row['win_rate']);
        $this->assertArrayNotHasKey('conviction_rate', $row);
        $this->assertNull($result['summary']['conviction_rate']);
    }

    public function test_license_conviction_is_revoked_over_granted(): void
    {
        $result = ProsecutorWeeklyMatrix::calculate('document_registration', [
            'doc_professionals' => [
                'attorney' => [
                    'received' => 10,
                    'accepted' => 6,
                    'rejected' => 2,
                    'punished' => 1,
                ],
            ],
        ]);

        $sections = collect($result['sections'])->keyBy('code');
        $row = $sections['doc_professionals']['rows'][0]['metrics'];

        $this->assertSame(80.0, $row['clearance_rate']);
        $this->assertSame(75.0, $row['win_rate']);
        $this->assertSame(16.7, $row['conviction_rate']);
        $this->assertSame(['civil', 'criminal', 'document_registration'], array_keys(ProsecutorWeeklyMatrix::types()));
        $this->assertCount(7, ProsecutorWeeklyMatrix::sections('civil'));
        $this->assertCount(5, ProsecutorWeeklyMatrix::sections('criminal'));
        $this->assertCount(3, ProsecutorWeeklyMatrix::sections('document_registration'));
    }
}
