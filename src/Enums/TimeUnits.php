<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Enums;

use InvalidArgumentException;

enum TimeUnits: string
{
    case SECOND = 'SECOND';
    case MINUTE = 'MINUTE';
    case HOUR = 'HOUR';
    case DAY = 'DAY';
    case WEEK = 'WEEK';
    case MONTH = 'MONTH';
    case YEAR = 'YEAR';

    public function toSeconds(): int
    {
        return match ($this) {
            self::SECOND => 1,
            self::MINUTE => 60,
            self::HOUR => 3600,
            self::DAY => 86400,
            self::WEEK => 604800,
            self::MONTH, self::YEAR => throw new InvalidArgumentException("Invalid method 'toSeconds' invoked for {$this->name} property."),
        };
    }

    public function toSqlServer(): string
    {
        return match ($this) {
            self::SECOND => 'Second',
            self::MINUTE => 'Minute',
            self::HOUR => 'hour',
            self::DAY => 'Day',
            self::WEEK => 'Week',
            self::MONTH => 'Month',
            self::YEAR => 'Year',
        };
    }
}
