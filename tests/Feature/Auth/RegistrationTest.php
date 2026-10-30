<?php

test('registration is disabled', function () {
    $this->get('/register')->assertNotFound();
});
