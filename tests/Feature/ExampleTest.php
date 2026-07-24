<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response', function () {
    // La page d'accueil est désormais la boutique dynamique : elle interroge la base.
    $response = $this->get('/');

    $response->assertStatus(200);
});
