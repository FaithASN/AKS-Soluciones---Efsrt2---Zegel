# Uso de AKS Soluciones

1. **Administrador:** entra en Acceso del personal. Crea cuentas en Usuarios, configura Servicios y revisa el panel. Crea el primer administrador con `php artisan aks:crear-admin` después de las migraciones.
2. **Recepción:** en Pedidos elige Nuevo pedido. Selecciona un cliente y equipo existentes, o registra los nuevos. Escribe la falla y guarda. Entrega al cliente el código de 32 caracteres mostrado en el detalle.
3. **Técnico:** abre el pedido, revisa la falla y confirma el siguiente estado con una observación interna. Las observaciones no se muestran en la consulta pública. Tras Entregado ya no puede avanzar.
4. **Administrador:** en Nueva cotización selecciona el pedido, agrega uno o más servicios y sus cantidades. El total visual es orientativo; el servidor calcula el definitivo. Abre el documento emitido y descarga el PDF.
5. **Cliente:** visita Seguimiento e ingresa su código de 32 caracteres. No necesita iniciar sesión. Los antiguos códigos `AKS-DEMO-0001` del prototipo HTML ya no corresponden al sistema conectado a MySQL.
6. **Ingresos:** el administrador registra importe y fecha de cobro en el detalle del pedido. Este registro alimenta los reportes; no realiza una transacción financiera.
7. **Reportes:** selecciona un período y actualiza. Los pedidos se filtran por ingreso; los cobros por su propia fecha. Descarga el PDF con la misma selección.
8. **Salir:** utiliza Cerrar sesión. Cada cuenta tiene las acciones permitidas por su perfil.

El catálogo inicial y los precios provienen del prototipo; deben validarse con AKS antes de uso real. Los datos cargados por DemoSeeder son ficticios y no constituyen información de clientes reales.
