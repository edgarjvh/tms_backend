<?php

use Illuminate\View\View;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/customers', 'CustomersController@customers')->name('customers')->middleware('cors');
Route::post('/saveCustomer', 'CustomersController@saveCustomer')->middleware('cors');
Route::post('/getCustomerPayload', 'CustomersController@getCustomerPayload')->middleware('cors');
Route::post('/carriers', 'CarriersController@carriers')->name('carriers')->middleware('cors');
Route::post('/saveCarrier', 'CarriersController@saveCarrier')->middleware('cors');
Route::post('/getCarrierPayload', 'CarriersController@getCarrierPayload')->middleware('cors');

Route::post('/getContacts', 'ContactsController@getContacts')->middleware('cors');
Route::post('/getContactsByEmail', 'ContactsController@getContactsByEmail')->middleware('cors');
Route::post('/getContactsByEmailOrName', 'ContactsController@getContactsByEmailOrName')->middleware('cors');
Route::post('/contacts', 'ContactsController@contacts')->name('contacts')->middleware('cors');
Route::post('/getContactById', 'ContactsController@getContactById')->middleware('cors');
Route::post('/getContactsByCustomerId', 'ContactsController@getContactsByCustomerId')->middleware('cors');
Route::post('/saveContact', 'ContactsController@saveContact')->middleware('cors');
Route::post('/deleteContact', 'ContactsController@deleteContact')->middleware('cors');
Route::post('/uploadAvatar', 'ContactsController@uploadAvatar')->middleware('cors');
Route::post('/removeAvatar', 'ContactsController@removeAvatar')->middleware('cors');

Route::post('/getAutomaticEmails', 'AutomaticEmailsController@getAutomaticEmails')->middleware('cors');
Route::post('/saveAutomaticEmails', 'AutomaticEmailsController@saveAutomaticEmails')->middleware('cors');

Route::post('/getCarrierContacts', 'ContactsController@getCarrierContacts')->middleware('cors');
Route::post('/carrierContacts', 'ContactsController@carrierContacts')->name('contacts')->middleware('cors');
Route::post('/getCarrierContactById', 'ContactsController@getCarrierContactById')->middleware('cors');
Route::post('/getCarrierContactsByCustomerId', 'ContactsController@getCarrierContactsByCustomerId')->middleware('cors');
Route::post('/saveCarrierContact', 'ContactsController@saveCarrierContact')->middleware('cors');
Route::post('/deleteCarrierContact', 'ContactsController@deleteCarrierContact')->middleware('cors');
Route::post('/uploadCarrierAvatar', 'ContactsController@uploadCarrierAvatar')->middleware('cors');
Route::post('/removeCarrierAvatar', 'ContactsController@removeCarrierAvatar')->middleware('cors');

Route::post('/notes', 'NotesController@notes')->name('notes')->middleware('cors');
Route::post('/saveNote', 'NotesController@saveNote')->middleware('cors');

Route::post('/carrierNotes', 'NotesController@carrierNotes')->name('notes')->middleware('cors');
Route::post('/saveCarrierNote', 'NotesController@saveCarrierNote')->middleware('cors');

Route::post('/directions', 'DirectionsController@directions')->name('directions')->middleware('cors');
Route::post('/saveDirection', 'DirectionsController@saveDirection')->middleware('cors');
Route::post('/deleteDirection', 'DirectionsController@deleteDirection')->middleware('cors');

Route::post('/getDriversByCarrierId', 'DriversController@getDriversByCarrierId')->middleware('cors');
Route::post('/saveCarrierDriver', 'DriversController@saveCarrierDriver')->middleware('cors');
Route::post('/deleteCarrierDriver', 'DriversController@deleteCarrierDriver')->middleware('cors');

Route::post('/saveCustomerHours', 'HoursController@saveCustomerHours')->middleware('cors');

Route::post('/getInsuranceTypes', 'InsuranceTypesController@getInsuranceTypes')->middleware('cors');
Route::post('/saveInsuranceType', 'InsuranceTypesController@saveInsuranceType')->middleware('cors');
Route::post('/deleteInsuranceType', 'InsuranceTypesController@deleteInsuranceType')->middleware('cors');

Route::post('/getInsurances', 'InsurancesController@getInsurances')->middleware('cors');
Route::post('/saveInsurance', 'InsurancesController@saveInsurance')->middleware('cors');
Route::post('/deleteInsurance', 'InsurancesController@deleteInsurance')->middleware('cors');

Route::post('/getEquipments', 'EquipmentsController@getEquipments')->middleware('cors');

Route::post('/getCarrierDropdownItems', 'MiscController@getCarrierDropdownItems')->middleware('cors');

Route::post('/getFactoringCompanies', 'FactoringCompaniesController@getFactoringCompanies')->middleware('cors');
Route::post('/saveFactoringCompany', 'FactoringCompaniesController@saveFactoringCompany')->middleware('cors');

Route::post('/saveDocument', 'CustomerDocumentsController@saveDocument')->middleware('cors');
Route::post('/getDocumentsByCustomer', 'CustomerDocumentsController@getDocumentsByCustomer')->middleware('cors');
Route::post('/deleteCustomerDocument', 'CustomerDocumentsController@deleteCustomerDocument')->middleware('cors');
