<?php

test('simple browser', function () {
    visit('https://dev.towerpower.test/admin/login')->assertSee('Tower Power');
});
