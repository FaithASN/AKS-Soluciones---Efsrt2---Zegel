-- AKS Soluciones. MySQL 8.4, InnoDB, utf8mb4.
-- Solo estructura y catálogos; sin usuarios, contraseñas ni datos de clientes.
-- Importar en una base NUEVA. Alternativa recomendada: php artisan migrate --seed.
CREATE DATABASE IF NOT EXISTS aks_soluciones CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE aks_soluciones;
SET NAMES utf8mb4;

CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `perfiles` (
  `id` tinyint unsigned NOT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perfiles_codigo_unique` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `usuarios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `perfil_id` tinyint unsigned NOT NULL,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuarios_email_unique` (`email`),
  KEY `usuarios_perfil_id_foreign` (`perfil_id`),
  CONSTRAINT `usuarios_perfil_id_foreign` FOREIGN KEY (`perfil_id`) REFERENCES `perfiles` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `clientes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombres` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `documento` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clientes_documento_index` (`documento`),
  KEY `clientes_telefono_index` (`telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `estados_servicio` (
  `id` tinyint unsigned NOT NULL,
  `codigo` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `orden` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `estados_servicio_codigo_unique` (`codigo`),
  UNIQUE KEY `estados_servicio_orden_unique` (`orden`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `equipos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cliente_id` bigint unsigned NOT NULL,
  `tipo` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `marca` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modelo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serie` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `equipos_id_cliente_id_unique` (`id`,`cliente_id`),
  KEY `equipos_cliente_id_foreign` (`cliente_id`),
  KEY `equipos_serie_index` (`serie`),
  CONSTRAINT `equipos_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pedidos_servicio` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `codigo_seguimiento` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cliente_id` bigint unsigned NOT NULL,
  `equipo_id` bigint unsigned NOT NULL,
  `registrado_por` bigint unsigned NOT NULL,
  `estado_actual_id` tinyint unsigned NOT NULL,
  `falla_reportada` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_ingreso` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_entrega` datetime DEFAULT NULL,
  `importe_cobrado` decimal(12,2) DEFAULT NULL,
  `fecha_cobro` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pedidos_servicio_codigo_seguimiento_unique` (`codigo_seguimiento`),
  KEY `pedidos_servicio_cliente_id_foreign` (`cliente_id`),
  KEY `pedidos_servicio_registrado_por_foreign` (`registrado_por`),
  KEY `pedidos_servicio_equipo_id_cliente_id_foreign` (`equipo_id`,`cliente_id`),
  KEY `pedidos_servicio_estado_actual_id_fecha_ingreso_index` (`estado_actual_id`,`fecha_ingreso`),
  KEY `pedidos_servicio_fecha_cobro_index` (`fecha_cobro`),
  CONSTRAINT `pedidos_servicio_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `pedidos_servicio_equipo_id_cliente_id_foreign` FOREIGN KEY (`equipo_id`, `cliente_id`) REFERENCES `equipos` (`id`, `cliente_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `pedidos_servicio_estado_actual_id_foreign` FOREIGN KEY (`estado_actual_id`) REFERENCES `estados_servicio` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `pedidos_servicio_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `cobro_valido` CHECK ((((`importe_cobrado` is null) and (`fecha_cobro` is null)) or ((`importe_cobrado` is not null) and (`importe_cobrado` >= 0) and (`fecha_cobro` is not null) and (`fecha_cobro` >= `fecha_ingreso`)))),
  CONSTRAINT `entrega_valida` CHECK (((`fecha_entrega` is null) or ((`fecha_entrega` >= `fecha_ingreso`) and (`estado_actual_id` = 5))))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `historial_estados` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `estado_anterior_id` tinyint unsigned DEFAULT NULL,
  `estado_nuevo_id` tinyint unsigned NOT NULL,
  `usuario_id` bigint unsigned NOT NULL,
  `fecha_cambio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `observacion` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `historial_estados_usuario_id_foreign` (`usuario_id`),
  KEY `historial_estados_estado_anterior_id_foreign` (`estado_anterior_id`),
  KEY `historial_estados_estado_nuevo_id_foreign` (`estado_nuevo_id`),
  KEY `historial_estados_pedido_id_fecha_cambio_id_index` (`pedido_id`,`fecha_cambio`,`id`),
  CONSTRAINT `historial_estados_estado_anterior_id_foreign` FOREIGN KEY (`estado_anterior_id`) REFERENCES `estados_servicio` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `historial_estados_estado_nuevo_id_foreign` FOREIGN KEY (`estado_nuevo_id`) REFERENCES `estados_servicio` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `historial_estados_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos_servicio` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `historial_estados_usuario_id_foreign` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `servicios` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `precio_vigente` decimal(12,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `servicios_activo_nombre_index` (`activo`,`nombre`),
  CONSTRAINT `servicios_precio_positivo` CHECK ((`precio_vigente` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cotizaciones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pedido_id` bigint unsigned NOT NULL,
  `emitida_por` bigint unsigned NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(12,2) NOT NULL,
  `observaciones` text COLLATE utf8mb4_unicode_ci,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cotizaciones_pedido_id_foreign` (`pedido_id`),
  KEY `cotizaciones_emitida_por_foreign` (`emitida_por`),
  KEY `cotizaciones_fecha_emision_index` (`fecha_emision`),
  CONSTRAINT `cotizaciones_emitida_por_foreign` FOREIGN KEY (`emitida_por`) REFERENCES `usuarios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `cotizaciones_pedido_id_foreign` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos_servicio` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `cotizacion_total_positivo` CHECK ((`total` >= 0))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `detalles_cotizacion` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cotizacion_id` bigint unsigned NOT NULL,
  `servicio_id` bigint unsigned NOT NULL,
  `descripcion_servicio` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad` smallint unsigned NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (round((`cantidad` * `precio_unitario`),2)) STORED,
  PRIMARY KEY (`id`),
  KEY `detalles_cotizacion_cotizacion_id_foreign` (`cotizacion_id`),
  KEY `detalles_cotizacion_servicio_id_foreign` (`servicio_id`),
  CONSTRAINT `detalles_cotizacion_cotizacion_id_foreign` FOREIGN KEY (`cotizacion_id`) REFERENCES `cotizaciones` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `detalles_cotizacion_servicio_id_foreign` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `detalle_valido` CHECK (((`cantidad` > 0) and (`precio_unitario` >= 0)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `perfiles` (`id`,`codigo`,`nombre`) VALUES ('1','ADMINISTRADOR','Administrador');
INSERT INTO `perfiles` (`id`,`codigo`,`nombre`) VALUES ('2','TECNICO','Técnico');
INSERT INTO `perfiles` (`id`,`codigo`,`nombre`) VALUES ('3','RECEPCIONISTA','Recepcionista');
INSERT INTO `estados_servicio` (`id`,`codigo`,`nombre`,`orden`) VALUES ('1','RECIBIDO','Recibido','1');
INSERT INTO `estados_servicio` (`id`,`codigo`,`nombre`,`orden`) VALUES ('2','EN_DIAGNOSTICO','En diagnóstico','2');
INSERT INTO `estados_servicio` (`id`,`codigo`,`nombre`,`orden`) VALUES ('3','EN_REPARACION','En reparación','3');
INSERT INTO `estados_servicio` (`id`,`codigo`,`nombre`,`orden`) VALUES ('4','LISTO_PARA_ENTREGA','Listo para entrega','4');
INSERT INTO `estados_servicio` (`id`,`codigo`,`nombre`,`orden`) VALUES ('5','ENTREGADO','Entregado','5');
INSERT INTO `servicios` (`id`,`nombre`,`descripcion`,`precio_vigente`,`activo`,`created_at`,`updated_at`) VALUES ('1','Mantenimiento de equipos','Limpieza y optimización para mejorar el funcionamiento del equipo.','60.00','1','2026-11-26 02:01:27','2026-11-26 02:01:27');
INSERT INTO `servicios` (`id`,`nombre`,`descripcion`,`precio_vigente`,`activo`,`created_at`,`updated_at`) VALUES ('2','Reparación de hardware','Diagnóstico y reparación de componentes del equipo.','80.00','1','2026-11-26 02:01:27','2026-11-26 02:01:27');
INSERT INTO `servicios` (`id`,`nombre`,`descripcion`,`precio_vigente`,`activo`,`created_at`,`updated_at`) VALUES ('3','Instalación de software','Instalación y configuración de sistemas y programas.','40.00','1','2026-11-26 02:01:27','2026-11-26 02:01:27');
INSERT INTO `servicios` (`id`,`nombre`,`descripcion`,`precio_vigente`,`activo`,`created_at`,`updated_at`) VALUES ('4','Redes y conectividad','Configuración de redes, Wi-Fi y conectividad.','90.00','1','2026-11-26 02:01:27','2026-11-26 02:01:27');
INSERT INTO `servicios` (`id`,`nombre`,`descripcion`,`precio_vigente`,`activo`,`created_at`,`updated_at`) VALUES ('5','Seguridad de datos','Respaldo y protección de la información del equipo.','100.00','1','2026-11-26 02:01:27','2026-11-26 02:01:27');
INSERT INTO `servicios` (`id`,`nombre`,`descripcion`,`precio_vigente`,`activo`,`created_at`,`updated_at`) VALUES ('6','Soporte técnico','Orientación y atención técnica según la necesidad del equipo.','50.00','1','2026-11-26 02:01:27','2026-11-26 02:01:27');
INSERT INTO `migrations` (`id`,`migration`,`batch`) VALUES ('1','2026_11_25_000001_create_aks_schema','1');
