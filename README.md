# Estructura MVC en PHP con Composer y MySQL (login.sql)

Proyecto con arquitectura **Modelo-Vista-Controlador (MVC)**, autocarga **PSR-4** vía Composer, comunicación asíncrona por **Ajax (Fetch API)** y persistencia en base de datos relacional **MySQL / MariaDB** (`login.sql`).

---

## 🗄️ Base de Datos (`login`)

Importada desde el archivo SQL `login.sql`:
- **`usuarios`**: Contiene `id_usuario`, `id_rol`, `nombre_completo`, `usuario`, `correo`, `clave_hash` (Bcrypt), `estado` (1=Activo, 0=Inactivo), `intentos_fallidos`, `bloqueado_hasta`, `ultimo_acceso`.
- **`roles`**: Contiene los roles del sistema (`1: admin`, `2: usuario`).
- **`logs_acceso`**: Registro de auditoría para eventos (`login_exitoso`, `login_fallido`, `logout`, `cambio_clave`, `recuperacion_solicitada`) con IP y User-Agent.
- **`recuperacion_contrasenas`**: Estructura para tokens de restablecimiento.

### 🔑 Credenciales iniciales (de `login.sql`):
- **Usuario:** `admin` (o `admin@ejemplo.com`)
- **Contraseña:** `password`
- **Rol:** `admin`

---

## 📁 Estructura del Proyecto

```text
Prueba/
├── app/
│   ├── Controllers/
│   │   └── HomeController.php    # Lógica de Login, Registro, Recuperación y CRUD
│   ├── Models/
│   │   └── UserModel.php         # Consultas preparadas con PDO hacia MySQL y auditoría
│   ├── Views/
│   │   └── home/
│   │       ├── index.php         # Vista interactiva (Auth + Dashboard CRUD)
│   │       └── about.php
│   └── Core/
│       ├── Controller.php        # Controlador base (render y json)
│       ├── Database.php          # Conexión Singleton PDO a MySQL
│       └── Router.php            # Enrutador HTTP (GET y POST)
├── config/
├── public/
│   ├── .htaccess                 # Reglas de reescritura para URLs amigables
│   └── index.php                 # Front Controller
├── composer.json                 # PSR-4 mapping: "App\\": "app/"
└── README.md
```

---

## ⚙️ Conexión a la Base de Datos

Definida en [app/Core/Database.php](file:///c:/Users/Gustavo%20Guerrero/Desktop/Prueba/app/Core/Database.php):
- **Host:** `127.0.0.1`
- **Puerto:** `3306`
- **Base de Datos:** `login`
- **Usuario:** `root`
- **Contraseña:** `""` (vacía por defecto en XAMPP)

---

## 🚀 Cómo ejecutar el proyecto

1. Asegúrate de tener **MySQL** activo en XAMPP.
2. Inicia el servidor de desarrollo de PHP:
   ```powershell
   php -S localhost:8000 -t public
   ```
3. Abre en tu navegador:
   👉 **[http://localhost:8000](http://localhost:8000)**
