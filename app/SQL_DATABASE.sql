/* 
 * Copyright (C) fmsdevelopment.com author musef2904@gmail.com
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
/**
 * Author:  musef <musef20904@gmail.com>
 * Created: 01-oct-2018
 */


--
-- Base de datos: `factufms`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `companies`
--

CREATE TABLE `companies` (
  `id` int(10) UNSIGNED NOT NULL,
  `company_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_nif` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_zip` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configs`
--

CREATE TABLE `configs` (
  `id` int(10) UNSIGNED NOT NULL,
  `idcompany` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `customer_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_nif` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_zip` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `idcompany` int(10) UNSIGNED NOT NULL,
  `idmethod` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `invoices`
--

CREATE TABLE `invoices` (
  `id` int(10) UNSIGNED NOT NULL,
  `inv_date` timestamp NOT NULL DEFAULT '2018-11-21 06:40:02',
  `inv_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inv_base0` decimal(8,2) NOT NULL DEFAULT '0.00',
  `inv_cuota0` decimal(8,2) NOT NULL DEFAULT '0.00',
  `idiva0` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `inv_base1` decimal(8,2) NOT NULL DEFAULT '0.00',
  `inv_cuota1` decimal(8,2) NOT NULL DEFAULT '0.00',
  `idiva1` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `inv_base2` decimal(8,2) NOT NULL DEFAULT '0.00',
  `inv_cuota2` decimal(8,2) NOT NULL DEFAULT '0.00',
  `idiva2` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `inv_base3` decimal(8,2) NOT NULL DEFAULT '0.00',
  `inv_cuota3` decimal(8,2) NOT NULL DEFAULT '0.00',
  `idiva3` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `inv_total` decimal(9,2) NOT NULL DEFAULT '0.00',
  `inv_expiration` timestamp NOT NULL DEFAULT '2018-11-21 06:40:02',
  `idcompany` int(10) UNSIGNED NOT NULL,
  `idcustomer` int(10) UNSIGNED NOT NULL,
  `idmethod` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `iva_rates`
--

CREATE TABLE `iva_rates` (
  `id` int(10) UNSIGNED NOT NULL,
  `idcompany` int(10) UNSIGNED NOT NULL,
  `iva_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(4,2) NOT NULL DEFAULT '0.00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `type` tinyint(1) NOT NULL DEFAULT '-1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(10) UNSIGNED NOT NULL,
  `idcompany` int(10) UNSIGNED NOT NULL,
  `payment_method` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `diff` int(11) NOT NULL DEFAULT '0',
  `payment_day` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idcompany` int(10) UNSIGNED NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `works`
--

CREATE TABLE `works` (
  `id` int(10) UNSIGNED NOT NULL,
  `work_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_date` timestamp NOT NULL DEFAULT '2018-11-21 06:39:59',
  `work_number` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_qtt` decimal(7,2) NOT NULL DEFAULT '0.00',
  `work_price` decimal(7,2) NOT NULL DEFAULT '0.00',
  `work_total` decimal(9,2) NOT NULL DEFAULT '0.00',
  `idinvoice` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `idcompany` int(10) UNSIGNED NOT NULL,
  `idcustomer` int(10) UNSIGNED NOT NULL,
  `idiva` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `configs`
--
ALTER TABLE `configs`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_idcompany_foreign` (`idcompany`),
  ADD KEY `customers_idmethod_foreign` (`idmethod`);

--
-- Indices de la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoices_idcompany_foreign` (`idcompany`),
  ADD KEY `invoices_idcustomer_foreign` (`idcustomer`),
  ADD KEY `invoices_idmethod_foreign` (`idmethod`),
  ADD KEY `invoices_idiva1_foreign` (`idiva1`),
  ADD KEY `invoices_idiva2_foreign` (`idiva2`),
  ADD KEY `invoices_idiva3_foreign` (`idiva3`);

--
-- Indices de la tabla `iva_rates`
--
ALTER TABLE `iva_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iva_rates_idcompany_foreign` (`idcompany`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indices de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_methods_idcompany_foreign` (`idcompany`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indices de la tabla `works`
--
ALTER TABLE `works`
  ADD PRIMARY KEY (`id`),
  ADD KEY `works_idcompany_foreign` (`idcompany`),
  ADD KEY `works_idcustomer_foreign` (`idcustomer`),
  ADD KEY `works_idiva_foreign` (`idiva`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `configs`
--
ALTER TABLE `configs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT de la tabla `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;
--
-- AUTO_INCREMENT de la tabla `iva_rates`
--
ALTER TABLE `iva_rates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
--
-- AUTO_INCREMENT de la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `works`
--
ALTER TABLE `works`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;
--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_idcompany_foreign` FOREIGN KEY (`idcompany`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `customers_idmethod_foreign` FOREIGN KEY (`idmethod`) REFERENCES `payment_methods` (`id`);

--
-- Filtros para la tabla `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_idcompany_foreign` FOREIGN KEY (`idcompany`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `invoices_idcustomer_foreign` FOREIGN KEY (`idcustomer`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `invoices_idiva1_foreign` FOREIGN KEY (`idiva1`) REFERENCES `iva_rates` (`id`),
  ADD CONSTRAINT `invoices_idiva2_foreign` FOREIGN KEY (`idiva2`) REFERENCES `iva_rates` (`id`),
  ADD CONSTRAINT `invoices_idiva3_foreign` FOREIGN KEY (`idiva3`) REFERENCES `iva_rates` (`id`),
  ADD CONSTRAINT `invoices_idmethod_foreign` FOREIGN KEY (`idmethod`) REFERENCES `payment_methods` (`id`);

--
-- Filtros para la tabla `iva_rates`
--
ALTER TABLE `iva_rates`
  ADD CONSTRAINT `iva_rates_idcompany_foreign` FOREIGN KEY (`idcompany`) REFERENCES `companies` (`id`);

--
-- Filtros para la tabla `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_idcompany_foreign` FOREIGN KEY (`idcompany`) REFERENCES `companies` (`id`);

--
-- Filtros para la tabla `works`
--
ALTER TABLE `works`
  ADD CONSTRAINT `works_idcompany_foreign` FOREIGN KEY (`idcompany`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `works_idcustomer_foreign` FOREIGN KEY (`idcustomer`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `works_idiva_foreign` FOREIGN KEY (`idiva`) REFERENCES `iva_rates` (`id`);