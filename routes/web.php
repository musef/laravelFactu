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
Route::match(['get','post'],'companyProfile','CompanyController@showCompanyProfile');
Route::post('changeCompanyProfile','CompanyController@changeCompanyProfile');
Route::match(['get','post'],'paymentMethods/{id?}','CompanyController@listPaymentMethods');
Route::post('createPaymentMethod','CompanyController@createPaymentMethod');
Route::post('editPaymentMethod/{id?}','CompanyController@editPaymentMethod');
Route::post('changePaymentMethod','CompanyController@changePaymentMethod');
Route::post('recordNewPaymentMethod','CompanyController@recordNewPaymentMethod');
Route::post('deletePaymentMethod/{id?}','CompanyController@deletePaymentMethod');
Route::match(['get','post'],'companySettings/{id?}','CompanyController@settings');

/* **** RUTAS DE CLIENTES **** */
Route::match(['get','post'],'showCustomers/{id?}','CustomerController@showListCustomers');
Route::post('createCustomer/{id?}','CustomerController@createNewCustomer');
Route::post('recordNewCustomer','CustomerController@recordNewCustomer');
Route::post('editCustomer/{id?}','CustomerController@editCustomer');
Route::post('changeCustomer','CustomerController@changeCustomer');
Route::post('deleteCustomer/{id?}','CustomerController@deleteCustomer');
Route::match(['get','post'],'customersList/{id?}','CustomerController@showCustomersListBySelection');
Route::post('locateCustomersByOptions','CustomerController@locateCustomersByOptions');

/* ***** RUTAS DE TRABAJOS ****** */
Route::match(['get','post'],'work/{id?}','WorkController@showWork');
Route::post('recordNewWork','WorkController@recordNewWork');
Route::match(['get','post'],'worksList/{id?}','WorkController@showWorksMenu');
Route::post('searchWorksByOptions','WorkController@searchWorksByOptions');
Route::post('editWork/{id?}','WorkController@editWork');
Route::post('deleteWork','WorkController@deleteWork');
Route::post('changeWork','WorkController@updateWork');