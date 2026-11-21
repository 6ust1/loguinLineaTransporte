<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Línea Occidente - Sistema de Rutas y Despacho</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #d97706;
            --bg: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
        }

        /* Alertas Ajax */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 16px;
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* PANTALLA DE AUTENTICACIÓN (LOGIN, REGISTRO, RECUPERAR) */
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .auth-card {
            background: var(--card-bg);
            width: 100%;
            max-width: 440px;
            padding: 35px 30px;
            border-radius: 14px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            border: 1px solid var(--border);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-header .logo-badge {
            display: inline-block;
            background: #eff6ff;
            color: var(--primary);
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 5px 12px;
            border-radius: 9999px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .auth-header h1 {
            font-size: 1.5rem;
            color: var(--text-main);
            margin-bottom: 4px;
        }

        .auth-header p {
            color: var(--text-muted);
            font-size: 0.88rem;
        }

        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 11px 16px;
            background-color: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
            text-align: center;
            text-decoration: none;
        }

        .btn:hover {
            background-color: var(--primary-hover);
        }

        .btn-success { background-color: var(--success); }
        .btn-success:hover { background-color: #15803d; }

        .btn-danger { background-color: var(--danger); }
        .btn-danger:hover { background-color: #b91c1c; }

        .btn-sm {
            width: auto;
            padding: 6px 12px;
            font-size: 0.82rem;
        }

        .auth-links {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .credential-tip {
            margin-top: 18px;
            padding: 10px 12px;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 0.78rem;
            color: var(--text-muted);
            border-left: 3px solid var(--primary);
        }

        /* PANTALLA DEL DASHBOARD CRUD */
        .navbar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--text-main);
        }

        .navbar-user {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.9rem;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-admin { background: #dbeafe; color: #1e40af; }
        .badge-user { background: #fef3c7; color: #92400e; }
        .badge-activo { background: #dcfce7; color: #15803d; }
        .badge-inactivo { background: #fee2e2; color: #b91c1c; }

        .dashboard-container {
            max-width: 1150px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            font-size: 0.9rem;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
        }

        .table-actions {
            display: flex;
            gap: 8px;
        }

        /* MODALES */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.2s ease;
        }

        .modal-card {
            background: #fff;
            width: 100%;
            max-width: 480px;
            padding: 26px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .modal-header h3 {
            font-size: 1.2rem;
            color: var(--text-main);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<?php if (empty($usuarioLogueado)): ?>
    <!-- ========================================== -->
    <!-- PANTALLAS DE AUTENTICACIÓN (LOGIN, REGISTRO, RECUPERACIÓN) -->
    <!-- ========================================== -->
    <div class="auth-container">
        <div class="auth-card">
            
            <div class="auth-header">
                <span class="logo-badge">Línea Occidente</span>
                <h1 id="authTitle">Acceso al Sistema</h1>
                <p id="authSubtitle">Conectado a Base de Datos MySQL (login.sql)</p>
            </div>

            <!-- Contenedor de Alertas AJAX -->
            <div id="authAlert" class="alert"></div>

            <!-- 1. FORMULARIO DE LOGIN -->
            <div id="sectionLogin" class="form-section active">
                <form id="formLogin" onsubmit="ejecutarLogin(event)">
                    <div class="form-group">
                        <label for="loginUser">Usuario o Correo Electrónico</label>
                        <input type="text" id="loginUser" class="form-control" placeholder="Ej: admin o admin@ejemplo.com" autofocus>
                    </div>

                    <div class="form-group">
                        <label for="loginPass">Contraseña</label>
                        <input type="password" id="loginPass" class="form-control" placeholder="••••••••">
                    </div>

                    <button type="submit" id="btnLogin" class="btn">Ingresar al Sistema</button>
                </form>

                <div class="auth-links">
                    <a onclick="cambiarSeccion('registro')">Crear nueva cuenta</a>
                    <a onclick="cambiarSeccion('recuperar')">¿Olvidó su contraseña?</a>
                </div>

                <div class="credential-tip">
                    <strong>Usuario inicial de la base de datos (login.sql):</strong><br>
                    • <strong>Usuario:</strong> <code>admin</code> (o <code>admin@ejemplo.com</code>)<br>
                    • <strong>Contraseña:</strong> <code>password</code> <em>(Rol: admin)</em>
                </div>
            </div>

            <!-- 2. FORMULARIO DE REGISTRO / CREAR USUARIO -->
            <div id="sectionRegistro" class="form-section">
                <form id="formRegistro" onsubmit="ejecutarRegistro(event)">
                    <div class="form-group">
                        <label for="regNombre">Nombre Completo</label>
                        <input type="text" id="regNombre" class="form-control" placeholder="Ej: María Rodríguez">
                    </div>

                    <div class="form-group">
                        <label for="regUser">Nombre de Usuario</label>
                        <input type="text" id="regUser" class="form-control" placeholder="Ej: mrodriguez">
                    </div>

                    <div class="form-group">
                        <label for="regCorreo">Correo Electrónico</label>
                        <input type="email" id="regCorreo" class="form-control" placeholder="Ej: maria@ejemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="regPass">Contraseña (Mínimo 6 caracteres)</label>
                        <input type="password" id="regPass" class="form-control" placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label for="regPassConfirm">Confirmar Contraseña</label>
                        <input type="password" id="regPassConfirm" class="form-control" placeholder="••••••••">
                    </div>

                    <button type="submit" id="btnRegistro" class="btn btn-success">Registrarse</button>
                </form>

                <div class="auth-links" style="justify-content: center;">
                    <a onclick="cambiarSeccion('login')">← Regresar a Iniciar Sesión</a>
                </div>
            </div>

            <!-- 3. FORMULARIO DE RECUPERAR CONTRASEÑA -->
            <div id="sectionRecuperar" class="form-section">
                <form id="formRecuperar" onsubmit="ejecutarRecuperar(event)">
                    <div class="form-group">
                        <label for="recIdentificador">Usuario o Correo Registrado</label>
                        <input type="text" id="recIdentificador" class="form-control" placeholder="Ej: admin o admin@ejemplo.com">
                    </div>

                    <div class="form-group">
                        <label for="recNewPass">Nueva Contraseña (Mín. 6 caracteres)</label>
                        <input type="password" id="recNewPass" class="form-control" placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label for="recNewPassConfirm">Confirmar Nueva Contraseña</label>
                        <input type="password" id="recNewPassConfirm" class="form-control" placeholder="••••••••">
                    </div>

                    <button type="submit" id="btnRecuperar" class="btn">Restablecer Contraseña</button>
                </form>

                <div class="auth-links" style="justify-content: center;">
                    <a onclick="cambiarSeccion('login')">← Regresar a Iniciar Sesión</a>
                </div>
            </div>

        </div>
    </div>

<?php else: ?>
    <!-- ========================================== -->
    <!-- PANTALLA DASHBOARD: CRUD DE USUARIOS (MYSQL) -->
    <!-- ========================================== -->
    <nav class="navbar">
        <div class="navbar-brand">
            🚌 Línea Occidente <span style="font-weight: 400; font-size: 0.85rem; color: var(--text-muted);">| Base de Datos MySQL</span>
        </div>
        <div class="navbar-user">
            <div>
                <strong><?= htmlspecialchars($usuarioLogueado['nombre_completo']) ?></strong>
                <span class="badge badge-admin"><?= htmlspecialchars($usuarioLogueado['nombre_rol'] ?? 'usuario') ?></span>
            </div>
            <a href="/logout" class="btn btn-danger btn-sm">Cerrar Sesión</a>
        </div>
    </nav>

    <div class="dashboard-container">
        
        <div class="dashboard-header">
            <div>
                <h2>CRUD de Usuarios</h2>
                <p style="color: var(--text-muted); font-size: 0.88rem;">Usuarios almacenados en la tabla <code>usuarios</code> de MySQL</p>
            </div>
            <button class="btn btn-sm btn-success" onclick="abrirModalCrear()">+ Nuevo Usuario</button>
        </div>

        <div id="crudAlert" class="alert"></div>

        <div class="dashboard-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre Completo</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaUsuarios">
                    <?php foreach ($listaUsuarios as $user): ?>
                        <tr id="row-<?= (int)$user['id_usuario'] ?>">
                            <td>#<?= (int)$user['id_usuario'] ?></td>
                            <td><strong><?= htmlspecialchars($user['nombre_completo']) ?></strong></td>
                            <td><?= htmlspecialchars($user['usuario']) ?></td>
                            <td><?= htmlspecialchars($user['correo']) ?></td>
                            <td>
                                <span class="badge <?= $user['nombre_rol'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                    <?= htmlspecialchars($user['nombre_rol']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= (int)$user['estado'] === 1 ? 'badge-activo' : 'badge-inactivo' ?>">
                                    <?= (int)$user['estado'] === 1 ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <small style="color: var(--text-muted);">
                                    <?= $user['ultimo_acceso'] ? htmlspecialchars($user['ultimo_acceso']) : 'Sin ingresos' ?>
                                </small>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class="btn btn-sm" onclick='abrirModalEditar(<?= json_encode($user) ?>)'>Editar</button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(<?= (int)$user['id_usuario'] ?>, '<?= htmlspecialchars($user['usuario']) ?>')">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: CREAR USUARIO (CRUD) -->
    <div id="modalCrear" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Nuevo Usuario (MySQL)</h3>
                <button class="modal-close" onclick="cerrarModal('modalCrear')">&times;</button>
            </div>
            <div id="alertModalCrear" class="alert"></div>
            <form onsubmit="ejecutarCrearCrud(event)">
                <div class="form-group">
                    <label for="crudNombre">Nombre Completo</label>
                    <input type="text" id="crudNombre" class="form-control" required placeholder="Ej: Pedro Sánchez">
                </div>
                <div class="form-group">
                    <label for="crudUser">Usuario</label>
                    <input type="text" id="crudUser" class="form-control" required placeholder="Ej: psanchez">
                </div>
                <div class="form-group">
                    <label for="crudCorreo">Correo Electrónico</label>
                    <input type="email" id="crudCorreo" class="form-control" required placeholder="Ej: pedro@ejemplo.com">
                </div>
                <div class="form-group">
                    <label for="crudRol">Rol</label>
                    <select id="crudRol" class="form-control">
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int)$rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre_rol']) ?> (<?= htmlspecialchars($rol['descripcion']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="crudEstado">Estado</label>
                    <select id="crudEstado" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="crudPass">Contraseña (Mín. 6 caracteres)</label>
                    <input type="password" id="crudPass" class="form-control" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-success">Guardar en Base de Datos</button>
            </form>
        </div>
    </div>

    <!-- MODAL: EDITAR USUARIO (CRUD) -->
    <div id="modalEditar" class="modal-overlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Editar Usuario #<span id="editIdSpan"></span></h3>
                <button class="modal-close" onclick="cerrarModal('modalEditar')">&times;</button>
            </div>
            <div id="alertModalEditar" class="alert"></div>
            <form onsubmit="ejecutarEditarCrud(event)">
                <input type="hidden" id="editIdUsuario">
                <div class="form-group">
                    <label for="editNombre">Nombre Completo</label>
                    <input type="text" id="editNombre" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editUser">Usuario</label>
                    <input type="text" id="editUser" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editCorreo">Correo Electrónico</label>
                    <input type="email" id="editCorreo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="editRol">Rol</label>
                    <select id="editRol" class="form-control">
                        <?php foreach ($roles as $rol): ?>
                            <option value="<?= (int)$rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre_rol']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editEstado">Estado</label>
                    <select id="editEstado" class="form-control">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editPass">Cambiar Contraseña (Opcional)</label>
                    <input type="password" id="editPass" class="form-control" placeholder="Dejar en blanco para conservar la actual">
                </div>
                <button type="submit" class="btn">Guardar Cambios</button>
            </form>
        </div>
    </div>

<?php endif; ?>

<!-- ========================================== -->
<!-- JAVASCRIPT: VALIDACIÓN (FRONTEND) Y AJAX   -->
<!-- ========================================== -->
<script>
    // Mostrar alertas visuales
    function mostrarAlerta(elementoId, mensaje, tipo = 'error') {
        const el = document.getElementById(elementoId);
        if (!el) return;
        el.className = `alert alert-${tipo}`;
        el.innerText = mensaje;
        el.style.display = 'block';
    }

    function ocultarAlerta(elementoId) {
        const el = document.getElementById(elementoId);
        if (el) el.style.display = 'none';
    }

    // Alternar entre Login, Registro y Recuperar Contraseña
    function cambiarSeccion(seccion) {
        ocultarAlerta('authAlert');
        document.querySelectorAll('.form-section').forEach(s => s.classList.remove('active'));

        const title = document.getElementById('authTitle');
        const subtitle = document.getElementById('authSubtitle');

        if (seccion === 'login') {
            document.getElementById('sectionLogin').classList.add('active');
            title.innerText = 'Acceso al Sistema';
            subtitle.innerText = 'Conectado a Base de Datos MySQL (login.sql)';
        } else if (seccion === 'registro') {
            document.getElementById('sectionRegistro').classList.add('active');
            title.innerText = 'Crear Cuenta';
            subtitle.innerText = 'Registra un nuevo usuario en la base de datos';
        } else if (seccion === 'recuperar') {
            document.getElementById('sectionRecuperar').classList.add('active');
            title.innerText = 'Recuperar Contraseña';
            subtitle.innerText = 'Restablece la clave de acceso de tu cuenta';
        }
    }

    // 1. AJAX: Iniciar Sesión (Login) con Validación Frontend
    async function ejecutarLogin(e) {
        e.preventDefault();
        ocultarAlerta('authAlert');

        const user = document.getElementById('loginUser').value.trim();
        const pass = document.getElementById('loginPass').value.trim();

        // Validación Frontend
        if (!user || !pass) {
            mostrarAlerta('authAlert', 'Por favor complete todos los campos.');
            return;
        }

        const btn = document.getElementById('btnLogin');
        btn.disabled = true;
        btn.innerText = 'Verificando en MySQL...';

        try {
            const formData = new FormData();
            formData.append('username', user);
            formData.append('password', pass);

            const res = await fetch('/login', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                mostrarAlerta('authAlert', data.message, 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                mostrarAlerta('authAlert', data.message || 'Error al iniciar sesión.');
            }
        } catch (err) {
            mostrarAlerta('authAlert', 'Error de conexión con el servidor.');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Ingresar al Sistema';
        }
    }

    // 2. AJAX: Crear Usuario / Registro con Validación Frontend
    async function ejecutarRegistro(e) {
        e.preventDefault();
        ocultarAlerta('authAlert');

        const nombre = document.getElementById('regNombre').value.trim();
        const usuario = document.getElementById('regUser').value.trim();
        const correo = document.getElementById('regCorreo').value.trim();
        const pass = document.getElementById('regPass').value;
        const passConfirm = document.getElementById('regPassConfirm').value;

        // Validación Frontend
        if (!nombre || !usuario || !correo || !pass || !passConfirm) {
            mostrarAlerta('authAlert', 'Todos los campos son obligatorios.');
            return;
        }

        if (usuario.length < 3) {
            mostrarAlerta('authAlert', 'El usuario debe tener al menos 3 caracteres.');
            return;
        }

        if (!correo.includes('@') || !correo.includes('.')) {
            mostrarAlerta('authAlert', 'Por favor ingrese un correo electrónico válido.');
            return;
        }

        if (pass.length < 6) {
            mostrarAlerta('authAlert', 'La contraseña debe tener mínimo 6 caracteres.');
            return;
        }

        if (pass !== passConfirm) {
            mostrarAlerta('authAlert', 'Las contraseñas no coinciden.');
            return;
        }

        const btn = document.getElementById('btnRegistro');
        btn.disabled = true;
        btn.innerText = 'Guardando en MySQL...';

        try {
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('usuario', usuario);
            formData.append('correo', correo);
            formData.append('id_rol', '2'); // 2 = rol usuario por defecto
            formData.append('password', pass);
            formData.append('confirm_password', passConfirm);

            const res = await fetch('/registro', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                mostrarAlerta('authAlert', data.message, 'success');
                document.getElementById('formRegistro').reset();
                setTimeout(() => cambiarSeccion('login'), 2000);
            } else {
                mostrarAlerta('authAlert', data.message || 'Error al registrar el usuario.');
            }
        } catch (err) {
            mostrarAlerta('authAlert', 'Error de conexión con el servidor.');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Registrarse';
        }
    }

    // 3. AJAX: Recuperar Contraseña con Validación Frontend
    async function ejecutarRecuperar(e) {
        e.preventDefault();
        ocultarAlerta('authAlert');

        const identificador = document.getElementById('recIdentificador').value.trim();
        const pass = document.getElementById('recNewPass').value;
        const passConfirm = document.getElementById('recNewPassConfirm').value;

        // Validación Frontend
        if (!identificador || !pass || !passConfirm) {
            mostrarAlerta('authAlert', 'Complete todos los campos de recuperación.');
            return;
        }

        if (pass.length < 6) {
            mostrarAlerta('authAlert', 'La nueva contraseña debe tener mínimo 6 caracteres.');
            return;
        }

        if (pass !== passConfirm) {
            mostrarAlerta('authAlert', 'Las contraseñas no coinciden.');
            return;
        }

        const btn = document.getElementById('btnRecuperar');
        btn.disabled = true;
        btn.innerText = 'Actualizando clave...';

        try {
            const formData = new FormData();
            formData.append('identificador', identificador);
            formData.append('nueva_password', pass);
            formData.append('confirm_nueva_password', passConfirm);

            const res = await fetch('/recuperar-password', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                mostrarAlerta('authAlert', data.message, 'success');
                document.getElementById('formRecuperar').reset();
                setTimeout(() => cambiarSeccion('login'), 2000);
            } else {
                mostrarAlerta('authAlert', data.message || 'Error al recuperar contraseña.');
            }
        } catch (err) {
            mostrarAlerta('authAlert', 'Error al conectar con el servidor.');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Restablecer Contraseña';
        }
    }

    // ==========================================
    // FUNCIONES DEL CRUD (DASHBOARD)
    // ==========================================
    function abrirModalCrear() {
        ocultarAlerta('alertModalCrear');
        document.getElementById('modalCrear').style.display = 'flex';
    }

    function abrirModalEditar(user) {
        ocultarAlerta('alertModalEditar');
        document.getElementById('editIdSpan').innerText = user.id_usuario;
        document.getElementById('editIdUsuario').value = user.id_usuario;
        document.getElementById('editNombre').value = user.nombre_completo;
        document.getElementById('editUser').value = user.usuario;
        document.getElementById('editCorreo').value = user.correo;
        document.getElementById('editRol').value = user.id_rol;
        document.getElementById('editEstado').value = user.estado;
        document.getElementById('editPass').value = '';
        document.getElementById('modalEditar').style.display = 'flex';
    }

    function cerrarModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Renderizar filas del CRUD tras actualizaciones AJAX
    function actualizarTablaUsuarios(usuarios) {
        const tbody = document.getElementById('tablaUsuarios');
        if (!tbody) return;

        tbody.innerHTML = '';
        usuarios.forEach(user => {
            const rolBadge = user.nombre_rol === 'admin' ? 'badge-admin' : 'badge-user';
            const estadoBadge = parseInt(user.estado) === 1 ? 'badge-activo' : 'badge-inactivo';
            const estadoTexto = parseInt(user.estado) === 1 ? 'Activo' : 'Inactivo';
            const ultimoAcceso = user.ultimo_acceso ? user.ultimo_acceso : 'Sin ingresos';

            const tr = document.createElement('tr');
            tr.id = `row-${user.id_usuario}`;
            tr.innerHTML = `
                <td>#${user.id_usuario}</td>
                <td><strong>${user.nombre_completo}</strong></td>
                <td>${user.usuario}</td>
                <td>${user.correo}</td>
                <td><span class="badge ${rolBadge}">${user.nombre_rol}</span></td>
                <td><span class="badge ${estadoBadge}">${estadoTexto}</span></td>
                <td><small style="color: var(--text-muted);">${ultimoAcceso}</small></td>
                <td>
                    <div class="table-actions">
                        <button class="btn btn-sm" onclick='abrirModalEditar(${JSON.stringify(user)})'>Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarUsuario(${user.id_usuario}, '${user.usuario}')">Eliminar</button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // AJAX: Crear Usuario desde el CRUD
    async function ejecutarCrearCrud(e) {
        e.preventDefault();
        const nombre = document.getElementById('crudNombre').value.trim();
        const user = document.getElementById('crudUser').value.trim();
        const correo = document.getElementById('crudCorreo').value.trim();
        const rol = document.getElementById('crudRol').value;
        const estado = document.getElementById('crudEstado').value;
        const pass = document.getElementById('crudPass').value;

        if (!nombre || !user || !correo || !pass) {
            mostrarAlerta('alertModalCrear', 'Complete todos los campos obligatorios.');
            return;
        }

        if (pass.length < 6) {
            mostrarAlerta('alertModalCrear', 'La contraseña debe tener mínimo 6 caracteres.');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('nombre', nombre);
            formData.append('usuario', user);
            formData.append('correo', correo);
            formData.append('id_rol', rol);
            formData.append('estado', estado);
            formData.append('password', pass);

            const res = await fetch('/usuarios/crear', { method: 'POST', body: formData });
            const data = await res.json();

            if (res.ok && data.status === 'success') {
                cerrarModal('modalCrear');
                mostrarAlerta('crudAlert', data.message, 'success');
                actualizarTablaUsuarios(data.usuarios);
            } else {
                mostrarAlerta('alertModalCrear', data.message || 'Error al crear usuario.');
            }
        } catch (err) {
            mostrarAlerta('alertModalCrear', 'Error de conexión con el servidor.');
        }
    }

    // AJAX: Editar Usuario desde el CRUD
    async function ejecutarEditarCrud(e) {
        e.preventDefault();
        const idUsuario = document.getElementById('editIdUsuario').value;
        const nombre = document.getElementById('editNombre').value.trim();
        const user = document.getElementById('editUser').value.trim();
        const correo = document.getElementById('editCorreo').value.trim();
        const rol = document.getElementById('editRol').value;
        const estado = document.getElementById('editEstado').value;
        const pass = document.getElementById('editPass').value;

        if (!nombre || !user || !correo) {
            mostrarAlerta('alertModalEditar', 'Nombre, usuario y correo son obligatorios.');
            return;
        }

        if (pass && pass.length < 6) {
            mostrarAlerta('alertModalEditar', 'La nueva contraseña debe tener mínimo 6 caracteres.');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('id_usuario', idUsuario);
            formData.append('nombre', nombre);
            formData.append('usuario', user);
            formData.append('correo', correo);
            formData.append('id_rol', rol);
            formData.append('estado', estado);
            if (pass) formData.append('nueva_password', pass);

            const res = await fetch('/usuarios/actualizar', { method: 'POST', body: formData });
            const data = await res.json();

            if (res.ok && data.status === 'success') {
                cerrarModal('modalEditar');
                mostrarAlerta('crudAlert', data.message, 'success');
                actualizarTablaUsuarios(data.usuarios);
            } else {
                mostrarAlerta('alertModalEditar', data.message || 'Error al actualizar usuario.');
            }
        } catch (err) {
            mostrarAlerta('alertModalEditar', 'Error de conexión con el servidor.');
        }
    }

    // AJAX: Eliminar Usuario desde el CRUD
    async function eliminarUsuario(idUsuario, username) {
        if (!confirm(`¿Está seguro de eliminar al usuario '${username}' (ID: ${idUsuario}) de la base de datos?`)) return;

        try {
            const formData = new FormData();
            formData.append('id_usuario', idUsuario);

            const res = await fetch('/usuarios/eliminar', { method: 'POST', body: formData });
            const data = await res.json();

            if (res.ok && data.status === 'success') {
                mostrarAlerta('crudAlert', data.message, 'success');
                actualizarTablaUsuarios(data.usuarios);
            } else {
                mostrarAlerta('crudAlert', data.message || 'Error al eliminar usuario.');
            }
        } catch (err) {
            mostrarAlerta('crudAlert', 'Error de comunicación con el servidor.');
        }
    }
</script>

</body>
</html>
