<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Only the messages used by the IP registry are translated. Missing keys
    | fall back to the framework's English translations.
    |
    */

    'confirmed' => 'La confirmación de :attribute no coincide.',
    'email' => 'El campo :attribute debe ser una dirección de correo válida.',
    'ip' => 'El campo :attribute debe ser una dirección IP válida.',
    'max' => [
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'required' => 'El campo :attribute es obligatorio.',
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'unique' => 'El valor del campo :attribute ya está en uso.',

    'attributes' => [
        'ip' => 'dirección IP',
        'hostname' => 'hostname',
        'model' => 'equipo o modelo',
        'name' => 'nombre',
        'office' => 'oficina',
        'staff' => 'personal',
        'position' => 'puesto',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
    ],

];
