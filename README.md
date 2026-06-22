# ✂️ Brian Barber - Plataforma Web para Barbería

Este proyecto es una aplicación web completa desarrollada para la gestión integral de una barbería. Incluye una interfaz visual atractiva para los clientes y un sistema de backend para la administración interna de la base de datos (gestión de empleados y sistema de citas).

🌍 **Demo en vivo:** [Añade tu enlace aquí] *(Nota: El dominio actual estará activo hasta el 27/03/2027).*

## 🚀 Características Principales

* **Sistema de Reservas:** Permite a los clientes agendar citas.
* **Gestión de Empleados:** Base de datos estructurada para administrar el personal de la barbería.
* **Diseño Responsive:** Interfaz estilizada y adaptada con CSS.
* **Arquitectura de Datos:** Relaciones de tablas optimizadas para el correcto flujo de información.

## 🛠️ Tecnologías Utilizadas

* **Frontend:** HTML5, CSS3
* **Backend:** PHP
* **Base de Datos:** MySQL / SQL

## 📂 Estructura del Proyecto

El repositorio contiene todos los archivos necesarios para desplegar la web, incluyendo el diseño y la lógica de servidor:

* `/` - Archivos principales de la web (PHP/HTML).
* `/css/` - Hojas de estilo para el diseño de la interfaz.
* `BBDD.sql` - Archivo de volcado de la base de datos con la estructura de tablas y datos iniciales.

## ⚙️ Instalación y Despliegue Local

Si deseas ejecutar este proyecto en tu entorno local, sigue estos pasos:

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/tu-usuario/nombre-del-repo.git](https://github.com/tu-usuario/nombre-del-repo.git)
    ```
2.  **Preparar el entorno:** Asegúrate de tener instalado un servidor local como XAMPP, WAMP o LAMP.
3.  **Configurar la Base de Datos:**
    * Abre phpMyAdmin (o tu gestor de base de datos preferido).
    * Crea una base de datos nueva (por ejemplo, `brian_barber_db`).
    * Importa el archivo `BBDD.sql` incluido en este repositorio para generar las tablas y relaciones.
4.  **Conexión a la BBDD:** Revisa el archivo de conexión PHP (ej. `conexion.php`) y asegúrate de que las credenciales (`localhost`, `root`, `password`, `nombre_bd`) coinciden con las de tu entorno local.
5.  **Ejecutar:** Inicia los servicios de Apache y MySQL, y accede al proyecto desde tu navegador a través de `http://localhost/tu-carpeta-del-proyecto`.

## 👨‍💻 Autor

* **Iván Moreno** - Técnico Superior en Sistemas y Redes (ASIX)
* [Perfil de LinkedIn](https://www.linkedin.com/in/ivanmorenogalvez) | [Tu Portafolio/GitHub]
