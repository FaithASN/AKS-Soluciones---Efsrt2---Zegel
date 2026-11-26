# Arquitectura de tres capas de AKS Soluciones

| Capa | Directorios | Responsabilidad |
|---|---|---|
| Vista / presentación | `resources/views`, `public/assets` | Plantillas Blade, formularios, navegación, estilos Bootstrap y JavaScript. |
| Negocio | `app/Services`, `app/Http/Controllers`, `app/Http/Middleware` | Casos de uso, validaciones, autorización por perfil, transacciones, cálculo de cotizaciones y reportes. |
| Datos | `app/Models`, `database/migrations`, `database/seeders`, `database/sql` | Eloquent, estructura de MySQL, claves foráneas, índices y datos iniciales. |

`routes/web.php` relaciona las solicitudes con los controladores. Los controladores validan la entrada y llaman a los servicios cuando corresponde. Los servicios coordinan los modelos, y estos acceden a MySQL. Los resultados se presentan con Blade. La separación es lógica; no requiere tres servidores. MVC de Laravel organiza el código y complementa esta distribución.

## Trazabilidad implementada

| Requisito / CU | Implementación | Evidencia |
|---|---|---|
| RF1 / CU01: iniciar sesión | AuthController, Usuario, middleware Perfil | Sesión regenerada al entrar, hash bcrypt, limitación de intentos, bloqueo de cuentas inactivas y cierre de sesión. |
| RF2 / CU02: registrar pedido | PedidoService::registrar | Cliente nuevo/existente, equipo nuevo/existente, propietario validado, código aleatorio único y primer historial en una transacción. |
| RF3 / CU03: actualizar estado | PedidoService::avanzar | Bloqueo de fila, avance consecutivo e historial en la misma transacción. |
| RF4 / CU04: catálogo | ServicioController | Crear, editar precios y activar/desactivar. Los activos se publican en la web. |
| RF4 / CU05: cotización | CotizacionService | Precio obtenido del servidor, cantidades validadas, suma en centavos, detalle histórico y descarga PDF. |
| RF5 / CU07: seguimiento | PublicoController | Consulta por código; solo estado, tipo/marca del equipo y fechas. Sin datos personales ni observaciones internas. |
| RF6 / CU06: reportes | ReporteService, ReporteController | Pedidos por ingreso, estados, cobros por fecha de cobro, promedio de horas y PDF. |
| CU08: servicios/contacto/ubicación | Vistas públicas | Catálogo activo y dirección documentada. Sin inventar teléfonos, correos comerciales ni horarios. |

## Reglas y decisiones

- El administrador puede supervisar registros y estados además de sus funciones administrativas. El técnico solo ve pedidos y actualiza estados; la recepción registra pedidos y administra datos de clientes. Esta ampliación de supervisión debe alinearse con la versión final del informe.
- Los cinco estados y sus identificadores son fijos. El avance consecutivo es la regla propuesta en el modelo físico; requiere confirmación comercial con AKS.
- Registro de cobro: importe y fecha juntos, para RF6. No hay pasarela de pagos, factura electrónica, cuotas ni comprobación bancaria.
- Cotizaciones emitidas son inmutables: para cambiar una cotización se emite una nueva, conservando la anterior. Los subtotales son columnas calculadas en MySQL.
- Usuarios y servicios se desactivan. No se eliminan registros operativos ni historiales.
- Fechas almacenadas en UTC y mostradas/filtradas en America/Lima.
- La base mantiene las diez tablas del diseño físico. `migrations` es una tabla técnica de Laravel.
- Interfaz de administración de usuarios: apoyo a RF1/RNF4; no se inventa un noveno caso de uso ni un RF adicional.

## Límites de la entrega

Implementación académica de los RF documentados, con datos ficticios opcionales. Falta la validación y aceptación de AKS, despliegue HTTPS, configuración de correo y copias de seguridad operativas. RNF de disponibilidad 24/7 depende del alojamiento y no queda acreditado por ejecutar localmente. No se afirma una certificación de seguridad ni una prueba de carga productiva. El restablecimiento de contraseña se realiza por un administrador autorizado; no se ofrece un enlace de recuperación por correo sin configurar ese servicio.
