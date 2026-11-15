<?php

// Solo las reglas que usa la app
return [
    'array' => 'El campo :attribute no es válido.',
    'between' => [
        'numeric' => ':attribute debe estar entre :min y :max.',
    ],
    'confirmed' => 'Las contraseñas no coinciden.',
    'date' => ':attribute no es una fecha válida.',
    'different' => ':attribute y :other tienen que ser distintos.',
    'email' => 'Escribe un correo válido.',
    'image' => 'El archivo tiene que ser una imagen.',
    'in' => 'El valor de :attribute no es válido.',
    'integer' => ':attribute tiene que ser un número entero.',
    'max' => [
        'array' => ':attribute no puede tener más de :max elementos.',
        'file' => 'La imagen no puede pesar más de :max KB.',
        'numeric' => ':attribute no puede ser mayor que :max.',
        'string' => ':attribute no puede tener más de :max caracteres.',
    ],
    'min' => [
        'array' => ':attribute tiene que tener al menos :min elemento.',
        'numeric' => ':attribute tiene que ser al menos :min.',
        'string' => ':attribute tiene que tener al menos :min caracteres.',
    ],
    'required' => 'Falta completar :attribute.',
    'unique' => 'Ese :attribute ya está registrado.',

    'attributes' => [
        'name' => 'el nombre',
        'email' => 'correo',
        'password' => 'la contraseña',
        'nombre' => 'el nombre',
        'emoji' => 'el ícono',
        'foto' => 'la foto',
        'frase' => 'la descripción del gasto',
        'descripcion' => 'la descripción',
        'categoria' => 'la categoría',
        'pagado_por' => 'quién pagó',
        'propina_pct' => 'la propina',
        'fecha' => 'la fecha',
        'items' => 'los ítems',
        'items.*.nombre' => 'el nombre del ítem',
        'items.*.cantidad' => 'la cantidad',
        'items.*.precio' => 'el precio del ítem',
        'items.*.miembros' => 'quién consumió el ítem',
        'codigo' => 'el código',
        'miembro' => 'quién eres en el grupo',
        'monto' => 'el monto',
    ],
];
