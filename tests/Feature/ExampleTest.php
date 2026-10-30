<?php

test('the home page redirects guests to the login page', function () {
    $this->get('/')->assertRedirect('/login');
});
