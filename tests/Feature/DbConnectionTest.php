<?php

use Illuminate\Support\Facades\DB;

it('uses the correct database', function () {
    expect(DB::connection()->getDatabaseName())->toBe('towertest');
    expect(DB::connection()->getDriverName())->toBe('pgsql');
});
