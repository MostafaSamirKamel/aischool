<?php

test('the application returns a redirect to login for guests', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin/login');
});
