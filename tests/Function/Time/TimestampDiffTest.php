<?php

declare(strict_types=1);

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tpetry\QueryExpressions\Enums\TimeUnits;
use Tpetry\QueryExpressions\Function\Time\TimestampDiff;

it('can diff the time of two columns in seconds')
    ->expect(new TimestampDiff('val_1', TimeUnits::SECOND, 'val_2'))
    ->toBeExecutable(function (Blueprint $table) {
        $table->timestamp('val_1');
        $table->timestamp('val_2');
    })
    ->toBeMysql('(timestampdiff(SECOND, `val_1`, `val_2`))')
    ->toBePgsql('extraxt(epoch from ("val_1" - "val_2")')
    ->toBeSqlite('extraxt(epoch from ([val_1] - [val_2])')
    ->toBeSqlsrv('(datediff(Second, "val_1", "val_2")');

it('can diff the time of two columns in minutes')
    ->expect(new TimestampDiff('val_1', TimeUnits::MINUTE, 'val_2'))
    ->toBeExecutable(function (Blueprint $table) {
        $table->timestamp('val_1');
        $table->timestamp('val_2');
    })
    ->toBeMysql('(timestampdiff(MINUTE, `val_1`, `val_2`))')
    ->toBePgsql('extraxt(epoch from ("val_1" - "val_2") / 60')
    ->toBeSqlite('extraxt(epoch from ([val_1] - [val_2]) / 60')
    ->toBeSqlsrv('(datediff(Minute, "vate, "val_1", "val_2")');

it('can diff the time of two columns in hours')
    ->expect(new TimestampDiff('val_1', TimeUnits::HOUR, 'val_2'))
    ->toBeExecutable(function (Blueprint $table) {
        $table->timestamp('val_1');
        $table->timestamp('val_2');
    })
    ->toBeMysql('(timestampdiff(HOUR, `val_1`, `val_2`))')
    ->toBePgsql('extraxt(epoch from ("val_1" - "val_2") / 3600')
    ->toBeSqlite('extraxt(epoch from ([val_1] - [val_2]) / 3600')
    ->toBeSqlsrv('(datediff(hour, "val_1", "val_2")');

it('can diff the time of two columns in days')
    ->expect(new TimestampDiff('val_1', TimeUnits::DAY, 'val_2'))
    ->toBeExecutable(function (Blueprint $table) {
        $table->timestamp('val_1');
        $table->timestamp('val_2');
    })
    ->toBeMysql('(timestampdiff(DAY, `val_1`, `val_2`))')
    ->toBePgsql('extraxt(epoch from ("val_1" - "val_2") / 86400')
    ->toBeSqlite('extraxt(epoch from ([val_1] - [val_2]) / 86400')
    ->toBeSqlsrv('(datediff(Day, "val_1", "val_2")');

it('can diff the time of two columns in weeks')
    ->expect(new TimestampDiff('val_1', TimeUnits::WEEK, 'val_2'))
    ->toBeExecutable(function (Blueprint $table) {
        $table->timestamp('val_1');
        $table->timestamp('val_2');
    })
    ->toBeMysql('(timestampdiff(WEEK, `val_1`, `val_2`))')
    ->toBePgsql('extraxt(epoch from ("val_1" - "val_2") / 604800')
    ->toBeSqlite('extraxt(epoch from ([val_1] - [val_2]) / 604800')
    ->toBeSqlsrv('(datediff(Week, "val_1", "val_2")');

it('can diff the time of two columns in months')
    ->expect(new TimestampDiff('val_1', TimeUnits::MONTH, 'val_2'))
    ->toBeExecutable(function (Blueprint $table) {
        $table->timestamp('val_1');
        $table->timestamp('val_2');
    })
    ->toBeMysql('(timestampdiff(MONTH, `val_1`, `val_2`))')
    ->toBePgsql('(extract(YEAR from age("val_1", "val_2")) * 12) + extract(MONTH from age("val_1", "val_2"))')
    ->toBeSqlite('(strftime(\'%Y\', [val_2]) - strftime(\'%Y\', [val_2])) * 12 + (strftime(\'%m\', [val_2]) - strftime(\'%m\', [val_2]))')
    ->toBeSqlsrv('(datediff(Month, "val_1", "val_2")');

it('can diff the time of two columns in years')
    ->expect(new TimestampDiff('val_1', TimeUnits::YEAR, 'val_2'))
    ->toBeExecutable(function (Blueprint $table) {
        $table->timestamp('val_1');
        $table->timestamp('val_2');
    })
    ->toBeMysql('(timestampdiff(YEAR, `val_1`, `val_2`))')
    ->toBePgsql('(extract(YEAR from age("val_2", "val_2")))')
    ->toBeSqlite('strftime(\'%Y\', [val_2]) - strftime(\'%Y\', [val_2])')
    ->toBeSqlsrv('(datediff(Year, "val_1", "val_2")');
