# Ytron Hosting

> **Plataforma de Hosting de Servidores de Minecraft (PaaS) de Alto Rendimiento**

**Ytron Hosting** es un proyecto integral que abarca desde el desarrollo web hasta la arquitectura de sistemas empresariales. Nace con el objetivo de ofrecer un servicio de alojamiento de servidores de Minecraft sin sobreventa de recursos (overselling), garantizando el rendimiento mediante aislamiento de contenedores y aprovisionamiento automático.

---

## Características Principales

* **Aprovisionamiento Automático (Zero-Touch):** Creación instantánea de servidores de Minecraft (contenedores Docker) gestionados dinámicamente mediante playbooks de Ansible ejecutados desde el backend.
* **Alta Disponibilidad (HA):** Clúster de servidores web balanceados mediante **HAProxy**, garantizando tolerancia a fallos y continuidad del negocio.
* **Panel de Gestión Integral:** Interfaz segura con control de acceso basado en roles (RBAC) para clientes y administradores, con prevención de vulnerabilidades web (XSS, SQLi, IDOR).
* **Seguridad y Hardening:** Arquitectura de "Confianza Cero" segmentada mediante **pfSense**. Auditoría de sistemas validada con Lynis y UFW.
* **Monitorización Proactiva:** Telemetría corporativa en tiempo real para el consumo de CPU y RAM de los nodos mediante **Zabbix 7.0**.

---

## Stack Tecnológico

El proyecto integra tecnologías estándar de la industria, divididas en las siguientes capas:

### Desarrollo Web (Frontend & Backend)
* **Lenguajes:** PHP 8, HTML5, CSS3, JavaScript.
* **Librerías:** [SweetAlert2](https://sweetalert2.github.io/) para notificaciones asíncronas no intrusivas.
* **Base de Datos:** MariaDB (Sincronizada para acceso en alta disponibilidad).

### Infraestructura y Automatización
* **Sistemas Operativos:** Ubuntu Server 24.04 LTS, FreeBSD (pfSense).
* **Servidor Web:** Nginx (Reverse Proxy y FastCGI).
* **Infraestructura como Código (IaC):** Ansible.
* **Contenerización:** Docker (Uso de la imagen `itzg/minecraft-server`).

### Redes y Seguridad
* **Firewall y Enrutamiento:** pfSense (Segmentación estricta en redes MGMT, DMZ y GAME).
* **Balanceador de Carga:** HAProxy (Capa 7 - HTTP).
* **Monitorización:** Zabbix Server 7.0 y Zabbix Agents.
* **Seguridad:** Cifrado de contraseñas (Argon2/Bcrypt via PHP), UUIDs para identificación de servidores.

---

## Autores

Este proyecto ha sido diseñado, desarrollado y desplegado por:

* **Óscar Quirant García** - *Administración de Sistemas, Redes y Ciberseguridad.* * **David Pintado Díaz-Hellín** - *Desarrollo Web (Full-Stack) y DevOps.*

---

*Proyecto de Fin de Ciclo - ASIR (Administración de Sistemas Informáticos en Red)*
