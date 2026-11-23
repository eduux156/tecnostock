# TecnoStock - Sistema de Inventario (MVP)

**Estudiante:** Eduardo Andrés Soto Vásquez  
**Carrera:** Ingeniería Civil en Computación, mención Informática  
**Asignatura:** Sistemas de Información  

---

## Video explicativo
* **Enlace al video (Google Drive):** [Ver demostración de TecnoStock](https://drive.google.com/file/d/123GaPbE1_5dB6hJdbVLFx5FGTdix_-zz/view?usp=sharing)
* *Nota: Se aplicó un ligero ajuste de velocidad en edición para respetar el límite de 5 minutos, por lo que el temporizador del grabador de pantalla muestra la duración original de captura. Si la vista previa de Drive carga en baja calidad, se recomienda seleccionar 1080p en el engranaje del reproductor o descargar el archivo.*

---

## Estructura de archivos del proyecto

| Archivo | Descripción y función |
| :--- | :--- |
| `tecnostock_db.sql` | Script SQL reproducible que crea las 4 tablas relacionales (`usuario`, `categoria`, `producto`, `movimiento`) con sus llaves primarias/foráneas y carga los datos iniciales. |
| `conexion.php` | Configura la conexión segura a MySQL utilizando **PDO** y manejo de excepciones. |
| `index.php` | Pantalla de inicio de sesión (login) con validación de credenciales contra la tabla `usuario`. |
| `panel.php` | Panel principal del inventario. Muestra los productos activos, permite buscar por nombre o código, destaca las **alertas de stock bajo** y centraliza los botones de acción. |
| `nuevo_producto.php` | Formulario y lógica para registrar nuevos productos, con validaciones en cliente (JavaScript) y servidor (PHP/PDO). |
| `editar_producto.php` | Permite modificar los datos de un producto existente (nombre, categoría, descripción, precio y stock mínimo) manteniendo su código único. |
| `desactivar_producto.php` | Ejecuta el **borrado lógico** del producto cambiando su columna `estado` de `1` a `0`, ocultándolo del panel sin perder el historial en la base de datos. |
| `movimiento.php` | Registra entradas y salidas de inventario. Utiliza transacciones SQL (`beginTransaction`, `commit`, `rollBack`) para actualizar el stock de forma íntegra y evitar stock negativo. |
| `logout.php` | Cierra la sesión activa del usuario de forma segura y redirige al login (`index.php`). |

---

## Instrucciones de instalación y ejecución local (XAMPP)

### 1. Descargar el proyecto
1. En este repositorio, hacer clic en el botón verde **`<> Code`** y seleccionar **Download ZIP**.
2. Descomprimir el archivo y mover la carpeta (renombrándola a `tecnostock`) dentro del directorio de XAMPP:  
   `C:\xampp\htdocs\tecnostock\`

### 2. Iniciar el servidor local
1. Abrir el **Panel de Control de XAMPP**.
2. Presionar **Start** en los módulos **Apache** y **MySQL**.

### 3. Importar la base de datos
1. Abrir el navegador e ingresar a `http://localhost/phpmyadmin/`.
2. Hacer clic en **Nueva** (menú izquierdo), crear una base de datos con el nombre exacto **`tecnostock_db`** y presionar **Crear**.
3. Con `tecnostock_db` seleccionada, ir a la pestaña **Importar**.
4. Seleccionar el archivo `tecnostock_db.sql` ubicado dentro de `C:\xampp\htdocs\tecnostock\` y presionar **Importar** al final de la página.

### 4. Ejecutar el sistema
1. Abrir una nueva pestaña en el navegador y entrar a:  
   `http://localhost/tecnostock/`
2. Iniciar sesión con las credenciales registradas en la tabla `usuario` de la base de datos.
Usuario: admin@tecnostock.cl
Contraseña: 123456
