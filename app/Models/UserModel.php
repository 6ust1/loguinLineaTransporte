<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Retorna todos los usuarios junto con el nombre de su rol
     */
    public function obtenerUsuarios(): array
    {
        $sql = "SELECT 
                    u.id_usuario,
                    u.id_rol,
                    u.nombre_completo,
                    u.usuario,
                    u.correo,
                    u.estado,
                    u.intentos_fallidos,
                    u.ultimo_acceso,
                    u.creado_en,
                    r.nombre_rol
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.id_usuario ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Retorna todos los roles disponibles
     */
    public function obtenerRoles(): array
    {
        $stmt = $this->db->query("SELECT id_rol, nombre_rol, descripcion FROM roles ORDER BY id_rol ASC");
        return $stmt->fetchAll();
    }

    /**
     * Busca un usuario por username o correo
     */
    public function buscarPorIdentificador(string $identificador): ?array
    {
        $sql = "SELECT u.*, r.nombre_rol 
                FROM usuarios u
                JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.usuario = :term1 OR u.correo = :term2
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':term1' => $identificador, ':term2' => $identificador]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    /**
     * Autenticación con soporte de intentos fallidos, bloqueo y registro de auditoría (logs)
     */
    public function autenticar(string $identificador, string $password): array
    {
        $usuario = $this->buscarPorIdentificador($identificador);

        if (!$usuario) {
            $this->registrarLog(null, $identificador, 'login_fallido');
            return [
                'success' => false,
                'message' => 'El usuario o correo no existe.'
            ];
        }

        $idUsuario = (int)$usuario['id_usuario'];

        // Verificar si la cuenta se encuentra bloqueada temporalmente
        if (!empty($usuario['bloqueado_hasta'])) {
            $bloqueadoHasta = strtotime($usuario['bloqueado_hasta']);
            if ($bloqueadoHasta > time()) {
                $minutosRestantes = ceil(($bloqueadoHasta - time()) / 60);
                return [
                    'success' => false,
                    'message' => "Cuenta bloqueada temporalmente por intentos fallidos. Intente de nuevo en {$minutosRestantes} minuto(s)."
                ];
            } else {
                // Si el tiempo ya pasó, desbloquear
                $this->db->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = :id")
                         ->execute([':id' => $idUsuario]);
            }
        }

        // Verificar si el usuario está inactivo
        if ((int)$usuario['estado'] !== 1) {
            $this->registrarLog($idUsuario, $identificador, 'login_fallido');
            return [
                'success' => false,
                'message' => 'La cuenta está inactiva. Comuníquese con el administrador.'
            ];
        }

        // Validar contraseña con clave_hash
        if (!password_verify($password, $usuario['clave_hash'])) {
            $intentos = (int)$usuario['intentos_fallidos'] + 1;
            
            if ($intentos >= 5) {
                // Bloquear por 15 minutos al 5to intento
                $sqlBlock = "UPDATE usuarios 
                             SET intentos_fallidos = :intentos, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE) 
                             WHERE id_usuario = :id";
                $this->db->prepare($sqlBlock)->execute([':intentos' => $intentos, ':id' => $idUsuario]);
                $this->registrarLog($idUsuario, $identificador, 'login_fallido');

                return [
                    'success' => false,
                    'message' => 'Ha excedido el número de intentos permitidos. Cuenta bloqueada por 15 minutos.'
                ];
            } else {
                $this->db->prepare("UPDATE usuarios SET intentos_fallidos = :intentos WHERE id_usuario = :id")
                         ->execute([':intentos' => $intentos, ':id' => $idUsuario]);
                $this->registrarLog($idUsuario, $identificador, 'login_fallido');

                $intentosRestantes = 5 - $intentos;
                return [
                    'success' => false,
                    'message' => "Contraseña incorrecta. Le quedan {$intentosRestantes} intento(s) antes del bloqueo temporal."
                ];
            }
        }

        // Login exitoso: restablecer intentos, actualizar último acceso
        $sqlUpdate = "UPDATE usuarios 
                      SET intentos_fallidos = 0, bloqueado_hasta = NULL, ultimo_acceso = NOW() 
                      WHERE id_usuario = :id";
        $this->db->prepare($sqlUpdate)->execute([':id' => $idUsuario]);

        // Registrar log de auditoría
        $this->registrarLog($idUsuario, $identificador, 'login_exitoso');

        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'usuario' => $usuario
        ];
    }

    /**
     * Registra un nuevo usuario en la base de datos
     */
    public function registrarUsuario(string $nombre, string $usuario, string $correo, string $password, int $idRol = 2, int $estado = 1): array
    {
        // Validar si el usuario o correo ya existen
        $checkStmt = $this->db->prepare("SELECT usuario, correo FROM usuarios WHERE usuario = :u OR correo = :c LIMIT 1");
        $checkStmt->execute([':u' => $usuario, ':c' => $correo]);
        $existente = $checkStmt->fetch();

        if ($existente) {
            if (strcasecmp($existente['usuario'], $usuario) === 0) {
                return ['success' => false, 'message' => "El usuario '{$usuario}' ya se encuentra registrado."];
            }
            if (strcasecmp($existente['correo'], $correo) === 0) {
                return ['success' => false, 'message' => "El correo '{$correo}' ya se encuentra registrado."];
            }
        }

        $claveHash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (id_rol, nombre_completo, usuario, correo, clave_hash, estado) 
                VALUES (:rol, :nombre, :usuario, :correo, :clave, :estado)";

        $stmt = $this->db->prepare($sql);
        $ejecutado = $stmt->execute([
            ':rol' => $idRol,
            ':nombre' => $nombre,
            ':usuario' => $usuario,
            ':correo' => $correo,
            ':clave' => $claveHash,
            ':estado' => $estado
        ]);

        if (!$ejecutado) {
            return ['success' => false, 'message' => 'No se pudo registrar el usuario en la base de datos.'];
        }

        return ['success' => true, 'message' => 'Usuario registrado exitosamente.'];
    }

    /**
     * Actualiza la información de un usuario desde el CRUD
     */
    public function actualizarUsuario(int $idUsuario, string $nombre, string $usuario, string $correo, int $idRol, int $estado, ?string $nuevaPassword = null): array
    {
        // Verificar que usuario o correo no pertenezcan a otro usuario
        $checkStmt = $this->db->prepare("SELECT id_usuario, usuario, correo FROM usuarios WHERE (usuario = :u OR correo = :c) AND id_usuario != :id LIMIT 1");
        $checkStmt->execute([':u' => $usuario, ':c' => $correo, ':id' => $idUsuario]);
        $existente = $checkStmt->fetch();

        if ($existente) {
            if (strcasecmp($existente['usuario'], $usuario) === 0) {
                return ['success' => false, 'message' => "El nombre de usuario '{$usuario}' ya está en uso por otra persona."];
            }
            if (strcasecmp($existente['correo'], $correo) === 0) {
                return ['success' => false, 'message' => "El correo '{$correo}' ya está en uso por otra persona."];
            }
        }

        if (!empty($nuevaPassword)) {
            $claveHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios 
                    SET nombre_completo = :nombre, usuario = :usuario, correo = :correo, id_rol = :rol, estado = :estado, clave_hash = :clave 
                    WHERE id_usuario = :id";
            $params = [
                ':nombre' => $nombre,
                ':usuario' => $usuario,
                ':correo' => $correo,
                ':rol' => $idRol,
                ':estado' => $estado,
                ':clave' => $claveHash,
                ':id' => $idUsuario
            ];
        } else {
            $sql = "UPDATE usuarios 
                    SET nombre_completo = :nombre, usuario = :usuario, correo = :correo, id_rol = :rol, estado = :estado 
                    WHERE id_usuario = :id";
            $params = [
                ':nombre' => $nombre,
                ':usuario' => $usuario,
                ':correo' => $correo,
                ':rol' => $idRol,
                ':estado' => $estado,
                ':id' => $idUsuario
            ];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return ['success' => true, 'message' => 'Usuario actualizado correctamente.'];
    }

    /**
     * Restablecer contraseña mediante usuario o correo
     */
    public function actualizarPassword(string $identificador, string $nuevaPassword): array
    {
        $usuario = $this->buscarPorIdentificador($identificador);

        if (!$usuario) {
            return ['success' => false, 'message' => "No se encontró ningún usuario con los datos ingresados."];
        }

        $idUsuario = (int)$usuario['id_usuario'];
        $claveHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare("UPDATE usuarios SET clave_hash = :clave, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = :id");
        $stmt->execute([':clave' => $claveHash, ':id' => $idUsuario]);

        // Registrar log de cambio de clave
        $this->registrarLog($idUsuario, $identificador, 'cambio_clave');

        return ['success' => true, 'message' => 'Contraseña restablecida exitosamente. Ya puedes iniciar sesión.'];
    }

    /**
     * Elimina un usuario por su ID
     */
    public function eliminarUsuario(int $idUsuario): array
    {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id_usuario = :id");
        $stmt->execute([':id' => $idUsuario]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'El usuario no existe o ya fue eliminado.'];
        }

        return ['success' => true, 'message' => 'Usuario eliminado correctamente.'];
    }

    /**
     * Inserta un registro en la tabla logs_acceso
     */
    public function registrarLog(?int $idUsuario, string $usuarioIngresado, string $evento): void
    {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 255);

            $sql = "INSERT INTO logs_acceso (id_usuario, usuario_ingresado, evento, ip_address, user_agent) 
                    VALUES (:id, :user, :evento, :ip, :ua)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id' => $idUsuario,
                ':user' => substr($usuarioIngresado, 0, 50),
                ':evento' => $evento,
                ':ip' => $ip,
                ':ua' => $userAgent
            ]);
        } catch (\Exception $e) {
            // No interrumpir el flujo principal si un log falla
        }
    }
}
