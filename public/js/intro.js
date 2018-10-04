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

$(document).ready(function(){
    /*
    $('#F').click(function(){
        document.getElementById("goForward").submit();
    });
    $('#M').click(function(){
        document.getElementById("goForward").submit();
    });
    $('#S').click(function(){
        document.getElementById("goForward").submit();
    }); 
    $('#factu').click(function(){
        document.getElementById("goForward").submit();
    });     
    */
});

function initiate() {
    
    $('#F').hide();
    $('#M').hide();
    $('#S').hide();
    $('#factu').hide();    
    
    $('#F').text('F');
    $('#M').text('M');
    $('#S').text('S');
    $('#factu').text('factu');    

    $('#F').show(500);
    $('#M').show(1000);
    $('#S').show(2000);    
    $('#factu').show(5000);    
}
