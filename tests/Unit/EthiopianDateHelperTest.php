<?php

namespace Tests\Unit;

use App\Helpers\EthiopianDateHelper;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EthiopianDateHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_known_new_year_and_pagume_dates(): void
    {
        $this->assertSame(
            'መስከረም 1 ቀን 2017 ዓ.ም.',
            EthiopianDateHelper::fromCarbon(Carbon::parse('2024-09-11', EthiopianDateHelper::TIMEZONE)),
        );
        $this->assertSame(
            'መስከረም 1 ቀን 2016 ዓ.ም.',
            EthiopianDateHelper::fromCarbon(Carbon::parse('2023-09-12', EthiopianDateHelper::TIMEZONE)),
        );
        $this->assertSame(
            'ጳጉሜን 6 ቀን 2015 ዓ.ም.',
            EthiopianDateHelper::fromCarbon(Carbon::parse('2023-09-11', EthiopianDateHelper::TIMEZONE)),
        );
        $this->assertSame(
            'ኅዳር 10 ቀን 2019 ዓ.ም.',
            EthiopianDateHelper::fromCarbon(Carbon::parse('2026-11-19', EthiopianDateHelper::TIMEZONE)),
        );
    }

    public function test_budget_year_turns_on_hamle_first(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-07 09:00:00', EthiopianDateHelper::TIMEZONE));
        $this->assertSame('ሰኔ 30 ቀን 2018 ዓ.ም.', EthiopianDateHelper::now());
        $this->assertSame(2018, EthiopianDateHelper::currentBudgetYear());

        Carbon::setTestNow(Carbon::parse('2026-07-08 09:00:00', EthiopianDateHelper::TIMEZONE));
        $this->assertSame('ሐምሌ 1 ቀን 2018 ዓ.ም.', EthiopianDateHelper::now());
        $this->assertSame(2019, EthiopianDateHelper::currentBudgetYear());

        Carbon::setTestNow(Carbon::parse('2023-07-08 09:00:00', EthiopianDateHelper::TIMEZONE));
        $this->assertSame(2016, EthiopianDateHelper::currentBudgetYear());
    }
}
