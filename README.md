# Estructura MVC en PHP con Composer

Este proyecto implementa una arquitectura **Modelo-Vista-Controlador (MVC)** limpia y moderna utilizando **Composer** para el autocargado de clases bajo el estándar **PSR-4**.

---

## 📁 Estructura del Proyecto

```text
Prueba/
├── app/
│   ├── Controllers/         # Controladores (lógica de negocio y peticiones)
│   │   └── HomeController.php
│   ├── Models/              # Modelos (datos y lógica de persistencia/DB)
│   │   └── UserModel.php
│   ├── Views/               # Vistas (plantillas HTML/PHP)
│   │   └── home/
│   │       ├── index.php
│   │       └── about.php
│   └── Core/                # Clases base del núcleo
│       ├── Controller.php   # Controlador base (renderizado y respuestas JSON)
│       └── Router.php       # Enrutador para gestionar URLs
├── config/                  # Archivos de configuración
├── public/                  # Raíz pública del servidor web
│   ├── .htaccess            # Reglas de reescritura para URLs amigables
│   └── index.php            # Front Controller (punto de entrada)
├── vendor/                  # Dependencias y autoload de Composer (ignorado en git)
├── .gitignore
├── composer.json            # Configuración de Composer y PSR-4
└── README.md
```

---

## ⚙️ ¿Cómo funciona Composer en este MVC?

En el archivo [composer.json](file:///c:/Users/Gustavo%20Guerrero/Desktop/Prueba/composer.json) se definió el mapeo **PSR-4**:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    }
}
```

Esto significa que:
- Cualquier clase bajo el namespace `App\Controllers` se busca automáticamente en la carpeta `app/Controllers/`.
- Cualquier clase bajo `App\Models` se busca en `app/Models/`.
- Ya **no necesitas usar `require` ni `include`** manualmente para cada clase. Solo importas con `use App\Controllers\MiControlador;`.

---

## 🚀 Cómo ejecutar el proyecto

Abre una terminal en la carpeta del proyecto y ejecuta el servidor integrado de PHP:

```bash
php -S localhost:8000 -t public
```

Luego abre tu navegador en:
👉 [http://localhost:8000](http://localhost:8000)

Rutas disponibles de prueba:
- **`http://localhost:8000/`** : Página principal con datos renderizados desde el Modelo.
- **`http://localhost:8000/about`** : Página acerca de.
- **`http://localhost:8000/api/users`** : Ejemplo de respuesta JSON para APIs.

---

## ➕ Cómo añadir nuevas funcionalidades

### 1. Crear un nuevo Controlador
Crea un archivo en `app/Controllers/ProductoController.php`:
```php
<?php
namespace App\Controllers;

use App\Core\Controller;

class ProductoController extends Controller
{
    public function listar()
    {
        $this->render('productos/listar', ['productos' => []]);
    }
}
```

### 2. Registrar la ruta
En `public/index.php`:
```php
use App\Controllers\ProductoController;

$router->get('/productos', [ProductoController::class, 'listar']);
```

### 3. Actualizar el autoload si creas nuevas carpetas o namespaces
Cada vez que agregues o modifiques namespaces en `composer.json`, corre:
```bash
composer dump-autoload
```

