<?php

use App\Models\Usuario;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('aks:crear-admin', function () {
    $nombre = $this->ask('Nombre del administrador');
    $email = $this->ask('Correo electrónico');
    $password = $this->secret('Contraseña (mínimo 10 caracteres)');
    $validator = validator(compact('nombre', 'email', 'password'), ['nombre' => 'required|string|max:120', 'email' => 'required|email|max:190|unique:usuarios', 'password' => 'required|string|min:10|max:72']);
    if ($validator->fails()) {
        foreach ($validator->errors()->all() as $error) {
            $this->error($error);
        }

return 1;
    }
    Usuario::create(['nombre' => $nombre, 'email' => $email, 'perfil_id' => 1, 'password_hash' => Hash::make($password), 'activo' => true]);
    $this->info('Administrador creado. Ya puedes iniciar sesión.');

    return 0;
})->purpose('Crear el primer administrador sin publicar contraseñas');
