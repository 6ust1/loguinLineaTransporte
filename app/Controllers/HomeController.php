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
        $listaUsuarios = [];
        $roles = [];

        if ($usuarioLogueado) {
            $listaUsuarios = $this->modelo->obtenerUsuarios();
            $roles = $this->modelo->obtenerRoles();
        }

        $this->render('home/index', [
            'usuarioLogueado' => $usuarioLogueado,
            'listaUsuarios' => $listaUsuarios,
            'roles' => $roles
        ]);
    }

    /**
     * Proceso de Login por AJAX conectado a MySQL con validación Backend
     */
    public function login(): void
    {
        $identificador = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // Validación Backend: campos obligatorios
        if (empty($identificador) || empty($password)) {
            $this->json([
                'status' => 'error',
                'message' => 'Por favor ingrese su usuario/correo y contraseña.'
            ], 400);
        }

        // Validación con el Modelo en MySQL
        $resultado = $this->modelo->autenticar($identificador, $password);

        if (!$resultado['success']) {
            $this->json([
                'status' => 'error',
                'message' => $resultado['message']
            ], 401);
        }

        // Guardar usuario en sesión (sin el hash de la clave por seguridad)
        $datosUsuario = $resultado['usuario'];
        unset($datosUsuario['clave_hash']);
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
        $correo = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');
        $idRol = (int)($_POST['id_rol'] ?? 2); // 2 = usuario estándar por defecto

        // Validación Backend
        if (empty($nombre) || empty($usuario) || empty($correo) || empty($password)) {
            $this->json([
                'status' => 'error',
                'message' => 'Todos los campos son obligatorios.'
            ], 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'status' => 'error',
                'message' => 'El correo electrónico ingresado no tiene un formato válido.'
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

        $resultado = $this->modelo->registrarUsuario($nombre, $usuario, $correo, $password, $idRol, 1);

        if (!$resultado['success']) {
            $this->json([
                'status' => 'error',
                'message' => $resultado['message']
            ], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => '¡Cuenta registrada con éxito en la base de datos! Ya puedes iniciar sesión.'
        ]);
    }

    /**
     * Recuperar / Restablecer Contraseña por AJAX con validación Backend
     */
    public function recuperarPassword(): void
    {
        $identificador = trim($_POST['identificador'] ?? '');
        $nuevaPassword = trim($_POST['nueva_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_nueva_password'] ?? '');

        // Validación Backend
        if (empty($identificador) || empty($nuevaPassword) || empty($confirmPassword)) {
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

        $resultado = $this->modelo->actualizarPassword($identificador, $nuevaPassword);

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
        $correo = trim($_POST['correo'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $idRol = (int)($_POST['id_rol'] ?? 2);
        $estado = (int)($_POST['estado'] ?? 1);

        if (empty($nombre) || empty($usuario) || empty($correo) || empty($password)) {
            $this->json([
                'status' => 'error',
                'message' => 'Todos los campos son obligatorios.'
            ], 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'status' => 'error',
                'message' => 'El correo electrónico no es válido.'
            ], 400);
        }

        if (strlen($password) < 6) {
            $this->json([
                'status' => 'error',
                'message' => 'La contraseña debe tener al menos 6 caracteres.'
            ], 400);
        }

        $resultado = $this->modelo->registrarUsuario($nombre, $usuario, $correo, $password, $idRol, $estado);

        if (!$resultado['success']) {
            $this->json(['status' => 'error', 'message' => $resultado['message']], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => 'Usuario guardado exitosamente en la base de datos.',
            'usuarios' => $this->modelo->obtenerUsuarios()
        ]);
    }

    /**
     * CRUD: Actualizar Usuario desde el panel
     */
    public function actualizarUsuario(): void
    {
        $this->verificarAutenticado();

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $idRol = (int)($_POST['id_rol'] ?? 2);
        $estado = (int)($_POST['estado'] ?? 1);
        $nuevaPassword = !empty($_POST['nueva_password']) ? trim($_POST['nueva_password']) : null;

        if ($idUsuario <= 0 || empty($nombre) || empty($usuario) || empty($correo)) {
            $this->json([
                'status' => 'error',
                'message' => 'Nombre, usuario y correo son obligatorios.'
            ], 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->json([
                'status' => 'error',
                'message' => 'El correo electrónico no tiene un formato válido.'
            ], 400);
        }

        if ($nuevaPassword !== null && strlen($nuevaPassword) < 6) {
            $this->json([
                'status' => 'error',
                'message' => 'La nueva contraseña debe tener mínimo 6 caracteres.'
            ], 400);
        }

        $resultado = $this->modelo->actualizarUsuario($idUsuario, $nombre, $usuario, $correo, $idRol, $estado, $nuevaPassword);

        if (!$resultado['success']) {
            $this->json(['status' => 'error', 'message' => $resultado['message']], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => 'Usuario actualizado correctamente en la base de datos.',
            'usuarios' => $this->modelo->obtenerUsuarios()
        ]);
    }

    /**
     * CRUD: Eliminar Usuario desde el panel
     */
    public function eliminarUsuario(): void
    {
        $this->verificarAutenticado();

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);

        if ($idUsuario <= 0) {
            $this->json(['status' => 'error', 'message' => 'ID de usuario inválido.'], 400);
        }

        // Impedir que un usuario elimine su propia cuenta activa
        if ((int)($_SESSION['usuario']['id_usuario'] ?? 0) === $idUsuario) {
            $this->json([
                'status' => 'error',
                'message' => 'No puedes eliminar tu propia cuenta en sesión.'
            ], 400);
        }

        $resultado = $this->modelo->eliminarUsuario($idUsuario);

        if (!$resultado['success']) {
            $this->json(['status' => 'error', 'message' => $resultado['message']], 400);
        }

        $this->json([
            'status' => 'success',
            'message' => 'Usuario eliminado correctamente de la base de datos.',
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

        if (!empty($_SESSION['usuario']['id_usuario'])) {
            $this->modelo->registrarLog((int)$_SESSION['usuario']['id_usuario'], $_SESSION['usuario']['usuario'], 'logout');
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
