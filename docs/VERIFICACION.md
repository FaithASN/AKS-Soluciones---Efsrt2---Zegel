# Verificación de la entrega

Revisión realizada el **25 de noviembre de 2026 (Lima)**, con PHP 8.2.12, Laravel 12.74.0 y **MySQL Community Server 8.4.13**. Los registros utilizados son ficticios.

## Pruebas automatizadas

`php artisan test --compact`: **17 pruebas aprobadas, 118 comprobaciones**, sin fallos. Suite ejecutada en la base aislada `aks_soluciones_test`.

Se comprobaron páginas públicas, autenticación activa/inactiva, cierre de sesión, renderizado de los módulos del administrador, permisos por perfil, registro transaccional, propietario del equipo, FK compuesta, rechazo de saltos/duplicación de estados, fecha de entrega, cálculo de cotizaciones en servidor, conservación de precios, rechazo de servicios inactivos y cantidades inválidas, privacidad del seguimiento, gestión de clientes/servicios/usuarios, protección de la cuenta administradora, registro de cobros, filtro por fecha de cobro y PDF de cotización/reporte.

`composer validate --no-check-publish`: válido. Plantillas compiladas con `php artisan view:cache`. JavaScript comprobado con `node --check`.

## MySQL y archivo SQL

- Migración y catálogos ejecutados en MySQL real.
- SQL importado en una base nueva de prueba: 10 tablas del negocio + migrations, con 14 relaciones FK.
- El archivo SQL entregado no contiene clientes, usuarios, contraseñas ni credenciales del entorno de revisión.

## Revisión en navegador

- Inicio y acceso; ingreso del administrador con cuenta ficticia.
- Panel y lista de pedidos con datos consultados desde MySQL.
- Registro de cliente/equipo/pedido desde el formulario.
- Cambio a En diagnóstico y persistencia del historial.
- Emisión de una cotización de S/ 120.00: 2 × S/ 60.00.
- Seguimiento público del mismo pedido y estado, sin información privada.
- Reportes y gráfico Chart.js visibles; sin errores de consola observados en esa vista.
- Seguimiento y panel revisados a 390 px: sin desbordamiento horizontal del documento. Las tablas se desplazan dentro de sus contenedores.

Capturas: [panel](capturas/panel.jpg), [cotización](capturas/cotizacion.jpg), [seguimiento](capturas/seguimiento.jpg) y [reportes](capturas/reportes.jpg).

## PDF

Cotización de ejemplo: 1 página A4. Reporte de ejemplo: 2 páginas A4 horizontales. Comprobados como documentos PDF y revisados visualmente. Los encabezados de tabla se repiten al paginar.

## Alcance de esta evidencia

Estas comprobaciones no sustituyen la aceptación de AKS, pruebas de carga, despliegue HTTPS ni verificación de disponibilidad continua. El workflow de GitHub Actions está incluido; su resultado remoto se consulta en Actions y no se presupone aprobado.
