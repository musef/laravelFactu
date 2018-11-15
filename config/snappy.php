<?php

return array(


    'pdf' => array(
        'enabled' => true,
        //'binary'  => '/usr/local/bin/wkhtmltopdf',
        'binary' => base_path('vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64'),        
        //'binary'  => 'vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64',
        'timeout' => false,
        'options' => array(
            'page-size' => 'A4',
            'margin-top' => 5,
            'margin-right' => 5,
            'margin-left' => 5,
            'margin-bottom' => 6,
            'orientation' => 'Portrait',
            //'footer-center' => 'Pagina [page] de [toPage]',
            'footer-center' => '',
            'footer-font-size' => 8,
            //'footer-left' => 'Listado emitido el '.now(),
            'footer-left' => '',
        ),
        'env'     => array(),
    ),  
    'image' => array(
        'enabled' => true,
        'binary' => base_path('vendor/h4cc/wkhtmltoimage-amd64/bin//wkhtmltoimage-amd64'),
        //'binary'  => 'vendor/h4cc/wkhtmltoimage-amd64/bin//wkhtmltoimage-amd64',
        'timeout' => false,
        'options' => array(),
        'env'     => array(),
    ),


);
