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
         * Esta funcion toma los elementos de facturación del albarán
         * y calcula, si procede, el valor total del albarán
         * @returns {undefined}
         */
        function calculator() {
            
            // transformamos las comas decimales por puntos decimales
            var qtt=document.getElementById('workqtt').value;
            qtt=qtt.replace(',','.');
            document.getElementById('workqtt').value=qtt;  
            
            var price=document.getElementById('workprice').value;
            price=price.replace(',','.');
            document.getElementById('workprice').value=price;
            
            var iva=document.getElementById('workiva').value;
            iva=iva.replace(',','.');
            document.getElementById('workiva').value=iva;
            
            var wtotal=document.getElementById('worktotal');

            // calcula el valor
            var total=(qtt*price)*(1+(iva/100));
            if (isNaN(total)) {
                total='ERROR';
                // lo guarda en el elemento            
                wtotal.value=total;
                wtotal.setAttribute('style','background-color:RED;color:WHITE');
            } else {
                wtotal.setAttribute('style','background-color:WHITE;color:BLACK');  
                // lo guarda en el elemento            
                wtotal.value=total.toFixed(2);                
            }
            

        }
        
        
        function checkingForm() {
            
            var asking=confirm('¿Seguro que desea grabar los datos del albarán?');
            
            if (asking===false) return false;
         
            // leemos el formulario 
            
            var id=document.getElementById('customerid').value;
            var wdate=document.getElementById('workdate').value;
            var invoice=document.getElementById('workinvoice').value;            
            var concept=document.getElementById('workconcept').value;

            var qtt=document.getElementById('workqtt').value;
            var price=document.getElementById('workprice').value;
            var iva=document.getElementById('workiva').value;
            var wtotal=document.getElementById('worktotal').value;


            // verificamos selección de cliente
            if (id==0 || isNaN(id)) {
                alert ('DEBE seleccionar un cliente');
                return false;
            }
            
            // verificamos fecha
            if (wdate.length!=10) {
                alert ('Longitud de fecha inválida: formato dd-mm-aaaa');
                return false;                
            }
            // verificamos día
            if (isNaN(wdate.substr(0,2)) || wdate.substr(0,2)<1 || wdate.substr(0,2)>31) {
                alert ('Día incorrecto: debe ser númerico y estar comprendido entre 01 y 31');
                return false;                
            }
            // verificamos mes
            if (isNaN(wdate.substr(3,2)) || wdate.substr(3,2)<1 || wdate.substr(3,2)>12) {
                alert ('Mes incorrecto: debe ser númerico y estar comprendido entre 01 y 12');
                return false;                
            }            
            // verificamos año
            if (isNaN(wdate.substr(6,4)) || wdate.substr(6,4)<2010 || wdate.substr(6,4)>2099) {
                alert ('Año incorrecto: debe ser númerico y estar comprendido entre 2010 y 2099');
                return false;                
            }             
            
            if (concept.length<5) {
                alert ('El concepto debe tener longitud entre 5 y 255 caracteres.');
                return false;
            }            
            if (isNaN(qtt.replace(',','.'))) {
                alert ('Cantidad es no numérica. Debe contener un número');
                return false;
            }
            if (isNaN(price.replace(',','.'))) {
                alert ('Precio es no numérico. Debe contener un número');
                return false;
            }            
            if (isNaN(iva.replace(',','.'))) {
                alert ('Iva es no numérico. Debe contener un número');
                return false;
            }     
            if (isNaN(wtotal.replace(',','.'))) {
                alert ('El total es no numérico. Debe contener un número');
                return false;
            }
            
            return true;
        }