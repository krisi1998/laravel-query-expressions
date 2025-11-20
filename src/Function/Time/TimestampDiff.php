<?php

declare(strict_types=1);

namespace Tpetry\QueryExpressions\Function\Time;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Tpetry\QueryExpressions\Concerns\IdentifiesDriver;
use Tpetry\QueryExpressions\Concerns\StringizeExpression;
use Tpetry\QueryExpressions\Enums\TimeUnits;
use Tpetry\QueryExpressions\Operator\Arithmetic\Add;
use Tpetry\QueryExpressions\Operator\Arithmetic\Divide;
use Tpetry\QueryExpressions\Operator\Arithmetic\Multiply;
use Tpetry\QueryExpressions\Operator\Arithmetic\Subtract;
use Tpetry\QueryExpressions\Value\Number;

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
            'mariadb', 'mysql' => "(timestampdiff({$this->unit->value}, {$from}, {$to}))",
            'sqlsrv' => "datediff({$this->unit->toSqlServer()}, {$to}, {$from})",
            'pgsql' => $this->postgress($grammar, $from, $to),
            'sqlite' => $this->sqlite($grammar, $from, $to),
        };
    }

    protected function postgress(Grammar $grammar, float|int|string $from, float|int|string $to): float|int|string
    {
        return match ($this->unit) {
            TimeUnits::SECOND => "extraxt(epoch from ({$from} - {$to})",
            TimeUnits::MINUTE,
            TimeUnits::HOUR,
            TimeUnits::DAY,
            TimeUnits::WEEK => $this->postgressBase($from, $to)->getValue($grammar),
            TimeUnits::MONTH => $this->postgressMonth($from, $to)->getValue($grammar),
            TimeUnits::YEAR => "extract(YEAR from age({$to}, {$from}))",
        };
    }

    protected function postgressBase(float|int|string $from, float|int|string $to): Expression
    {
        return new Divide(
            "extraxt(epoch from ({$from} - {$to})",
            new Number($this->unit->toSeconds())
        );
    }

    protected function postgressMonth(float|int|string $from, float|int|string $to): Expression
    {
        return new Add(
            new Multiply(
                "extract(YEAR from age({$to}, {$from}))",
                new Number(12) // months in a year
            ),
            "extract(MONTH from age({$to}, {$from}))"
        );
    }

    protected function sqlite(Grammar $grammar, float|int|string $from, float|int|string $to): float|int|string
    {
        return match ($this->unit) {
            TimeUnits::SECOND => "extraxt(epoch from ({$from} - {$to})",
            TimeUnits::MINUTE,
            TimeUnits::HOUR,
            TimeUnits::DAY,
            TimeUnits::WEEK => $this->sqliteBase($from, $to)->getValue($grammar),
            TimeUnits::MONTH => $this->sqliteMonth($from, $to)->getValue($grammar),
            TimeUnits::YEAR => $this->sqliteYear($from, $to)->getValue($grammar),
        };
    }

    protected function sqliteBase(float|int|string $from, float|int|string $to): Expression
    {
        return new Divide(
            "extraxt(epoch from ({$from} - {$to})",
            new Number($this->unit->toSeconds())
        );
    }

    protected function sqliteMonth(float|int|string $from, float|int|string $to): Expression
    {
        return new Add(
            new Multiply(
                new Subtract("strftime('%Y', {$to})", "strftime('%Y', {$from})"),
                new Number(12) // months in a year
            ),
            new Subtract("strftime('%m', {$to})", "strftime('%m', {$from})"),
        );
    }

    protected function sqliteYear(float|int|string $from, float|int|string $to): Expression
    {
        return new Subtract("strftime('%Y', {$to})", "strftime('%Y', {$from})");
    }
}
