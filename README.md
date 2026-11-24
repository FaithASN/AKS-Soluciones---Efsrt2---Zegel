# AKS Soluciones — primer avance del frontend

Entrega equivalente a un **hito aproximado del 30 %**, delimitado a las tres pantallas públicas del conjunto de nueve wireframes compartidos. El porcentaje describe el alcance acordado de esta entrega; no es una medición automática de todo el esfuerzo del proyecto.

## Abrir

Abre `index.html` con Chrome, Edge o Firefox. No requiere instalar paquetes ni tener conexión a internet. También puedes abrir esta carpeta en Visual Studio Code y usar Live Server.

Para revisión por HTTP con Node instalado: `node server.mjs`, y visita `http://127.0.0.1:4173`. Detén el servidor con Ctrl+C.

## Incluido

- **Inicio (web pública):** navegación, presentación, seis servicios, proceso de atención, contacto y pie de página.
- **Servicios (web pública):** catálogo visual, precios referenciales tomados del prototipo, consulta de servicio mediante un diálogo y preguntas frecuentes desplegables.
- **Seguimiento público (web):** búsqueda simulada, validación, estados de error/no encontrado, línea de estados e historial de dos pedidos ficticios.
- Diseño responsive, menú móvil, navegación por teclado, diálogo con cierre por Escape y mensajes de estado accesibles.
- Estilo oscuro y azul, distribución y contenido basados en las capturas compartidas. No se tuvo acceso a un archivo o enlace directo de Figma; no se declara una reproducción exacta de sus capas o medidas.

## Probar seguimiento

Usa `AKS-DEMO-0001` (en reparación) o `AKS-DEMO-0002` (listo para entrega). También hay un botón que carga el primer ejemplo. Los registros son ficticios, están en JavaScript y no contienen datos personales reales. El formulario no conecta con una API, base de datos ni servidor de negocio. El diálogo de atención no envía solicitudes.

## Pendiente del frontend

Acceso (login), Panel de control, Lista de pedidos, Nueva cotización, Catálogo de servicios y Usuarios y perfiles. Las pantallas no entregadas no aparecen como enlaces funcionales. Los formularios internos y la edición de estados también quedan pendientes. No se simula autenticación real.

## Tecnologías y arquitectura

HTML5, CSS, JavaScript y la cuadrícula oficial local de **Bootstrap 5.3.3**. Los estilos de componentes son propios para respetar el wireframe. No se agregaron React, Vue ni otro framework ajeno al informe.

Este trabajo corresponde únicamente a la **capa de presentación**. Para integrarlo posteriormente en Laravel: mover la cabecera y el pie a un layout Blade, convertir cada página en una vista, reemplazar las rutas HTML por rutas Laravel y sustituir `demoOrders` por consultas a un endpoint que aplique las reglas y permisos del backend. MySQL y la capa de negocio quedan fuera de este avance.

## Estructura

`index.html`, `servicios.html`, `seguimiento.html`: páginas. `assets/css/styles.css`: diseño compartido. `assets/js/app.js`: componentes e interacciones de presentación. `assets/vendor/bootstrap-grid.min.css`: cuadrícula Bootstrap (licencia MIT incluida en su cabecera). `server.mjs`: servidor local de revisión, sin dependencias y sin almacenamiento.

Teléfono, correo y horarios no se inventaron: se muestra la dirección documentada. Las condiciones comerciales y los precios deben validarse con AKS antes de publicar.
