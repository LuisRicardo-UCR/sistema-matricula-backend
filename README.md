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
4. [Configuración de Base de Datos y Migraciones](#4-configuración-de-base-datos-y-migraciones)
5. [Lógica de Negocio (Control de Cupos)](#5-lógica-de-negocio-control-de-cupos)
6. [Catálogo de Endpoints API RESTful](#6-catálogo-de-endpoints-api-restful)
7. [Guía de Pruebas en Postman](#7-guía-de-pruebas-en-postman)

---

## 1. Descripción del Proyecto
Este proyecto corresponde al backend del **Sistema de Matrícula Académica**, diseñado bajo una arquitectura limpia y desacoplada mediante una API RESTful construida con Symfony 7. 

El sistema permite:
* Administrar Estudiantes, Cursos y Matrículas de forma independiente.
* Garantizar el **control estricto de cupos máximos** por curso mediante lógica encapsulada en servicios (`MatriculaService`).
* Consultar listados detallados optimizados para vistas modales en el cliente (Angular).

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
git clone [https://github.com/TU-USUARIO-GITHUB/sistema-matricula-backend.git](https://github.com/TU-USUARIO-GITHUB/sistema-matricula-backend.git)
cd sistema-matricula-backend

2. Abrir en Visual Studio CodeBashcode .
3. Instalar DependenciasDescarga las librerías necesarias con Composer utilizando la terminal integrada de VS Code (Ctrl + ~):Bashcomposer install
4. Configuración de Base de Datos y Migraciones1. Configurar el archivo .envLocaliza el archivo .env en la raíz y ajusta la conexión de MySQL (ejemplo con XAMPP):Fragmento de códigoDATABASE_URL="mysql://root:@127.0.0.1:3306/sistema_matricula_db?serverVersion=8.0.32&charset=utf8mb4"
2. Crear la Base de DatosBashphp bin/console doctrine:database:create
3. Ejecutar MigracionesCrea las tablas correspondientes en la base de datos:Bashphp bin/console doctrine:migrations:migrate
5. Lógica de Negocio (Control de Cupos)La validación principal se encuentra aislada en el servicio src/Service/MatriculaService.php:Existencia: Verifica que el estudiante y el curso existan.Estado: Comprueba que el curso se encuentre 'Disponible'.Cupo Máximo: Cuenta las matrículas asociadas (count($curso->getMatriculas())) y las compara estrictamente con el cupoMaximo antes de permitir la persistencia.6. Catálogo de Endpoints API RESTfulMóduloMétodoEndpointDescripciónEstudiantesGET/api/estudiantesObtiene la lista de todos los estudiantes.EstudiantesPOST/api/estudiantesRegistra un nuevo estudiante.CursosGET/api/cursosObtiene la lista de cursos disponibles.CursosPOST/api/cursosRegistra un nuevo curso con su cupoMaximo.Modal / Pop-UpGET/api/cursos/{id}/estudiantesDevuelve los alumnos matriculados en un curso.MatrículasPOST/api/matriculasEjecuta el servicio de validación y matrícula.7. Guía de Pruebas en Postman1. Levantar el Servidor LocalBashsymfony server:start
(El servidor correrá en http://127.0.0.1:8000)2. Configurar PostmanSelecciona la pestaña Body -> raw -> JSON.3. Casos de Prueba ClaveCrear Curso (POST /api/cursos): Define un cupoMaximo bajo (ej. 1) para probar el límite.JSON{
  "codigo": "IF-6201",
  "nombre": "Informática Aplicada",
  "creditos": 4,
  "cupoMaximo": 1,
  "estado": "Disponible"
}
Registrar Matrícula (POST /api/matriculas):Primera vez: Retorna 201 Created.Al superar el cupo: Retorna 400 Bad Request indicando que se alcanzó el límite.JSON{
  "estudianteId": 1,
  "cursoId": 1
}
Desarrollado para fines académicos en la Universidad de Costa Rica (UCR).
---

### 🚀 ¿Qué debes hacer ahora?
1. Ve a la **raíz de tu proyecto** en VS Code.
2. Abre (o crea) el archivo **`README.md`** principal.
3. Borra todo lo que tenía antes y pega este bloque completo.
4. En tu terminal ejecuta:
   ```bash
   git add .
   git commit -m "docs: unificar toda la guia tecnica y de instalacion en un solo README principal"
   git push origin main
