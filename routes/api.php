<?php

use App\Http\Controllers\AgentContactsController;
use App\Http\Controllers\AgentHoursController;
use App\Http\Controllers\AgentMailingAddressesController;
use App\Http\Controllers\AgentNotesController;
use App\Http\Controllers\DivisionContactsController;
use App\Http\Controllers\DivisionDocumentsController;
use App\Http\Controllers\DivisionHoursController;
use App\Http\Controllers\DivisionMailingAddressesController;
use App\Http\Controllers\DivisionNotesController;
use App\Http\Controllers\SalesmenController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderCustomerRatingsController;
use App\Http\Controllers\OrderCarrierRatingsController;
use App\Http\Controllers\CarrierMailingAddressesController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CustomerMailingAddressesController;
use App\Http\Controllers\DivisionsController;
use App\Http\Controllers\EventTypesController;
use App\Http\Controllers\LoadTypesController;
use App\Http\Controllers\MileagesController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\RateTypesController;
use App\Http\Controllers\TemplatesController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\CarriersController;
use App\Http\Controllers\CarrierEquipmentsController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\AutomaticEmailsController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\DirectionsController;
use App\Http\Controllers\DriversController;
use App\Http\Controllers\HoursController;
use App\Http\Controllers\InsuranceTypesController;
use App\Http\Controllers\InsurancesController;
use App\Http\Controllers\EquipmentsController;
use App\Http\Controllers\MiscController;
use App\Http\Controllers\FactoringCompaniesController;
use App\Http\Controllers\CustomerDocumentsController;
use App\Http\Controllers\OrderDocumentsController;
use App\Http\Controllers\CarrierDocumentsController;
use App\Http\Controllers\FactoringCompanyDocumentsController;
use App\Http\Controllers\DispatchNotesController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\CompanyMailingAddressesController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\AgentsController;
use App\Http\Controllers\CompanyDriversController;
use App\Http\Controllers\OwnerOperatorsController;
use App\Http\Controllers\EmployeeDocumentsController;
use App\Http\Controllers\AgentDocumentsController;
use App\Http\Controllers\DriverDocumentsController;
use App\Http\Controllers\OperatorDocumentsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailsController;

Route::post('/customers', [CustomersController::class, 'customers'])->name('customers');
Route::post('/getCustomerById', [CustomersController::class, 'getCustomerById']);
Route::post('/customerSearch', [CustomersController::class, 'customerSearch']);
Route::post('/getFullCustomers', [CustomersController::class, 'getFullCustomers'])->name('customers');
Route::post('/saveCustomer', [CustomersController::class, 'saveCustomer']);
Route::post('/submitCustomerImport', [CustomersController::class, 'submitCustomerImport']);
Route::post('/submitCustomerImport2', [CustomersController::class, 'submitCustomerImport2']);
Route::post('/getCustomerPayload', [CustomersController::class, 'getCustomerPayload']);
Route::post('/getCustomerOrders', [CustomersController::class, 'getCustomerOrders']);
Route::post('/customerTest', [CustomersController::class, 'customerTest']);

Route::post('/getAutomaticEmails', [AutomaticEmailsController::class, 'getAutomaticEmails']);
Route::post('/saveAutomaticEmails', [AutomaticEmailsController::class, 'saveAutomaticEmails']);
Route::post('/removeAutomaticEmail', [AutomaticEmailsController::class, 'removeAutomaticEmail']);

Route::post('/getCarrierById', [CarriersController::class, 'getCarrierById']);
Route::post('/carriers', [CarriersController::class, 'carriers'])->name('carriers');
Route::post('/carrierSearch', [CarriersController::class, 'carrierSearch']);
Route::post('/getFullCarriers', [CarriersController::class, 'getFullCarriers']);
Route::post('/getCarrierOrders', [CarriersController::class, 'getCarrierOrders']);
Route::post('/saveCarrier', [CarriersController::class, 'saveCarrier']);
Route::post('/submitCarrierImport', [CarriersController::class, 'submitCarrierImport']);
Route::post('/submitCarrierImport2', [CarriersController::class, 'submitCarrierImport2']);
Route::post('/getCarrierPayload', [CarriersController::class, 'getCarrierPayload']);
Route::post('/getCarrierPopupItems', [CarriersController::class, 'getCarrierPopupItems']);
Route::post('/saveCarrierAchWiringInfo', [CarriersController::class, 'saveCarrierAchWiringInfo']);

Route::post('/getMcNumbers', [CarriersController::class, 'getMcNumbers']);
Route::post('/getDotNumbers', [CarriersController::class, 'getDotNumbers']);
Route::post('/getScacNumbers', [CarriersController::class, 'getScacNumbers']);
Route::post('/getFidNumbers', [CarriersController::class, 'getFidNumbers']);

Route::post('/saveCarrierEquipment', [CarrierEquipmentsController::class, 'saveCarrierEquipment']);
Route::post('/deleteCarrierEquipment', [CarrierEquipmentsController::class, 'deleteCarrierEquipment']);
Route::post('/getCarrierEquipments', [CarrierEquipmentsController::class, 'getCarrierEquipments']);

Route::post('/getContacts', [ContactsController::class, 'getContacts']);
Route::post('/customerContactsSearch', [ContactsController::class, 'customerContactsSearch']);
Route::post('/carrierContactsSearch', [ContactsController::class, 'carrierContactsSearch']);
Route::post('/getContactsByEmail', [ContactsController::class, 'getContactsByEmail']);
Route::post('/getContactsByEmailOrName', [ContactsController::class, 'getContactsByEmailOrName']);
Route::post('/contacts', [ContactsController::class, 'contacts'])->name('contacts');
Route::post('/getContactById', [ContactsController::class, 'getContactById']);
Route::post('/getContactsByCustomerId', [ContactsController::class, 'getContactsByCustomerId']);
Route::post('/saveContact', [ContactsController::class, 'saveContact']);
Route::post('/deleteContact', [ContactsController::class, 'deleteContact']);
Route::post('/uploadAvatar', [ContactsController::class, 'uploadAvatar']);
Route::post('/removeAvatar', [ContactsController::class, 'removeAvatar']);

Route::post('/getCarrierContacts', [ContactsController::class, 'getCarrierContacts']);
Route::post('/carrierContacts', [ContactsController::class, 'carrierContacts'])->name('contacts');
Route::post('/getCarrierContactById', [ContactsController::class, 'getCarrierContactById']);
Route::post('/getCarrierContactsByCustomerId', [ContactsController::class, 'getCarrierContactsByCustomerId']);
Route::post('/saveCarrierContact', [ContactsController::class, 'saveCarrierContact']);
Route::post('/deleteCarrierContact', [ContactsController::class, 'deleteCarrierContact']);
Route::post('/uploadCarrierAvatar', [ContactsController::class, 'uploadCarrierAvatar']);
Route::post('/removeCarrierAvatar', [ContactsController::class, 'removeCarrierAvatar']);

Route::post('/saveFactoringCompanyContact', [ContactsController::class, 'saveFactoringCompanyContact']);
Route::post('/deleteFactoringCompanyContact', [ContactsController::class, 'deleteFactoringCompanyContact']);
Route::post('/uploadFactoringCompanyAvatar', [ContactsController::class, 'uploadFactoringCompanyAvatar']);
Route::post('/removeFactoringCompanyAvatar', [ContactsController::class, 'removeFactoringCompanyAvatar']);
Route::post('/factoringCompanyContactsSearch', [ContactsController::class, 'factoringCompanyContactsSearch']);

Route::post('/getContactList', [ContactsController::class, 'getContactList']);
Route::post('/saveExtCustomerContact', [ContactsController::class, 'saveExtCustomerContact']);

Route::post('/notes', [NotesController::class, 'notes'])->name('notes');
Route::post('/saveNote', [NotesController::class, 'saveNote']);
Route::post('/saveCustomerNote', [NotesController::class, 'saveCustomerNote']);
Route::post('/deleteCustomerNote', [NotesController::class, 'deleteCustomerNote']);

Route::post('/carrierNotes', [NotesController::class, 'carrierNotes'])->name('notes');
Route::post('/saveCarrierNote', [NotesController::class, 'saveCarrierNote']);
Route::post('/deleteCarrierNote', [NotesController::class, 'deleteCarrierNote']);

Route::post('/directions', [DirectionsController::class, 'directions'])->name('directions');
Route::post('/saveDirection', [DirectionsController::class, 'saveDirection']);
Route::post('/saveCustomerDirection', [DirectionsController::class, 'saveCustomerDirection']);
Route::post('/deleteDirection', [DirectionsController::class, 'deleteDirection']);
Route::post('/deleteCustomerDirection', [DirectionsController::class, 'deleteCustomerDirection']);

Route::post('/getDriversByCarrierId', [DriversController::class, 'getDriversByCarrierId']);
Route::post('/saveCarrierDriver', [DriversController::class, 'saveCarrierDriver']);
Route::post('/deleteCarrierDriver', [DriversController::class, 'deleteCarrierDriver']);

Route::post('/saveCustomerHours', [HoursController::class, 'saveCustomerHours']);

Route::post('/getInsuranceTypes', [InsuranceTypesController::class, 'getInsuranceTypes']);
Route::post('/saveInsuranceType', [InsuranceTypesController::class, 'saveInsuranceType']);
Route::post('/deleteInsuranceType', [InsuranceTypesController::class, 'deleteInsuranceType']);

Route::post('/getInsurances', [InsurancesController::class, 'getInsurances']);
Route::post('/saveInsurance', [InsurancesController::class, 'saveInsurance']);
Route::post('/deleteInsurance', [InsurancesController::class, 'deleteInsurance']);
Route::post('/getInsuranceCompanies', [InsurancesController::class, 'getInsuranceCompanies']);

Route::post('/getEquipments', [EquipmentsController::class, 'getEquipments']);

Route::post('/getCarrierDropdownItems', [MiscController::class, 'getCarrierDropdownItems']);

Route::post('/getFactoringCompanyById', [FactoringCompaniesController::class, 'getFactoringCompanyById']);
Route::post('/factoringCompanies', [FactoringCompaniesController::class, 'factoringCompanies']);
Route::post('/saveFactoringCompany', [FactoringCompaniesController::class, 'saveFactoringCompany']);
Route::post('/factoringCompanySearch', [FactoringCompaniesController::class, 'factoringCompanySearch']);
Route::post('/saveFactoringCompanyMailingAddress', [FactoringCompaniesController::class, 'saveFactoringCompanyMailingAddress']);
Route::post('/deleteFactoringCompanyMailingAddress', [FactoringCompaniesController::class, 'deleteFactoringCompanyMailingAddress']);
Route::post('/saveFactoringCompanyNotes', [FactoringCompaniesController::class, 'saveFactoringCompanyNotes']);
Route::post('/saveFactoringCompanyAchWiringInfo', [FactoringCompaniesController::class, 'saveFactoringCompanyAchWiringInfo']);
Route::post('/getFactoringCompanyOutstandingInvoices', [FactoringCompaniesController::class, 'getFactoringCompanyOutstandingInvoices']);

Route::post('/saveCustomerDocument', [CustomerDocumentsController::class, 'saveCustomerDocument']);
Route::post('/getDocumentsByCustomer', [CustomerDocumentsController::class, 'getDocumentsByCustomer']);
Route::post('/deleteCustomerDocument', [CustomerDocumentsController::class, 'deleteCustomerDocument']);
Route::post('/getNotesByCustomerDocument', [CustomerDocumentsController::class, 'getNotesByCustomerDocument']);
Route::post('/saveCustomerDocumentNote', [CustomerDocumentsController::class, 'saveCustomerDocumentNote']);
Route::post('/deleteCustomerDocumentNote', [CustomerDocumentsController::class, 'deleteCustomerDocumentNote']);

Route::post('/saveCarrierDocument', [CarrierDocumentsController::class, 'saveCarrierDocument']);
Route::post('/getDocumentsByCarrier', [CarrierDocumentsController::class, 'getDocumentsByCarrier']);
Route::post('/deleteCarrierDocument', [CarrierDocumentsController::class, 'deleteCarrierDocument']);
Route::post('/getNotesByCarrierDocument', [CarrierDocumentsController::class, 'getNotesByCarrierDocument']);
Route::post('/saveCarrierDocumentNote', [CarrierDocumentsController::class, 'saveCarrierDocumentNote']);
Route::post('/deleteCarrierDocumentNote', [CarrierDocumentsController::class, 'deleteCarrierDocumentNote']);

Route::post('/saveFactoringCompanyDocument', [FactoringCompanyDocumentsController::class, 'saveFactoringCompanyDocument']);
Route::post('/getDocumentsByFactoringCompany', [FactoringCompanyDocumentsController::class, 'getDocumentsByFactoringCompany']);
Route::post('/deleteFactoringCompanyDocument', [FactoringCompanyDocumentsController::class, 'deleteFactoringCompanyDocument']);
Route::post('/getNotesByFactoringCompanyDocument', [FactoringCompanyDocumentsController::class, 'getNotesByFactoringCompanyDocument']);
Route::post('/saveFactoringCompanyDocumentNote', [FactoringCompanyDocumentsController::class, 'saveFactoringCompanyDocumentNote']);
Route::post('/deleteFactoringCompanyDocumentNote', [FactoringCompanyDocumentsController::class, 'deleteFactoringCompanyDocumentNote']);

Route::post('/saveOrderDocument', [OrderDocumentsController::class, 'saveOrderDocument']);
Route::post('/getDocumentsByOrder', [OrderDocumentsController::class, 'getDocumentsByOrder']);
Route::post('/deleteOrderDocument', [OrderDocumentsController::class, 'deleteOrderDocument']);
Route::post('/getNotesByOrderDocument', [OrderDocumentsController::class, 'getNotesByOrderDocument']);
Route::post('/saveOrderDocumentNote', [OrderDocumentsController::class, 'saveOrderDocumentNote']);
Route::post('/deleteOrderDocumentNote', [OrderDocumentsController::class, 'deleteOrderDocumentNote']);

Route::post('/saveOrderBillingDocument', [OrderDocumentsController::class, 'saveOrderBillingDocument']);
Route::post('/getOrderBillingDocumentsByOrder', [OrderDocumentsController::class, 'getOrderBillingDocumentsByOrder']);
Route::post('/deleteOrderBillingDocument', [OrderDocumentsController::class, 'deleteOrderBillingDocument']);
Route::post('/getNotesByOrderBillingDocument', [OrderDocumentsController::class, 'getNotesByOrderBillingDocument']);
Route::post('/saveOrderBillingDocumentNote', [OrderDocumentsController::class, 'saveOrderBillingDocumentNote']);
Route::post('/deleteOrderBillingDocumentNote', [OrderDocumentsController::class, 'deleteOrderBillingDocumentNote']);

Route::post('/saveOrderInvoiceCarrierDocument', [OrderDocumentsController::class, 'saveOrderInvoiceCarrierDocument']);
Route::post('/getInvoiceCarrierDocumentsByOrder', [OrderDocumentsController::class, 'getInvoiceCarrierDocumentsByOrder']);
Route::post('/deleteOrderInvoiceCarrierDocument', [OrderDocumentsController::class, 'deleteOrderInvoiceCarrierDocument']);
Route::post('/getNotesByOrderInvoiceCarrierDocument', [OrderDocumentsController::class, 'getNotesByOrderInvoiceCarrierDocument']);
Route::post('/saveOrderInvoiceCarrierDocumentNote', [OrderDocumentsController::class, 'saveOrderInvoiceCarrierDocumentNote']);

Route::post('/getDispatchNotes', [DispatchNotesController::class, 'getDispatchNotes']);
Route::post('/getInternalNotes', [DispatchNotesController::class, 'getInternalNotes']);
Route::post('/saveInternalNotes', [DispatchNotesController::class, 'saveInternalNotes']);
Route::post('/deleteInternalNotes', [DispatchNotesController::class, 'deleteInternalNotes']);
Route::post('/getNotesForCarrier', [DispatchNotesController::class, 'getNotesForCarrier']);
Route::post('/saveNotesForCarrier', [DispatchNotesController::class, 'saveNotesForCarrier']);
Route::post('/deleteNotesForCarrier', [DispatchNotesController::class, 'deleteNotesForCarrier']);
Route::post('/getNotesForDriver', [DispatchNotesController::class, 'getNotesForDriver']);
Route::post('/saveNotesForDriver', [DispatchNotesController::class, 'saveNotesForDriver']);
Route::post('/deleteNotesForDriver', [DispatchNotesController::class, 'deleteNotesForDriver']);
Route::post('/getOrderInvoiceInternalNotes', [DispatchNotesController::class, 'getOrderInvoiceInternalNotes']);
Route::post('/saveOrderInvoiceInternalNotes', [DispatchNotesController::class, 'saveOrderInvoiceInternalNotes']);
Route::post('/getOrderBillingNotes', [DispatchNotesController::class, 'getOrderBillingNotes']);
Route::post('/saveOrderBillingNotes', [DispatchNotesController::class, 'saveOrderBillingNotes']);

Route::post('/saveCustomerMailingAddress', [CustomerMailingAddressesController::class, 'saveCustomerMailingAddress']);
Route::post('/deleteCustomerMailingAddress', [CustomerMailingAddressesController::class, 'deleteCustomerMailingAddress']);
Route::post('/getCustomerMailingAddressByCode', [CustomerMailingAddressesController::class, 'getCustomerMailingAddressByCode']);

Route::post('/saveCarrierMailingAddress', [CarrierMailingAddressesController::class, 'saveCarrierMailingAddress']);
Route::post('/deleteCarrierMailingAddress', [CarrierMailingAddressesController::class, 'deleteCarrierMailingAddress']);

Route::post('/getAgentHours', [AgentHoursController::class, 'getAgentHours']);
Route::post('/saveAgentHours', [AgentHoursController::class, 'saveAgentHours']);

Route::post('/getOrders', [OrdersController::class, 'getOrders']);
Route::post('/getOrders2', [OrdersController::class, 'getOrders2']);
Route::post('/getOrderById', [OrdersController::class, 'getOrderById']);
Route::post('/getOrderByOrderNumber', [OrdersController::class, 'getOrderByOrderNumber']);
Route::post('/getOrderByTripNumber', [OrdersController::class, 'getOrderByTripNumber']);
Route::post('/getLastOrderNumber', [OrdersController::class, 'getLastOrderNumber']);
Route::post('/saveOrder', [OrdersController::class, 'saveOrder']);
Route::post('/saveOrderEvent', [OrdersController::class, 'saveOrderEvent']);
Route::post('/removeOrderPickup', [OrdersController::class, 'removeOrderPickup']);
Route::post('/removeOrderDelivery', [OrdersController::class, 'removeOrderDelivery']);
Route::post('/saveOrderPickup', [OrdersController::class, 'saveOrderPickup']);
Route::post('/saveTemplateOrderPickup', [OrdersController::class, 'saveTemplateOrderPickup']);
Route::post('/saveOrderDelivery', [OrdersController::class, 'saveOrderDelivery']);
Route::post('/saveTemplateOrderDelivery', [OrdersController::class, 'saveTemplateOrderDelivery']);
Route::post('/saveOrderRouting', [OrdersController::class, 'saveOrderRouting']);
Route::post('/saveTemplateOrderRouting', [OrdersController::class, 'saveTemplateOrderRouting']);
Route::post('/getOrdersRelatedData', [OrdersController::class, 'getOrdersRelatedData']);
Route::post('/submitOrderImport', [OrdersController::class, 'submitOrderImport']);
Route::post('/submitOrderImport2', [OrdersController::class, 'submitOrderImport2']);
Route::post('/arrayTest', [OrdersController::class, 'arrayTest']);

Route::post('/getDivisions', [DivisionsController::class, 'getDivisions']);
Route::post('/getSalesmen', [SalesmenController::class, 'getSalesmen']);
Route::post('/getDivisionById', [DivisionsController::class, 'getDivisionById']);
Route::post('/divisionSearch', [DivisionsController::class, 'divisionSearch']);
Route::post('/getDivisionOrders', [DivisionsController::class, 'getDivisionOrders']);
Route::post('/saveDivision', [DivisionsController::class, 'saveDivision']);

Route::post('/getDivisionContacts', [DivisionContactsController::class, 'getDivisionContacts']);
Route::post('/divisionContactsSearch', [DivisionContactsController::class, 'divisionContactsSearch']);
Route::post('/getDivisionContactsByEmail', [DivisionContactsController::class, 'getDivisionContactsByEmail']);
Route::post('/getDivisionContactsByEmailOrName', [DivisionContactsController::class, 'getDivisionContactsByEmailOrName']);
Route::post('/divisionContacts', [DivisionContactsController::class, 'divisionContacts']);
Route::post('/getDivisionContactById', [DivisionContactsController::class, 'getDivisionContactById']);
Route::post('/getContactsByDivisionId', [DivisionContactsController::class, 'getContactsByDivisionId']);
Route::post('/saveDivisionContact', [DivisionContactsController::class, 'saveDivisionContact']);
Route::post('/uploadDivisionContactAvatar', [DivisionContactsController::class, 'uploadDivisionContactAvatar']);
Route::post('/removeDivisioContactAvatar', [DivisionContactsController::class, 'removeDivisioContactAvatar']);
Route::post('/deleteDivisionContact', [DivisionContactsController::class, 'deleteDivisionContact']);

Route::post('/getDivisionNotes', [DivisionNotesController::class, 'getDivisionNotes']);
Route::post('/getDivisionNoteById', [DivisionNotesController::class, 'getDivisionNoteById']);
Route::post('/saveDivisionNote', [DivisionNotesController::class, 'saveDivisionNote']);
Route::post('/deleteDivisionNote', [DivisionNotesController::class, 'deleteDivisionNote']);

Route::post('/saveDivisionMailingAddress', [DivisionMailingAddressesController::class, 'saveDivisionMailingAddress']);
Route::post('/deleteDivisionMailingAddress', [DivisionMailingAddressesController::class, 'deleteDivisionMailingAddress']);

Route::post('/getDivisionHours', [DivisionHoursController::class, 'getDivisionHours']);
Route::post('/saveDivisionHours', [DivisionHoursController::class, 'saveDivisionHours']);

Route::post('/getDocumentsByDivision', [DivisionDocumentsController::class, 'getDocumentsByDivision']);
Route::post('/saveDivisionDocument', [DivisionDocumentsController::class, 'saveDivisionDocument']);
Route::post('/deleteDivisionDocument', [DivisionDocumentsController::class, 'deleteDivisionDocument']);
Route::post('/getNotesByDivisionDocument', [DivisionDocumentsController::class, 'getNotesByDivisionDocument']);
Route::post('/saveDivisionDocumentNote', [DivisionDocumentsController::class, 'saveDivisionDocumentNote']);
Route::post('/deleteDivisionDocumentNote', [DivisionDocumentsController::class, 'deleteDivisionDocumentNote']);

Route::post('/getEventTypes', [EventTypesController::class, 'getEventTypes']);
Route::post('/getLoadTypes', [LoadTypesController::class, 'getLoadTypes']);
Route::post('/getTemplates', [TemplatesController::class, 'getTemplates']);
Route::post('/getRateTypes', [RateTypesController::class, 'getRateTypes']);
Route::post('/getRateSubtypes', [RateTypesController::class, 'getRateSubtypes']);
Route::post('/getTerms', [TermsController::class, 'getTerms']);
Route::post('/getConfig', [ConfigController::class, 'getConfig']);
Route::post('/saveConfig', [ConfigController::class, 'saveConfig']);
Route::post('/getMileage', [MileagesController::class, 'getMileage']);
Route::post('/saveMileage', [MileagesController::class, 'saveMileage']);

Route::post('/getOrderCustomerRatings', [OrderCustomerRatingsController::class, 'getOrderCustomerRatings']);
Route::post('/saveOrderCustomerRating', [OrderCustomerRatingsController::class, 'saveOrderCustomerRating']);
Route::post('/deleteOrderCustomerRating', [OrderCustomerRatingsController::class, 'deleteOrderCustomerRating']);

Route::post('/getRevenueCustomer', [OrdersController::class, 'getRevenueCustomer']);
Route::post('/getOrderHistoryCustomer', [OrdersController::class, 'getOrderHistoryCustomer']);
Route::post('/getRevenueCarrier', [OrdersController::class, 'getRevenueCarrier']);
Route::post('/getOrderHistoryCarrier', [OrdersController::class, 'getOrderHistoryCarrier']);

Route::post('/getOrderCarrierRatings', [OrderCarrierRatingsController::class, 'getOrderCarrierRatings']);
Route::post('/saveOrderCarrierRating', [OrderCarrierRatingsController::class, 'saveOrderCarrierRating']);
Route::post('/deleteOrderCarrierRating', [OrderCarrierRatingsController::class, 'deleteOrderCarrierRating']);

Route::post('/getCompanyById', [CompaniesController::class, 'getCompanyById']);
Route::post('/companies', [CompaniesController::class, 'companies']);
Route::post('/saveCompany', [CompaniesController::class, 'saveCompany']);
Route::post('/removeCompany', [CompaniesController::class, 'removeCompany']);
Route::post('/uploadCompanyLogo', [CompaniesController::class, 'uploadCompanyLogo']);
Route::post('/removeCompanyLogo', [CompaniesController::class, 'removeCompanyLogo']);

Route::post('/saveCompanyMailingAddress', [CompanyMailingAddressesController::class, 'saveCompanyMailingAddress']);
Route::post('/deleteCompanyMailingAddress', [CompanyMailingAddressesController::class, 'deleteCompanyMailingAddress']);

Route::post('/getEmployees', [EmployeesController::class, 'getEmployees']);
Route::post('/saveEmployee', [EmployeesController::class, 'saveEmployee']);
Route::post('/deleteEmployee', [EmployeesController::class, 'deleteEmployee']);
Route::post('/uploadEmployeeAvatar', [EmployeesController::class, 'uploadAvatar']);
Route::post('/removeEmployeeAvatar', [EmployeesController::class, 'removeAvatar']);
Route::post('/companyEmployeesSearch', [EmployeesController::class, 'companyEmployeesSearch']);
Route::post('/resetEmployeePassword', [EmployeesController::class, 'resetEmployeePassword']);

Route::post('/getAgentById', [AgentsController::class, 'getAgentById']);
Route::post('/getAgents', [AgentsController::class, 'getAgents']);
Route::post('/agentSearch', [AgentsController::class, 'agentSearch']);
Route::post('/getAgentOrders', [AgentsController::class, 'getAgentOrders']);
Route::post('/saveAgent', [AgentsController::class, 'saveAgent']);

Route::post('/saveAgentMailingAddress', [AgentMailingAddressesController::class, 'saveAgentMailingAddress']);
Route::post('/deleteAgentMailingAddress', [AgentMailingAddressesController::class, 'deleteAgentMailingAddress']);

Route::post('/getAgentNotes', [AgentNotesController::class, 'getAgentNotes']);
Route::post('/getAgentNoteById', [AgentNotesController::class, 'getAgentNoteById']);
Route::post('/saveAgentNote', [AgentNotesController::class, 'saveAgentNote']);
Route::post('/deleteAgentNote', [AgentNotesController::class, 'deleteAgentNote']);

Route::post('/getAgentContacts', [AgentContactsController::class, 'getAgentContacts']);
Route::post('/agentContactsSearch', [AgentContactsController::class, 'agentContactsSearch']);
Route::post('/getAgentContactsByEmail', [AgentContactsController::class, 'getAgentContactsByEmail']);
Route::post('/getAgentContactsByEmailOrName', [AgentContactsController::class, 'getAgentContactsByEmailOrName']);
Route::post('/agentContacts', [AgentContactsController::class, 'agentContacts']);
Route::post('/getAgentContactById', [AgentContactsController::class, 'getAgentContactById']);
Route::post('/getContactsByAgentId', [AgentContactsController::class, 'getContactsByAgentId']);
Route::post('/saveAgentContact', [AgentContactsController::class, 'saveAgentContact']);
Route::post('/uploadAgentContactAvatar', [AgentContactsController::class, 'uploadAgentContactAvatar']);
Route::post('/removeDivisioContactAvatar', [AgentContactsController::class, 'removeDivisioContactAvatar']);
Route::post('/deleteAgentContact', [AgentContactsController::class, 'deleteAgentContact']);
Route::post('/resetAgentContactPassword', [AgentContactsController::class, 'resetAgentContactPassword']);



Route::post('/saveDriver', [CompanyDriversController::class, 'saveDriver']);
Route::post('/deleteDriver', [CompanyDriversController::class, 'deleteDriver']);
Route::post('/uploadDriverAvatar', [CompanyDriversController::class, 'uploadAvatar']);
Route::post('/removeDriverAvatar', [CompanyDriversController::class, 'removeAvatar']);
Route::post('/companyDriversSearch', [CompanyDriversController::class, 'companyDriversSearch']);

Route::post('/saveOperator', [OwnerOperatorsController::class, 'saveOperator']);
Route::post('/deleteOperator', [OwnerOperatorsController::class, 'deleteOperator']);
Route::post('/uploadOperatorAvatar', [OwnerOperatorsController::class, 'uploadAvatar']);
Route::post('/removeOperatorAvatar', [OwnerOperatorsController::class, 'removeAvatar']);
Route::post('/companyOperatorsSearch', [OwnerOperatorsController::class, 'companyOperatorsSearch']);

Route::post('/saveEmployeeDocument', [EmployeeDocumentsController::class, 'saveEmployeeDocument']);
Route::post('/getDocumentsByEmployee', [EmployeeDocumentsController::class, 'getDocumentsByEmployee']);
Route::post('/deleteEmployeeDocument', [EmployeeDocumentsController::class, 'deleteEmployeeDocument']);
Route::post('/getNotesByEmployeeDocument', [EmployeeDocumentsController::class, 'getNotesByEmployeeDocument']);
Route::post('/saveEmployeeDocumentNote', [EmployeeDocumentsController::class, 'saveEmployeeDocumentNote']);
Route::post('/deleteEmployeeDocumentNote', [EmployeeDocumentsController::class, 'deleteEmployeeDocumentNote']);

Route::post('/saveAgentDocument', [AgentDocumentsController::class, 'saveAgentDocument']);
Route::post('/getDocumentsByAgent', [AgentDocumentsController::class, 'getDocumentsByAgent']);
Route::post('/deleteAgentDocument', [AgentDocumentsController::class, 'deleteAgentDocument']);
Route::post('/getNotesByAgentDocument', [AgentDocumentsController::class, 'getNotesByAgentDocument']);
Route::post('/saveAgentDocumentNote', [AgentDocumentsController::class, 'saveAgentDocumentNote']);
Route::post('/deleteAgentDocumentNote', [AgentDocumentsController::class, 'deleteAgentDocumentNote']);

Route::post('/saveDriverDocument', [DriverDocumentsController::class, 'saveDriverDocument']);
Route::post('/getDocumentsByDriver', [DriverDocumentsController::class, 'getDocumentsByDriver']);
Route::post('/deleteDriverDocument', [DriverDocumentsController::class, 'deleteDriverDocument']);
Route::post('/getNotesByDriverDocument', [DriverDocumentsController::class, 'getNotesByDriverDocument']);
Route::post('/saveDriverDocumentNote', [DriverDocumentsController::class, 'saveDriverDocumentNote']);
Route::post('/deleteDriverDocumentNote', [DriverDocumentsController::class, 'deleteDriverDocumentNote']);

Route::post('/saveOperatorDocument', [OperatorDocumentsController::class, 'saveOperatorDocument']);
Route::post('/getDocumentsByOperator', [OperatorDocumentsController::class, 'getDocumentsByOperator']);
Route::post('/deleteOperatorDocument', [OperatorDocumentsController::class, 'deleteOperatorDocument']);
Route::post('/getNotesByOperatorDocument', [OperatorDocumentsController::class, 'getNotesByOperatorDocument']);
Route::post('/saveOperatorDocumentNote', [OperatorDocumentsController::class, 'saveOperatorDocumentNote']);
Route::post('/deleteOperatorDocumentNote', [OperatorDocumentsController::class, 'deleteOperatorDocumentNote']);

Route::post('/sendRateConfEmail', [EmailsController::class, 'sendRateConfEmail']);
Route::post('/sendBookedLoadEmail', [EmailsController::class, 'sendBookedLoadEmail']);
Route::post('/sendCarrierArrivedShipperEmail', [EmailsController::class, 'sendCarrierArrivedShipperEmail']);
Route::post('/sendCarrierArrivedConsigneeEmail', [EmailsController::class, 'sendCarrierArrivedConsigneeEmail']);
Route::post('/sendCarrierLoadedShipperEmail', [EmailsController::class, 'sendCarrierLoadedShipperEmail']);
Route::post('/sendCarrierUnloadedConsigneeEmail', [EmailsController::class, 'sendCarrierUnloadedConsigneeEmail']);

Route::post('/testPdf', [EmailsController::class, 'testPdf']);
Route::get('/testView', function (){
    return view('mails.rate-conf.rate_conf_template');
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/generatePass', [AuthController::class, 'generatePass']);
Route::post('/checkPass', [AuthController::class, 'checkPass']);
Route::middleware('auth:sanctum')->group(function (){
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});



Route::get('/company-logo/{filename}', function($filename){
    $path = public_path('company-logo/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'Image not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::get('/avatars/{filename}', function($filename){
    $path = public_path('avatars/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'Image not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return $response;
});

Route::get('/agent-documents/{filename}', function($filename){
    $path = public_path('agent-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/carrier-documents/{filename}', function($filename){
    $path = public_path('carrier-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/customer-documents/{filename}', function($filename){
    $path = public_path('customer-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/division-documents/{filename}', function($filename){
    $path = public_path('division-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/driver-documents/{filename}', function($filename){
    $path = public_path('driver-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/employee-documents/{filename}', function($filename){
    $path = public_path('employee-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/factoring-company-documents/{filename}', function($filename){
    $path = public_path('factoring-company-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/operator-documents/{filename}', function($filename){
    $path = public_path('operator-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/order-billing-documents/{filename}', function($filename){
    $path = public_path('order-billing-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/order-documents/{filename}', function($filename){
    $path = public_path('order-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});

Route::get('/order-invoice-carrier-documents/{filename}', function($filename){
    $path = public_path('order-invoice-carrier-documents/' . $filename);

    if(!File::exists($path)) {
        return response()->json(['message' => 'File not found.'], 404);
    }

    $file = File::get($path);
    $type = File::mimeType($path);

    $response = Response::make($file, 200);
    $response->header("Content-Type", $type);

    return response()->file($path);
});
