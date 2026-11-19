<?php

namespace App\Helpers;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Gregorian to Ethiopian calendar conversion for Africa/Addis_Ababa.
 *
 * The budget year runs from Hamle 1 through Sene 30 and is numbered by the
 * Ethiopian year in which that Sene 30 falls.
 */
class EthiopianDateHelper
{
    public const TIMEZONE = 'Africa/Addis_Ababa';

    /**
     * @var array<int, string>
     */
    public const MONTHS = [
        1 => 'መስከረም',
        2 => 'ጥቅምት',
        3 => 'ኅዳር',
        4 => 'ታኅሣሥ',
        5 => 'ጥር',
        6 => 'የካቲት',
        7 => 'መጋቢት',
        8 => 'ሚያዝያ',
        9 => 'ግንቦት',
        10 => 'ሰኔ',
        11 => 'ሐምሌ',
        12 => 'ነሐሴ',
        13 => 'ጳጉሜን',
    ];

    public static function now(): string
    {
        return self::fromCarbon(Carbon::now(self::TIMEZONE));
    }

    public static function fromCarbon(CarbonInterface $date): string
    {
        $parts = self::parts($date);

        return sprintf('%s %d ቀን %d ዓ.ም.', $parts['month_name'], $parts['day'], $parts['year']);
    }

    public static function currentBudgetYear(): int
    {
        $parts = self::parts(Carbon::now(self::TIMEZONE));

        return $parts['month'] >= 11 ? $parts['year'] + 1 : $parts['year'];
    }

    /**
     * @return array{year: int, month: int, day: int, month_name: string}
     */
    public static function parts(CarbonInterface $date): array
    {
        $local = Carbon::instance($date)->timezone(self::TIMEZONE);
        [$year, $month, $day] = self::gregorianToEthiopian(
            (int) $local->year,
            (int) $local->month,
            (int) $local->day,
        );

        return [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'month_name' => self::MONTHS[$month],
        ];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function gregorianToEthiopian(int $year, int $month, int $day): array
    {
        $jdn = self::gregorianToJdn($year, $month, $day);
        $era = 1723856;
        $r = ($jdn - $era) % 1461;
        $n = ($r % 365) + (365 * intdiv($r, 1460));
        $ethYear = (4 * intdiv($jdn - $era, 1461)) + intdiv($r, 365) - intdiv($r, 1460);
        $ethMonth = intdiv($n, 30) + 1;
        $ethDay = ($n % 30) + 1;

        return [$ethYear, $ethMonth, $ethDay];
    }

    private static function gregorianToJdn(int $year, int $month, int $day): int
    {
        $a = intdiv(14 - $month, 12);
        $y = $year + 4800 - $a;
        $m = $month + (12 * $a) - 3;

        return $day
            + intdiv((153 * $m) + 2, 5)
            + (365 * $y)
            + intdiv($y, 4)
            - intdiv($y, 100)
            + intdiv($y, 400)
            - 32045;
    }
}
