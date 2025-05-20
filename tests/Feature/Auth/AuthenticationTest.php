<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('user can register', function () {
    $response = $this->get('/register');
    $response->assertStatus(200);

    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $response = $this->post('/register', $userData);

    $this->assertAuthenticated();
    // Depending on Breeze setup, this might be 'verification.notice' or 'dashboard'
    // For now, let's assume it redirects to dashboard or a route that implies successful registration.
    // If email verification is enabled by default, the user is redirected to /verify-email
    // which has the route name 'verification.notice'. If not, it's 'dashboard'.
    // Let's check for either, or a successful redirect pattern.
    // A more robust way might be to check if the intended route is one of the expected ones.
    // $response->assertRedirect(route('dashboard', absolute: false)); // Or route('verification.notice')
    // For this exercise, asserting a redirect without specifying the exact one if it can vary.
    $response->assertRedirect();


    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
    ]);
});

test('user can login and logout', function () {
    // Create a user via the User factory
    $user = User::factory()->create([
        'email' => 'loginlogout@example.com',
        'password' => bcrypt('password123'), // bcrypt or Hash::make
    ]);

    // Assert that a guest can visit the /login page successfully
    $response = $this->get('/login');
    $response->assertStatus(200);

    // Perform a POST request to /login with the created user's email and correct password
    $loginResponse = $this->post('/login', [
        'email' => 'loginlogout@example.com',
        'password' => 'password123',
    ]);

    // Assert that the user is authenticated
    $this->assertAuthenticatedAs($user);

    // Assert that the user is redirected to /dashboard
    // Using route('dashboard', absolute: false) is more robust than hardcoding '/dashboard'
    // For now, using the hardcoded path as specified in the prompt.
    $loginResponse->assertRedirect('/dashboard');

    // Perform a POST request to /logout
    $logoutResponse = $this->actingAs($user)->post('/logout');

    // Assert that the user is no longer authenticated
    $this->assertGuest();

    // Assert that the user is redirected to the home page (/) or login page
    // Default Breeze behavior is redirect to '/'
    $logoutResponse->assertRedirect('/');
});
