<?php

namespace Unit\lucatume\WPBrowser\Utils;

use Codeception\Test\Unit;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Generator;
use lucatume\WPBrowser\Utils\Dates;

class DatesTest extends Unit
{

    public function immutableDataProvider(): Generator
    {
        $timezoneString = date_default_timezone_get();
        $timezone = new DateTimezone($timezoneString);
        $format = 'Y-m-d H:i:s';

        yield 'today 9am' => ['today 9am', (new DateTimeImmutable('today 9am', $timezone))->format($format)];
        yield 'yesterday 9am' => [
            'yesterday 9am',
            (new DateTimeImmutable('yesterday 9am', $timezone))->format($format)
        ];
        yield 'yesterday 9am timestamp' => [
            (new DateTimeImmutable('yesterday 9am', $timezone))->getTimestamp(),
            (new DateTimeImmutable('yesterday 9am', $timezone))->format($format)
        ];

        $dateTime = new DateTime('2022-09-30 08:00:00', new DateTimezone('America/New_York'));
        yield '2022-09-30 08:00:00 DateTime' => [$dateTime, $dateTime->format($format)];

        $dateTime = new DateTimeImmutable('2022-09-30 08:00:00', new DateTimezone('America/New_York'));
        yield '2022-09-30 08:00:00 DateTimeImmutable' => [$dateTime, $dateTime->format($format)];
    }

    /**
     * @dataProvider immutableDataProvider
     */
    public function test_immutable($date, string $expected): void
    {
        $built = Dates::immutable($date);

        $this->assertEquals($expected, $built->format('Y-m-d H:i:s'));
    }
}
