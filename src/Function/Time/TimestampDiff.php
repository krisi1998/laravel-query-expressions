<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Function\Time;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Tpetry\QueryExpressions\Concerns\IdentifiesDriver;
use Tpetry\QueryExpressions\Concerns\StringizeExpression;
use Tpetry\QueryExpressions\Enums\TimeUnits;

class TimestampDiff implements Expression
{
    use IdentifiesDriver;
    use StringizeExpression;

    public function __construct(
        private readonly string|Expression $from,
        private readonly TimeUnits $unit,
        private readonly string|Expression|null $to = null,
    ) {
        //
    }

    public function getValue(Grammar $grammar)
    {
        $from = $this->stringize($grammar, $this->from);
        $to = $this->stringize($grammar, $this->to ?? new Now);

        return match ($this->identify($grammar)) {
            'mariadb', 'mysql' => "(timestampdiff({$this->unit->toMysql()}, {$from}, {$to}))",
            'sqlsrv' => "datediff({$this->unit->toSqlServer()}, {$to}, {$from})",
            'pgsql' => $this->postgress($from, $to),
            'sqlite' => $this->sqlite($from, $to),
        };
    }

    protected function postgress(float|int|string $from, float|int|string $to): float|int|string
    {
        return match ($this->unit) {
            TimeUnits::SECOND => "extract(epoch from ({$from} - {$to})",
            TimeUnits::MINUTE,
            TimeUnits::HOUR,
            TimeUnits::DAY,
            TimeUnits::WEEK => "extract(epoch from ({$from} - {$to}) / {$this->unit->toSeconds()}",
            TimeUnits::MONTH => "(extract(YEAR from age({$to}, {$from})) * 12) + extract(MONTH from age({$to}, {$from}))",
            TimeUnits::YEAR => "extract(YEAR from age({$to}, {$from}))",
        };
    }

    protected function sqlite(float|int|string $from, float|int|string $to): float|int|string
    {
        return match ($this->unit) {
            TimeUnits::SECOND => "extract(epoch from ({$from} - {$to})",
            TimeUnits::MINUTE,
            TimeUnits::HOUR,
            TimeUnits::DAY,
            TimeUnits::WEEK => "extract(epoch from ({$from} - {$to}) / {$this->unit->toSeconds()}",
            TimeUnits::MONTH => with(
                value: ['from' => $from, 'to' => $to],
                callback: function ($value) {
                    $yearsDiff = "strftime('%Y', {$value['to']}) - strftime('%Y', {$value['from']})";
                    $monthsDiff = "strftime('%m', {$value['to']}) - strftime('%m', {$value['from']})";

                    return "({$yearsDiff} * 12) + {$monthsDiff}";
                }),
            TimeUnits::YEAR => "strftime('%Y', {$to}) - strftime('%Y', {$from})",
        };
    }
}
