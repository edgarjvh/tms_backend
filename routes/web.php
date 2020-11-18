<?php

Route::get('/', function () {
    return view('welcome');
});

Route::post('/customers', 'CustomersController@customers')->name('customers')->middleware('cors');
Route::post('/saveCustomer', 'CustomersController@saveCustomer')->middleware('cors');
Route::post('/getCustomerPayload', 'CustomersController@getCustomerPayload')->middleware('cors');
Route::post('/carriers', 'CarriersController@carriers')->name('carriers')->middleware('cors');
Route::post('/getContacts', 'ContactsController@getContacts')->middleware('cors');
Route::post('/contacts', 'ContactsController@contacts')->name('contacts')->middleware('cors');
Route::post('/getContactById', 'ContactsController@getContactById')->middleware('cors');
Route::post('/getContactsByCustomerId', 'ContactsController@getContactsByCustomerId')->middleware('cors');
Route::post('/saveContact', 'ContactsController@saveContact')->middleware('cors');
Route::post('/deleteContact', 'ContactsController@deleteContact')->middleware('cors');
Route::post('/uploadAvatar', 'ContactsController@uploadAvatar')->middleware('cors');
Route::post('/removeAvatar', 'ContactsController@removeAvatar')->middleware('cors');
Route::post('/notes', 'NotesController@notes')->name('notes')->middleware('cors');
Route::post('/saveNote', 'NotesController@saveNote')->middleware('cors');
Route::post('/directions', 'DirectionsController@directions')->name('directions')->middleware('cors');
Route::post('/saveDirection', 'DirectionsController@saveDirection')->middleware('cors');
