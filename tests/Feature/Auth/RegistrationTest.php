<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'Correct-Horse-9!Battery',
        'password_confirmation' => 'Correct-Horse-9!Battery',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});


test('weak passwords are rejected', function () {
    $response =
        $this->post(
            '/register',
            [
                'name' =>
                    'Weak Password User',

                'email' =>
                    'weak@example.com',

                'password' =>
                    'password',

                'password_confirmation' =>
                    'password',
            ]
        );

    $response
        ->assertSessionHasErrors(
            'password'
        );

    $this->assertGuest();
});
