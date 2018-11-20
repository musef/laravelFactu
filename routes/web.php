<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('factulogin',function(){
    return view('auth/factulogin');    
});


Route::get('/home', 'HomeController@index')->name('home');

/* **** RUTAS DE USUARIOS **** */
Route::match(['get','post'],'userProfile','UserController@showUserProfile');
Route::post('changeUserProfile','UserController@changeUserProfile');

/* **** RUTAS DE EMPRESA **** */
Route::match(['get','post'],'companyProfile','CompanyController@showCompanyProfileMenu');
Route::post('changeCompanyProfile','CompanyController@changeCompanyProfile');
Route::match(['get','post'],'paymentMethods','CompanyController@listPaymentMethodsMenu');
Route::post('createPaymentMethod','CompanyController@createPaymentMethod');
Route::post('editPaymentMethod/{id?}','CompanyController@editPaymentMethod');
Route::post('changePaymentMethod','CompanyController@changePaymentMethod');
Route::post('recordNewPaymentMethod','CompanyController@recordNewPaymentMethod');
Route::post('deletePaymentMethod/{id?}','CompanyController@deletePaymentMethod');
Route::match(['get','post'],'companySettings','CompanyController@settingsMenu');
Route::match(['get','post'],'ivaTypes','IvaController@ivaTypesMenu');
Route::post('editIva/{id?}','IvaController@showIva');
Route::post('createIva','IvaController@showIva');
Route::post('deleteIva/{id?}','IvaController@deleteIva');
Route::post('recordNewIva','IvaController@recordNewIva');
Route::post('changeIva','IvaController@updateIva');
Route::post('changeSettings','CompanyController@updateSettings');

/* **** RUTAS DE CLIENTES **** */
Route::match(['get','post'],'showCustomers','CustomerController@listCustomersMenu');
Route::post('createCustomer','CustomerController@createNewCustomer');
Route::post('recordNewCustomer','CustomerController@recordNewCustomer');
Route::post('editCustomer/{id?}','CustomerController@editCustomer');
Route::post('changeCustomer','CustomerController@changeCustomer');
Route::post('deleteCustomer/{id?}','CustomerController@deleteCustomer');
Route::match(['get','post'],'customersList','CustomerController@customersListBySelectionMenu');
Route::post('locateCustomersByOptions','CustomerController@locateCustomersByOptions');

/* ***** RUTAS DE TRABAJOS ****** */
Route::match(['get','post'],'work','WorkController@worksMenu');
Route::post('recordNewWork','WorkController@recordNewWork');
Route::match(['get','post'],'worksList','WorkController@showWorksMenu');
Route::post('searchWorksByOptions','WorkController@searchWorksByOptions');
Route::post('searchWorksPdf','WorkController@worksPdfList');
Route::post('editWork/{id?}','WorkController@editWork');
Route::post('deleteWork','WorkController@deleteWork');
Route::post('deleteWork/{id?}','WorkController@deleteWorkFromList');
Route::post('changeWork','WorkController@updateWork');
Route::post('generateWork/{id?}','WorkController@generatePdfWork');
Route::post('showPdfWork/{id?}','WorkController@showPdfWork');

/* ***** RUTAS DE FACTURAS ****** */
Route::match(['get','post'],'invoicesMenu','InvoiceController@invoicesMenu');
Route::post('worksToInvoicingList','InvoiceController@showWorksList');
Route::post('generateInvoices','InvoiceController@createInvoices');
Route::match(['get','post'],'invoicesList','InvoiceController@showInvoicesMenu');
Route::post('searchInvoices','InvoiceController@invoicesList');
Route::post('searchInvoicesPdf','InvoiceController@invoicesPdfList');
Route::post('showInvoice/{id?}','InvoiceController@showInvoice');
Route::post('deleteInvoice','InvoiceController@deleteInvoice');
Route::post('generateInvoice/{id?}','InvoiceController@generatePdfInvoice');
Route::post('showPdfInvoice/{id?}','InvoiceController@showPdfInvoice');
Route::match(['get','post'],'customerInvoicesList','InvoiceController@customerInvoicesMenu');
Route::post('showSumatoryInvoices','InvoiceController@showSumatoryInvoices');
Route::post('sumatoryInvoicesPdf','InvoiceController@sumatoryInvoicesPdf');


/**
 * Redirección página no encontrada
 */
Route::fallback(function () {
    return view('/errors/404');
});