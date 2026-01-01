<?php

it('returns a successful response', function () {
    $response = $this->get('/');

    // App root redirects to login when unauthenticated.
    $response->assertRedirect();
});
