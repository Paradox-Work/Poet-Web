<?php

test('guests can browse the dashboard', function () {
    $this
        ->get('/')
        ->assertOk();
});
