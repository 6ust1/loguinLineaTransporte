<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

class HomeController extends Controller
{
    private UserModel $modelo;

    public function __construct()
    {
        $this->modelo = new UserModel();
    }

    /**
     * Vista principal:
     * Si hay sesión iniciada muestra el Dashboard con el CRUD de usuarios.
     * Si no, muestra la vista de Login con opciones de Registro y Recuperación.
     */
    public function index(): void
    {
        $usuarioLogueado = $_SESSION['usuario'] ?? null;
        $listaUsuarios = $this->modelo->obtenerUsuarios();

        $this->render('home/index', [
            'usuarioLogueado' => $usuarioLogueado,
            'listaUsuarios' => $listaUsuarios
        ]);
    }

    /**
     * Proceso de Login por AJAX con validación Backend
     */
    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validación Backend: campos obligatorios
        if (empty($username) || empty($password)) {
            $this->json([
                'status' => 'error',
                'message' => 'Por favor ingrese tanto el usuario como la contraseña.'
            ], 400);
        }

        // Validación con el Modelo
        $resultado = $this->modelo->autenticar($username, $password);

        if (!$resultado['success']) {
            $this->json([
                'status' => 'error',
                'message' => $resultado['message']
            ], 401);
        }

        // Guardar usuario en sesión (sin el hash de la contraseña por seguridad)
        $datosUsuario = $resultado['usuario'];
        unset($datosUsuario['password']);
        $_SESSION['usuario'] = $datosUsuario;

        $this->json([
            'status' => 'success',
            'message' => '¡Autenticación exitosa! Ingresando al sistema...',
            'usuario' => $datosUsuario
        ]);
    }

    /**
     * Crear Usuario / Registro por AJAX con validación Backend
     */
    public function registro(): void
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $rol = trim($_POST['rol'] ?? 'Oficinista');

        // Validación Backend
        if (empty($nombre) || empty($usuario) || empty($password)) {
            $this->json([
                'status' => 'error',
                'message' => 'Todos los campos son obligatorios.'
            ], 400);
        }

        if (strlen($usuario) < 3) {
            $this->json([
                'status' => 'error',
                'message' => 'El nombre de usuario debe tener al menos 3 caracteres.'
            ], 400);
        }

        if (strlen($password) < 6) {
            $this->json([
                'status' => 'error',
                'message' => 'La contraseña debe tener un mínimo de 6 caracteres.'
            ], 400);
        }

        if ($password !== $confirmPassword) {
            $this->json([
                'status' => 'error',
                'message' => 'Las contraseñas no coinciden.'
            ], 400);
        }

        $rolesPermitidos = ['Administrador', 'Despachador', 'Oficinista'];
        if (!in_array($rol, $rolesPermitidos, true)) {
            $rol = 'Oficinista';
        }

        $resultado = $this->modelo->registrarUsuario($nombre, $usuario, $password, $rol);

        if (!$resultado['success']) {
            $this->json([
                'status' => 'error',
                'message' => $resultado['message']
            ], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => '¡Usuario creado correctamente! Ahora puedes iniciar sesión.'
        ]);
    }

    /**
     * Recuperar / Restablecer Contraseña por AJAX con validación Backend
     */
    public function recuperarPassword(): void
    {
        $usuario = trim($_POST['usuario'] ?? '');
        $nuevaPassword = trim($_POST['nueva_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_nueva_password'] ?? '');

        // Validación Backend
        if (empty($usuario) || empty($nuevaPassword) || empty($confirmPassword)) {
            $this->json([
                'status' => 'error',
                'message' => 'Debe completar todos los campos.'
            ], 400);
        }

        if (strlen($nuevaPassword) < 6) {
            $this->json([
                'status' => 'error',
                'message' => 'La nueva contraseña debe tener al menos 6 caracteres.'
            ], 400);
        }

        if ($nuevaPassword !== $confirmPassword) {
            $this->json([
                'status' => 'error',
                'message' => 'Las contraseñas no coinciden.'
            ], 400);
        }

        $resultado = $this->modelo->actualizarPassword($usuario, $nuevaPassword);

        if (!$resultado['success']) {
            $this->json([
                'status' => 'error',
                'message' => $resultado['message']
            ], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => $resultado['message']
        ]);
    }

    /**
     * CRUD: Crear Usuario desde el panel de administración
     */
    public function crearUsuario(): void
    {
        $this->verificarAutenticado();

        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol = trim($_POST['rol'] ?? 'Oficinista');
        $estado = trim($_POST['estado'] ?? 'Activo');

        if (empty($nombre) || empty($usuario) || empty($password)) {
            $this->json([
                'status' => 'error',
                'message' => 'Nombre, usuario y contraseña son obligatorios.'
            ], 400);
        }

        if (strlen($password) < 6) {
            $this->json([
                'status' => 'error',
                'message' => 'La contraseña debe tener al menos 6 caracteres.'
            ], 400);
        }

        $resultado = $this->modelo->registrarUsuario($nombre, $usuario, $password, $rol, $estado);

        if (!$resultado['success']) {
            $this->json(['status' => 'error', 'message' => $resultado['message']], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => 'Usuario registrado exitosamente en el CRUD.',
            'usuarios' => $this->modelo->obtenerUsuarios()
        ]);
    }

    /**
     * CRUD: Actualizar Usuario desde el panel
     */
    public function actualizarUsuario(): void
    {
        $this->verificarAutenticado();

        $originalUsername = trim($_POST['original_username'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $rol = trim($_POST['rol'] ?? 'Oficinista');
        $estado = trim($_POST['estado'] ?? 'Activo');
        $nuevaPassword = !empty($_POST['nueva_password']) ? trim($_POST['nueva_password']) : null;

        if (empty($originalUsername) || empty($nombre)) {
            $this->json([
                'status' => 'error',
                'message' => 'El nombre y el usuario son obligatorios.'
            ], 400);
        }

        if ($nuevaPassword !== null && strlen($nuevaPassword) < 6) {
            $this->json([
                'status' => 'error',
                'message' => 'La nueva contraseña debe tener mínimo 6 caracteres.'
            ], 400);
        }

        $resultado = $this->modelo->actualizarUsuario($originalUsername, $nombre, $rol, $estado, $nuevaPassword);

        if (!$resultado['success']) {
            $this->json(['status' => 'error', 'message' => $resultado['message']], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => 'Usuario actualizado correctamente.',
            'usuarios' => $this->modelo->obtenerUsuarios()
        ]);
    }

    /**
     * CRUD: Eliminar Usuario desde el panel
     */
    public function eliminarUsuario(): void
    {
        $this->verificarAutenticado();

        $username = trim($_POST['username'] ?? '');

        if (empty($username)) {
            $this->json(['status' => 'error', 'message' => 'Usuario no especificado.'], 400);
        }

        // Impedir que un usuario se elimine a sí mismo
        if (strcasecmp($_SESSION['usuario']['username'] ?? '', $username) === 0) {
            $this->json([
                'status' => 'error',
                'message' => 'No puedes eliminar tu propia cuenta en sesión.'
            ], 400);
        }

        $resultado = $this->modelo->eliminarUsuario($username);

        if (!$resultado['success']) {
            $this->json(['status' => 'error', 'message' => $resultado['message']], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => $resultado['message'],
            'usuarios' => $this->modelo->obtenerUsuarios()
        ]);
    }

    /**
     * Cerrar Sesión
     */
    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['usuario']);
        session_destroy();

        header('Location: /');
        exit;
    }

    /**
     * Protección de rutas para usuarios autenticados
     */
    private function verificarAutenticado(): void
    {
        if (empty($_SESSION['usuario'])) {
            $this->json([
                'status' => 'error',
                'message' => 'Sesión expirada o no autenticado. Por favor inicie sesión.'
            ], 401);
        }
    }
}
