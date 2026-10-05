<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdministrator extends Command
{
    protected $signature = 'ins:create-admin {email} {--name=Administrador}';

    protected $description = 'Crea el administrador inicial y muestra una contraseña temporal aleatoria';

    public function handle(): int
    {
        $email = $this->argument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Correo inválido.');

            return self::FAILURE;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('El correo ya está registrado.');

            return self::FAILURE;
        }
        $password = bin2hex(random_bytes(10));
        User::create(['name' => $this->option('name'), 'email' => $email, 'password' => $password, 'role' => 'admin', 'active' => true]);
        $this->info('Administrador creado: '.$email);
        $this->line('Contraseña temporal: '.$password);

        return self::SUCCESS;
    }
}
