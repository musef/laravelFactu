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


CREATE DATABASE factufms;
USE factufms;

CREATE TABLE companies (id INT NOT NULL AUTO_INCREMENT, company_name VARCHAR(200) NOT NULL, company_address VARCHAR(255) NOT NULL, company_zip VARCHAR(5) NOT NULL, 
company_city VARCHAR(100) NOT NULL, company_nif VARCHAR(9) NOT NULL, PRIMARY KEY (id));

CREATE TABLE payment_methods (id INT NOT NULL AUTO_INCREMENT, idcompany INT UNSIGNED NOT NULL, payment_method VARCHAR(200) NOT NULL, diff INT DEFAULT 0, 
payment_day INT DEFAULT 0, PRIMARY KEY (id), 
CONSTRAINT fk_meth_comp FOREIGN KEY (idcompany) REFERENCES companies(id));

CREATE TABLE customers (id INT NOT NULL AUTO_INCREMENT, idcompany INT UNSIGNED NOT NULL, customer_name VARCHAR(100) NOT NULL, customer_address VARCHAR(255) NOT NULL, 
customer_zip VARCHAR(5) NOT NULL, customer_city VARCHAR(100) NOT NULL, customer_nif VARCHAR(9) NOT NULL, idmethod INT UNSIGNED NOT NULL DEFAULT 1, PRIMARY KEY(id),
CONSTRAINT fk_cust_comp FOREIGN KEY (idcompany) REFERENCES companies(id),
CONSTRAINT fk_cust_meth FOREIGN KEY (idmethod) REFERENCES payment_methods(id));

CREATE TABLE works (id INT NOT NULL AUTO_INCREMENT, idcompany INT UNSIGNED NOT NULL, work_date TIMESTAMP NOT NULL, work_number VARCHAR(15) NOT NULL, 
idcustomer INT UNSIGNED NOT NULL, work_text TEXT NOT NULL, work_qtt DECIMAL(7,2) NOT NULL DEFAULT 0, work_price DECIMAL (7,2) NOT NULL DEFAULT 0, 
work_typeiva DECIMAL (4,2) NOT NULL DEFAULT 0, work_total DECIMAL (9,2) NOT NULL DEFAULT 0, idinvoice INT UNSIGNED NOT NULL DEFAULT 0, 
PRIMARY KEY(id), CONSTRAINT fk_work_comp FOREIGN KEY (idcompany) REFERENCES companies(id), CONSTRAINT fk_work_cust FOREIGN KEY (idcustomer) REFERENCES customers(id));

CREATE TABLE invoices (id INT NOT NULL AUTO_INCREMENT, idcompany INT UNSIGNED NOT NULL, inv_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
inv_number VARCHAR(15) NOT NULL, idcustomer INT UNSIGNED NOT NULL, inv_base1 DECIMAL(8,2) NOT NULL DEFAULT 0, inv_cuota1 DECIMAL (8,2) NOT NULL DEFAULT 0,
 inv_base2 DECIMAL(8,2) NOT NULL DEFAULT 0, inv_cuota2 DECIMAL (8,2) NOT NULL DEFAULT 0, inv_base3 DECIMAL(8,2) NOT NULL DEFAULT 0,
 inv_cuota3 DECIMAL (8,2) NOT NULL DEFAULT 0, inv_total DECIMAL (9,2) NOT NULL DEFAULT 0, idmethod INT UNSIGNED NOT NULL, 
inv_expiration TIMESTAMP DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id), CONSTRAINT fk_inv_comp FOREIGN KEY (idcompany) REFERENCES companies(id), 
CONSTRAINT fk_inv_cust FOREIGN KEY (idcustomer) REFERENCES customers(id),CONSTRAINT fk_inv_meth FOREIGN KEY (idmethod) REFERENCES payment_methods(id));

