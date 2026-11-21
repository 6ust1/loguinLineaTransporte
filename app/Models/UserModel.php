<?php

namespace App\Models;

class UserModel
{
    private string $archivo;

    public function __construct()
    {
        // Ubicación de la base de datos simulada en formato JSON
        $this->archivo = dirname(__DIR__, 2) . '/usuarios.json';
        $this->inicializarBaseDatos();
    }

    /**
     * Si no existe el archivo usuarios.json, crea un usuario Administrador por defecto
     */
    private function inicializarBaseDatos(): void
    {
        if (!file_exists($this->archivo)) {
            $usuarioInicial = [
                [
                    "nombre_completo" => "Administrador del Sistema",
                    "username" => "admin",
                    "password" => password_hash("admin123", PASSWORD_BCRYPT),
                    "rol" => "Administrador",
                    "estado" => "Activo"
                ],
                [
                    "nombre_completo" => "Carlos Despachador",
                    "username" => "carlos",
                    "password" => password_hash("carlos123", PASSWORD_BCRYPT),
                    "rol" => "Despachador",
                    "estado" => "Activo"
                ]
            ];
            file_put_contents($this->archivo, json_encode($usuarioInicial, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Retorna todos los usuarios
     */
    public function obtenerUsuarios(): array
    {
        if (file_exists($this->archivo)) {
            $contenido = file_get_contents($this->archivo);
            $datos = json_decode($contenido, true);
            return is_array($datos) ? $datos : [];
        }
        return [];
    }

    /**
     * Busca un usuario por su nombre de usuario
     */
    public function buscarPorUsername(string $username): ?array
    {
        $usuarios = $this->obtenerUsuarios();
        foreach ($usuarios as $usuario) {
            if (strcasecmp($usuario['username'], $username) === 0) {
                return $usuario;
            }
        }
        return null;
    }

    /**
     * Valida credenciales para el Login
     */
    public function autenticar(string $username, string $password): array
    {
        $usuario = $this->buscarPorUsername($username);

        if (!$usuario) {
            return [
                'success' => false,
                'message' => 'El usuario ingresado no existe.'
            ];
        }

        if (($usuario['estado'] ?? 'Activo') !== 'Activo') {
            return [
                'success' => false,
                'message' => 'El usuario se encuentra inactivo. Comuníquese con el administrador.'
            ];
        }

        if (!password_verify($password, $usuario['password'])) {
            return [
                'success' => false,
                'message' => 'Contraseña incorrecta.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'usuario' => $usuario
        ];
    }

    /**
     * Registra un nuevo usuario (Crear Usuario)
     */
    public function registrarUsuario(string $nombre, string $usuario, string $password, string $rol, string $estado = 'Activo'): array
    {
        if ($this->buscarPorUsername($usuario) !== null) {
            return [
                'success' => false,
                'message' => "El nombre de usuario '{$usuario}' ya está registrado. Por favor elija otro."
            ];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $nuevoUsuario = [
            "nombre_completo" => $nombre,
            "username" => $usuario,
            "password" => $passwordHash,
            "rol" => $rol,
            "estado" => $estado
        ];

        $usuarios = $this->obtenerUsuarios();
        $usuarios[] = $nuevoUsuario;

        $guardado = file_put_contents($this->archivo, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if ($guardado === false) {
            return [
                'success' => false,
                'message' => 'Error al guardar el usuario en el archivo de datos.'
            ];
        }

        return [
            'success' => true,
            'message' => 'Usuario creado exitosamente.',
            'usuario' => $nuevoUsuario
        ];
    }

    /**
     * Actualiza la información de un usuario (CRUD Update)
     */
    public function actualizarUsuario(string $originalUsername, string $nombre, string $rol, string $estado, ?string $nuevaPassword = null): array
    {
        $usuarios = $this->obtenerUsuarios();
        $encontrado = false;

        foreach ($usuarios as $indice => $u) {
            if (strcasecmp($u['username'], $originalUsername) === 0) {
                $usuarios[$indice]['nombre_completo'] = $nombre;
                $usuarios[$indice]['rol'] = $rol;
                $usuarios[$indice]['estado'] = $estado;

                if (!empty($nuevaPassword)) {
                    $usuarios[$indice]['password'] = password_hash($nuevaPassword, PASSWORD_BCRYPT);
                }

                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado para actualizar.'
            ];
        }

        file_put_contents($this->archivo, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'message' => 'Usuario actualizado correctamente.'
        ];
    }

    /**
     * Recuperar o restablecer contraseña
     */
    public function actualizarPassword(string $username, string $nuevaPassword): array
    {
        $usuarios = $this->obtenerUsuarios();
        $encontrado = false;

        foreach ($usuarios as $indice => $u) {
            if (strcasecmp($u['username'], $username) === 0) {
                $usuarios[$indice]['password'] = password_hash($nuevaPassword, PASSWORD_BCRYPT);
                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            return [
                'success' => false,
                'message' => "El usuario '{$username}' no fue encontrado."
            ];
        }

        file_put_contents($this->archivo, json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'message' => 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión.'
        ];
    }

    /**
     * Elimina un usuario (CRUD Delete)
     */
    public function eliminarUsuario(string $username): array
    {
        $usuarios = $this->obtenerUsuarios();
        $nuevosUsuarios = [];
        $encontrado = false;

        foreach ($usuarios as $u) {
            if (strcasecmp($u['username'], $username) === 0) {
                $encontrado = true;
                continue;
            }
            $nuevosUsuarios[] = $u;
        }

        if (!$encontrado) {
            return [
                'success' => false,
                'message' => 'El usuario no existe o ya fue eliminado.'
            ];
        }

        file_put_contents($this->archivo, json_encode($nuevosUsuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return [
            'success' => true,
            'message' => "Usuario '{$username}' eliminado correctamente."
        ];
    }
}
