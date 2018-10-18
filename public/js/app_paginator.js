$(document).ready(function(){

    /* ******************** MANEJO DEL LISTADO Y RENUMERACIÓN ********************* */
    
    /*
     * Para poder ser paginadas, las tablas de datos deben cumplir:
     * el tbody debe tener id=bodytable  -> <tbody id="bodytable">
     * cada uno de los tr debe tener id=num de posición -> <tr id="1">
     * para posiciones repetidas , debe tener dup al final del id -> <tr id="1dup">
     * 
     * Toma el intervalo del select id="datatable_sel"
     * 
     * A partir de ahí fabrica un paginador de 5 páginas con un "anterior" y un
     * "siguiente" que muestra las anteriores o siguientes 5 páginas
     */


    // inicialmente deshabilitamos el boton anterior
    $('#btprev').hide();

    // renumeramos las paginas
    renumPages(0);

    // leemos el click de los botones de paginas con numero
    $('a.paginate_button').click(function() {
        // ocultamos la tabla completa
        $("#bodytable").children().hide();
        // maximo de registros
        var maxregs=$('#count').val();
        // intervalo de registros                              
        var interval=Number(document.getElementById("datatable_sel").value);
        // valor de la pagina
        var page=$(this).text()-1;
        // limite 1 a mostrar
        var lim1=(page*interval)+1;
        // limite 2 a mostrar
        var lim2= Number(lim1+interval-1);
        if (lim2>maxregs) lim2=Number(maxregs);

        // anterior deshabilitado o habilitado en funcion
        // de pagina donde estemos
        if (page<5) {
            $('#btprev').hide();
        } else {
            $('#btprev').show();
        }

        // mostramos los seleccionados
        for (var n=lim1;n<=lim2;n++) {
            $("#"+n).show(); 
            $("#"+n+"dup").show();            
        }
        $('#mostrando').text('Mostrando '+lim1+' a '+Number(lim2)+' de '+maxregs+' entradas');

        renumPages(parseInt(page/5)); 
    });

    // leemos el click del boton pagina siguiente
    $('a.paginate_button_next').click(function() {
        // ocultamos la tabla completa
        $("#bodytable").children().hide();
        // maximo de registros
        var maxregs=$('#count').val();
        // intervalo de registros                              
        var interval=Number(document.getElementById("datatable_sel").value);

        // renumeramos al hacer next 6 paginas posteriores
        $('#bt5').text(parseInt($('#bt5').text())+5); 
        $('#bt4').text(parseInt($('#bt4').text())+5); 
        $('#bt3').text(parseInt($('#bt3').text())+5);
        $('#bt2').text(parseInt($('#bt2').text())+5);
        $('#bt1').text(parseInt($('#bt1').text())+5);

        // valor de la pagina primera
        (parseInt($('#bt1').text())-1)<0 ?  page=0 : page=parseInt($('#bt1').text())-1 ;

        // limite 1 a mostrar
        var lim1=(page*interval)+1;
        // limite 2 a mostrar
        var lim2= Number(lim1+interval-1);
        if (lim2>maxregs) lim2=Number(maxregs);

        // anterior deshabilitado o habilitado en funcion
        // de pagina donde estemos
        if (page<5) {
            $('#btprev').hide();
        } else {
            $('#btprev').show();
        }

        // mostramos los seleccionados
        for (var n=lim1;n<=lim2;n++) {
            $("#"+n).show();  
            $("#"+n+"dup").show();            
        }
        // mensaje
        $('#mostrando').text('Mostrando '+lim1+' a '+Number(lim2)+' de '+maxregs+' entradas');

        // renumeramos pie
        renumPages(parseInt(page/5)); 
    });

    // leemos el click del boton pagina anterior
    $('a.paginate_button_prev').click(function() {

        // ocultamos la tabla completa
        $("#bodytable").children().hide();
        // maximo de registros
        var maxregs=$('#count').val();
        // intervalo de registros                              
        var interval=Number(document.getElementById("datatable_sel").value);

        // renumeramos al hacer next 6 paginas posteriores
        $('#bt5').text(parseInt($('#bt5').text())-5); 
        $('#bt4').text(parseInt($('#bt4').text())-5); 
        $('#bt3').text(parseInt($('#bt3').text())-5);
        $('#bt2').text(parseInt($('#bt2').text())-5);
        $('#bt1').text(parseInt($('#bt1').text())-5);

        // valor de la pagina primera
        (parseInt($('#bt1').text())-1)<0 ?  page=4 : page=parseInt($('#bt1').text())+3 ;

        // limite 1 a mostrar
        var lim1=(page*interval)+1;
        // limite 2 a mostrar
        var lim2= Number(lim1+interval-1);
        if (lim2>maxregs) lim2=Number(maxregs);

        // anterior deshabilitado o habilitado en funcion
        // de pagina donde estemos
        if (page<5) {
            $('#btprev').hide();
        } else {
            $('#btprev').show();
        }

        // mostramos los seleccionados
        for (var n=lim1;n<=lim2;n++) {
            $("#"+n).show(); 
            $("#"+n+"dup").show();            
        }

        // mensaje
        $('#mostrando').text('Mostrando '+lim1+' a '+Number(lim2)+' de '+maxregs+' entradas');

        // renumeramos pie
        renumPages(parseInt(page/5)); 
    });


    // leemos el select de cuantos registros mostrar
    $('#datatable_sel').change(function() {                
        resizeTable();
    });


    /**
     * Reconstruye la tabla de nuevo con el número de registros deseado, y
     * reconstruye también el pie de página
     * @returns {undefined}
     */
    function resizeTable() {
        
       // var doc=document.getElementById('bodytable');
        //var numlineas=doc.getElementsByTagName('tr').length;

        // ocultamos la tabla completa
        $("#bodytable").children().hide();
        // maximo de registros
        var maxregs=$('#count').val();
        //var maxregs=numlineas;
        // intervalo de registros                              
        var interval=Number(document.getElementById("datatable_sel").value);
        // valor de la pagina
        var page=0;
        // limite 1 a mostrar
        var lim1=(page*interval)+1;
        // limite 2 a mostrar
        var lim2= Number(lim1+interval-1);
        if (lim2>maxregs) lim2=Number(maxregs);

        // mostramos los seleccionados
        for (var n=lim1;n<=lim2;n++) {
            $("#"+n).show();
            $("#"+n+"dup").show();
        }
        $('#mostrando').text('Mostrando '+lim1+' a '+Number(lim2)+' de '+maxregs+' entradas');

        renumPages(0);                
    }

    /**
     * Reconstruye el pie de pagina en función del numero de elementos a mostrar y el
     * intervalo existente
     * @returns {undefined}
     */
    function renumPages( pg) {

        // escalaridad del next-previous
        pg=pg*5;
        // intervalo a mostrar
        var interval=Number(document.getElementById("datatable_sel").value);
        // registros obtenidos
        var maxregs=$('#count').val();

        // calculamos las pages a mostrar
        if (parseInt(maxregs/interval)<maxregs/interval) {
            var pages=parseInt(maxregs/interval)+1;    
        } else var pages=parseInt(maxregs/interval);


        // habilitamos botones en función de cuantos
        switch(pages) {
            case (5+pg):
                $('#btnext').hide();
                $('#bt5').show(); 
                $('#bt4').show(); 
                $('#bt3').show();
                $('#bt2').show();                         
                break;
            case (4+pg):
                $('#btnext').hide();
                $('#bt5').hide();
                $('#bt4').show(); 
                $('#bt3').show();
                $('#bt2').show();                         
                break;
            case (3+pg):
                $('#btnext').hide();                    
                $('#bt5').hide();                    
                $('#bt4').hide();
                $('#bt3').show();
                $('#bt2').show();                        
                break;
            case (2+pg):
                $('#btnext').hide();                    
                $('#bt5').hide();                    
                $('#bt4').hide();                    
                $('#bt3').hide();
                $('#bt2').show();                       
                break;
            case (1+pg):
                $('#btnext').hide();                    
                $('#bt5').hide();                    
                $('#bt4').hide();                    
                $('#bt3').hide();                    
                $('#bt2').hide(); 
                break;
            case (0):
                $('#btnext').hide();                    
                $('#bt5').hide();                    
                $('#bt4').hide();                    
                $('#bt3').hide();                    
                $('#bt2').hide();                        
                break;
            default:
                $('#btnext').show();
                $('#bt5').show(); 
                $('#bt4').show(); 
                $('#bt3').show();
                $('#bt2').show();                      
        }

    }

    /*  ************* FIN DEL PAGINADOR *********************** */

});


