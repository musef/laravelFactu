<?php

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
     * Esta funcion limpia los inputs de valores indeseados
     * 
     * @param type $data
     */
    function clearInput($data) {
        
       $forbidden=array('<','>','|','\\','script');
       
       $data= trim(str_ireplace($forbidden, '', $data));
       
       return $data;
    }
    
    
    
    /**
     * Esta función convierte una fecha en formato ddbb (YYYY-mm-dd hh:mm:ss)
     * en una fecha en formato español dd-mm-AAAA
     * 
     * Si la fecha es corta,la devuelve sin más
     * 
     * @param type $sqldate
     * @return type
     */
    function converterDate($sqldate) {
        
        if (strlen($sqldate)<10) return $sqldate;
        
        return substr($sqldate,8,2).substr($sqldate,4,4).substr($sqldate,0,4);
        
    }

    /**
     * Esta función convierte una fecha en formato ddbb (YYYY-mm-dd hh:mm:ss)
     * en una fecha en formato español dd-mm-AAAA hh:mm:ss
     * 
     * Si la fecha es corta,la devuelve sin más
     * 
     * @param type $sqldate
     * @return type
     */
    function converterDateTime($sqldate) {
        
        if (strlen($sqldate)<10) return $sqldate;
        
        return substr($sqldate,8,2).substr($sqldate,4,4).substr($sqldate,0,4).substr($sqldate,10);
        
    }
    
    
     /**
     * Esta función convierte una fecha en formato español (dd-mm-aaaa)
     * en una fecha en formato DDBB yyyy-mm-dd
     * 
     * Si la fecha es diferente,la devuelve sin más
     * 
     * @param type $sqldate
     * @return type
     */
    function converterDateToDDBB($sqldate) {
        
        if (strlen($sqldate)!=10) return $sqldate;
        
        return substr($sqldate,6).'-'.substr($sqldate,3,2).'-'.substr($sqldate,0,2);
        
    }
    
    /**
     * Esta función convierte una fecha en formato español (dd-mm-aaaa hh:mm:ss)
     * en una fecha en formato DDBB yyyy-mm-dd hh:mm:ss
     * 
     * Si la fecha es diferente,la devuelve sin más
     * 
     * @param type $sqldate
     * @return type
     */
    function converterDateTimeToDDBB($sqldate) {
        
        if (strlen($sqldate)!=19) return $sqldate;
        
        return substr($sqldate,6,4).'-'.substr($sqldate,3,2).'-'.substr($sqldate,0,2).' '.substr($sqldate,10);
        
    }