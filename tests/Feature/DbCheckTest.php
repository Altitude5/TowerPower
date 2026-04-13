<?php

use Illuminate\Support\Facades\DB;

test('it uses the correct database', function () {
    $db = DB::getDatabaseName();
    expect($db)->toBe('towertest');
});
