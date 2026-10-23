# Guía práctica: Instalación y configuración de Symfony para Backend y conexión con Angular

## Desarrollo profesional del Sistema de Matrícula de Cursos

* **Curso:** IF-6201 Informática Aplicada a los Negocios
* **Nivel:** 3er año de Informática Empresarial
* **Framework Backend:** Symfony (PHP)
* **Framework Frontend:** Angular
* **Base de Datos:** MySQL
* **IDE/Editor:** Visual Studio Code
* **Nivel:** Intermedio
* **Modalidad:** Guía de implementación paso a paso
* **Duración estimada:** 1.5 horas

---

## 1. ¿Qué vamos a construir?

En esta guía configuraremos el entorno de desarrollo y la arquitectura del **Sistema de Matrícula Académica**. Construiremos un backend sólido utilizando **Symfony** y **Doctrine ORM** sobre **MySQL**, preparado para conectarse mediante una API RESTful a un cliente en **Angular**.

El sistema permitirá:
1. Administrar **Estudiantes**, **Cursos** y **Matrículas**.
2. Garantizar el **control de cupos máximos** por curso de manera automática.
3. Consultar la lista de estudiantes matriculados por curso mediante un endpoint optimizado para ser consumido por un **Modal (Pop-Up)** en el frontend.

---

## 2. Control de Versiones y Repositorio

Para clonar este repositorio del backend en tu entorno local, ejecuta el siguiente comando en tu terminal:

```bash
git clone [https://github.com/LuisRicardo-UCR/sistema-matricula-backend.git](https://github.com/LuisRicardo-UCR/sistema-matricula-backend.git)