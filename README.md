# 🎓 Sistema de Matrícula de Cursos - Backend (Symfony 7)

- **Curso:** IF-6201 Informática Aplicada a los Negocios
- **Nivel:** 3er año de Informática Empresarial (Universidad de Costa Rica - UCR)
- **Framework Backend:** Symfony 7 (PHP 8.2)
- **Base de Datos:** MySQL / Doctrine ORM
- **IDE/Editor:** Visual Studio Code
- **Plataforma:** Windows / Linux / macOS

---

## 📋 Tabla de Contenidos
1. [Descripción del Proyecto](#1-descripción-del-proyecto)
2. [Requisitos Previos (Desde Cero)](#2-requisitos-previos-desde-cero)
3. [Clonación y Dependencias](#3-clonación-y-dependencias)
4. [Configuración de Base de Datos y Migraciones](#4-configuración-de-base-de-datos-y-migraciones)
5. [Lógica de Negocio (Control de Cupos)](#5-lógica-de-negocio-control-de-cupos)
6. [Catálogo de Endpoints API RESTful](#6-catálogo-de-endpoints-api-restful)
7. [Guía de Pruebas en Postman](#7-guía-de-pruebas-en-postman)

---

## 1. Descripción del Proyecto
Este proyecto corresponde al backend del **Sistema de Matrícula Académica**, diseñado bajo una arquitectura limpia y desacoplada mediante una API RESTful construida con Symfony 7.

El sistema permite:
- Administrar Estudiantes, Cursos y Matrículas de forma independiente.
- Garantizar el **control estricto de cupos máximos** por curso mediante lógica encapsulada en servicios (`MatriculaService`).
- Consultar listados detallados optimizados para vistas modales en el cliente (Angular).

---

## 2. Requisitos Previos (Desde Cero)
Si estás configurando una computadora completamente nueva, debes instalar las siguientes herramientas antes de descargar el código:

1. **Git:** Control de versiones (`git --version`).
2. **Visual Studio Code:** Editor de código recomendado.
3. **PHP (Versión 8.2 o superior):** Agregado a las Variables de Entorno (PATH) del sistema (`php -v`).
4. **Composer:** Gestor de dependencias de PHP (`composer --version`).
5. **Symfony CLI:** Herramienta oficial para el servidor local (`symfony check:requirements`).
6. **MySQL / XAMPP:** Servidor de base de datos relacional.

---

## 3. Clonación y Dependencias

### 1. Clonar el Repositorio
Abre tu terminal y ejecuta:
```bash
git clone https://github.com/TU-USUARIO-GITHUB/sistema-matricula-backend.git
cd sistema-matricula-backend
```

### 2. Abrir en Visual Studio Code
```bash
code .
```

### 3. Instalar Dependencias
Descarga las librerías necesarias con Composer utilizando la terminal integrada de VS Code (`Ctrl + ~`):
```bash
composer install
```

---

## 4. Configuración de Base de Datos y Migraciones

### 1. Configurar el archivo `.env`
Localiza el archivo `.env` en la raíz y ajusta la conexión de MySQL (ejemplo con XAMPP):
```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/sistema_matricula_db?serverVersion=8.0.32&charset=utf8mb4"
```

### 2. Crear la Base de Datos
```bash
php bin/console doctrine:database:create
```

### 3. Ejecutar Migraciones
Crea las tablas correspondientes en la base de datos:
```bash
php bin/console doctrine:migrations:migrate
```

---

## 5. Lógica de Negocio (Control de Cupos)
La validación principal se encuentra aislada en el servicio `src/Service/MatriculaService.php`:
- **Existencia:** Verifica que el estudiante y el curso existan.
- **Estado:** Comprueba que el curso se encuentre `'Disponible'`.
- **Cupo Máximo:** Cuenta las matrículas asociadas (`count($curso->getMatriculas())`) y las compara estrictamente con el `cupoMaximo` antes de permitir la persistencia.

---

## 6. Catálogo de Endpoints API RESTful

| Módulo | Método | Endpoint | Descripción |
| :--- | :--- | :--- | :--- |
| **Estudiantes** | `GET` | `/api/estudiantes` | Obtiene la lista de todos los estudiantes. |
| **Estudiantes** | `POST` | `/api/estudiantes` | Registra un nuevo estudiante. |
| **Cursos** | `GET` | `/api/cursos` | Obtiene la lista de cursos disponibles. |
| **Cursos** | `POST` | `/api/cursos` | Registra un nuevo curso con su `cupoMaximo`. |
| **Modal / Pop-Up** | `GET` | `/api/cursos/{id}/estudiantes` | Devuelve los alumnos matriculados en un curso. |
| **Matrículas** | `POST` | `/api/matriculas` | Ejecuta el servicio de validación y matrícula. |

---

## 7. Guía de Pruebas en Postman

### 1. Levantar el Servidor Local
```bash
symfony server:start
```
*(El servidor correrá en `http://127.0.0.1:8000`)*

### 2. Configurar Postman
- Selecciona la pestaña **Body** -> **raw** -> **JSON**.

### 3. Casos de Prueba Clave

**Crear Curso (`POST /api/cursos`):**
```json
{
  "codigo": "IF-6201",
  "nombre": "Informática Aplicada",
  "creditos": 4,
  "cupoMaximo": 1,
  "estado": "Disponible"
}
```

**Registrar Matrícula (`POST /api/matriculas`):**
```json
{
  "estudianteId": 1,
  "cursoId": 1
}
```
- *Primera petición:* Retorna `201 Created`.
- *Al superar el cupo:* Retorna `400 Bad Request` indicando que se alcanzó el límite.

---
*Desarrollado para fines académicos en la Universidad de Costa Rica (UCR).*
