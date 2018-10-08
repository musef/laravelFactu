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


/* **** RUTAS DE CLIENTES **** */
Route::match(['get','post'],'showCustomers/{id?}','CustomerController@showListCustomers');
Route::post('createCustomer/{id?}','CustomerController@createNewCustomer');