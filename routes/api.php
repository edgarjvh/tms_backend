<?php

use App\Http\Controllers\AgentContactsController;
use App\Http\Controllers\AgentHoursController;
use App\Http\Controllers\AgentMailingAddressesController;
use App\Http\Controllers\AgentNotesController;
use App\Http\Controllers\DriverLicenseDocumentsController;
use App\Http\Controllers\DriverMedicalCardDocumentsController;
use App\Http\Controllers\DriverTractorDocumentsController;
use App\Http\Controllers\DriverTrailerDocumentsController;
use App\Http\Controllers\CompanyOperatorLicenseDocumentsController;
use App\Http\Controllers\CompanyOperatorMedicalCardDocumentsController;
use App\Http\Controllers\CompanyOperatorsController;
use App\Http\Controllers\CompanyOperatorTractorDocumentsController;
use App\Http\Controllers\CompanyOperatorTrailerDocumentsController;
use App\Http\Controllers\DivisionContactsController;
use App\Http\Controllers\DivisionDocumentsController;
use App\Http\Controllers\DivisionHoursController;
use App\Http\Controllers\DivisionMailingAddressesController;
use App\Http\Controllers\DivisionNotesController;
use App\Http\Controllers\SaimeController;
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
use App\Http\Controllers\DriversController;
use App\Http\Controllers\EmployeeDocumentsController;
use App\Http\Controllers\AgentDocumentsController;
use App\Http\Controllers\DriverDocumentsController;
use App\Http\Controllers\OperatorDocumentsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailsController;
use App\Http\Controllers\AgentDriversController;
use App\Http\Controllers\HazmatsController;
use App\Http\Controllers\OrderLtlUnitsController;
use App\Http\Controllers\TruckerToolsController;
use App\Http\Controllers\WidgetsController;

Route::post('/ttLocationUpdate', [TruckerToolsController::class, 'ttLocationUpdate']);
Route::post('/ttCommentUpdate', [TruckerToolsController::class, 'ttCommentUpdate']);
Route::post('/ttDocumentUpdate', [TruckerToolsController::class, 'ttDocumentUpdate']);
Route::post('/ttStatusUpdate', [TruckerToolsController::class, 'ttStatusUpdate']);
Route::post('/testUpdateStatus', [TruckerToolsController::class, 'testUpdateStatus']);
Route::post('/createLoadOnTT', [TruckerToolsController::class, 'createLoadOnTT']);

Route::post('/customers', [CustomersController::class, 'customers'])->name('customers');
Route::post('/getCustomerByCode', [CustomersController::class, 'getCustomerByCode']);
Route::post('/getCustomerById', [CustomersController::class, 'getCustomerById']);
Route::post('/customerSearch', [CustomersController::class, 'customerSearch']);
Route::post('/getCustomerReport', [CustomersController::class, 'getCustomerReport']);
Route::post('/getCustomerOpenInvoicesReport', [CustomersController::class, 'getCustomerOpenInvoicesReport']);
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
Route::post('/updateCarrierTtStatus', [CarriersController::class, 'updateCarrierTtStatus']);
Route::post('/getCarrierByCode', [CarriersController::class, 'getCarrierByCode']);
Route::post('/getCarrierReport', [CarriersController::class, 'getCarrierReport']);
Route::post('/getCarrierOpenInvoicesReport', [CarriersController::class, 'getCarrierOpenInvoicesReport']);
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
Route::post('/getEmailContacts', [ContactsController::class, 'getEmailContacts']);

Route::post('/getCarrierContacts', [ContactsController::class, 'getCarrierContacts']);
Route::post('/getContactsByCarrierId', [ContactsController::class, 'getContactsByCarrierId']);
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
Route::post('/getContactsByFactoringCompanyId', [ContactsController::class, 'getContactsByFactoringCompanyId']);

Route::post('/getContactList', [ContactsController::class, 'getContactList']);
Route::post('/saveExtCustomerContact', [ContactsController::class, 'saveExtCustomerContact']);

Route::post('/getUserContacts', [ContactsController::class, 'getUserContacts']);
Route::post('/saveUserContact', [ContactsController::class, 'saveUserContact']);
Route::post('/deleteUserContact', [ContactsController::class, 'deleteUserContact']);
Route::post('/uploadUserContactAvatar', [ContactsController::class, 'uploadUserContactAvatar']);
Route::post('/removeUserContactAvatar', [ContactsController::class, 'removeUserContactAvatar']);
Route::post('/addToUserContact', [ContactsController::class, 'addToUserContact']);
Route::post('/removeFromUserContact', [ContactsController::class, 'removeFromUserContact']);

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

Route::post('/saveCustomerHours', [HoursController::class, 'saveCustomerHours']);

Route::post('/getInsuranceTypes', [InsuranceTypesController::class, 'getInsuranceTypes']);
Route::post('/saveInsuranceType', [InsuranceTypesController::class, 'saveInsuranceType']);
Route::post('/deleteInsuranceType', [InsuranceTypesController::class, 'deleteInsuranceType']);
Route::post('/getInsuranceCompanies', [InsuranceTypesController::class, 'getInsuranceCompanies']);

Route::post('/getInsurances', [InsurancesController::class, 'getInsurances']);
Route::post('/saveInsurance', [InsurancesController::class, 'saveInsurance']);
Route::post('/deleteInsurance', [InsurancesController::class, 'deleteInsurance']);
Route::post('/getInsuranceCompanies', [InsurancesController::class, 'getInsuranceCompanies']);

Route::post('/getEquipments', [EquipmentsController::class, 'getEquipments']);

Route::post('/getCarrierDropdownItems', [MiscController::class, 'getCarrierDropdownItems']);

Route::post('/getFactoringCompanyById', [FactoringCompaniesController::class, 'getFactoringCompanyById']);
Route::post('/getFactoringCompanyByCode', [FactoringCompaniesController::class, 'getFactoringCompanyByCode']);
Route::post('/factoringCompanies', [FactoringCompaniesController::class, 'factoringCompanies']);
Route::post('/saveFactoringCompany', [FactoringCompaniesController::class, 'saveFactoringCompany']);
Route::post('/factoringCompanySearch', [FactoringCompaniesController::class, 'factoringCompanySearch']);
Route::post('/saveFactoringCompanyMailingAddress', [FactoringCompaniesController::class, 'saveFactoringCompanyMailingAddress']);
Route::post('/deleteFactoringCompanyMailingAddress', [FactoringCompaniesController::class, 'deleteFactoringCompanyMailingAddress']);
Route::post('/saveFactoringCompanyNotes', [FactoringCompaniesController::class, 'saveFactoringCompanyNotes']);
Route::post('/deleteFactoringCompanyNotes', [FactoringCompaniesController::class, 'deleteFactoringCompanyNotes']);
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
Route::post('/getCarrierMailingAddressByCode', [CarrierMailingAddressesController::class, 'getCarrierMailingAddressByCode']);

Route::post('/getAgentHours', [AgentHoursController::class, 'getAgentHours']);
Route::post('/saveAgentHours', [AgentHoursController::class, 'saveAgentHours']);

Route::post('/getWidgets', [WidgetsController::class, 'getWidgets']);
Route::post('/saveWidget', [WidgetsController::class, 'saveWidget']);

Route::post('/getOrders', [OrdersController::class, 'getOrders']);
Route::post('/getLoadBoardOrders', [OrdersController::class, 'getLoadBoardOrders']);
Route::post('/getLoadBoardOrderById', [OrdersController::class, 'getLoadBoardOrderById']);
Route::post('/getOrders2', [OrdersController::class, 'getOrders2']);
Route::post('/getOrderById', [OrdersController::class, 'getOrderById']);
Route::post('/getOrderByOrderNumber', [OrdersController::class, 'getOrderByOrderNumber']);
Route::post('/getOrderByTripNumber', [OrdersController::class, 'getOrderByTripNumber']);
Route::post('/getLastOrderNumber', [OrdersController::class, 'getLastOrderNumber']);
Route::post('/saveOrder', [OrdersController::class, 'saveOrder']);
Route::post('/updateTtInfo', [OrdersController::class, 'updateTtInfo']);
Route::post('/saveOrderEvent', [OrdersController::class, 'saveOrderEvent']);
Route::post('/removeOrderPickup', [OrdersController::class, 'removeOrderPickup']);
Route::post('/removeOrderDelivery', [OrdersController::class, 'removeOrderDelivery']);
Route::post('/saveOrderPickup', [OrdersController::class, 'saveOrderPickup']);
Route::post('/saveOrderDelivery', [OrdersController::class, 'saveOrderDelivery']);
Route::post('/saveOrderRouting', [OrdersController::class, 'saveOrderRouting']);
Route::post('/useTemplate', [OrdersController::class, 'useTemplate']);
Route::post('/deleteTemplate', [OrdersController::class, 'deleteTemplate']);
Route::post('/saveOrderMilesWaypoints', [OrdersController::class, 'saveOrderMilesWaypoints']);
Route::post('/getTemplateById', [OrdersController::class, 'getTemplateById']);
Route::post('/saveTemplate', [OrdersController::class, 'saveTemplate']);
Route::post('/saveTemplatePickup', [OrdersController::class, 'saveTemplatePickup']);
Route::post('/removeTemplatePickup', [OrdersController::class, 'removeTemplatePickup']);
Route::post('/saveTemplateDelivery', [OrdersController::class, 'saveTemplateDelivery']);
Route::post('/removeTemplateDelivery', [OrdersController::class, 'removeTemplateDelivery']);
Route::post('/saveTemplateRouting', [OrdersController::class, 'saveTemplateRouting']);
Route::post('/saveTemplateNotesForCarrier', [OrdersController::class, 'saveTemplateNotesForCarrier']);
Route::post('/deleteTemplateNotesForCarrier', [OrdersController::class, 'deleteTemplateNotesForCarrier']);
Route::post('/saveTemplateInternalNotes', [OrdersController::class, 'saveTemplateInternalNotes']);
Route::post('/deleteTemplateInternalNotes', [OrdersController::class, 'deleteTemplateInternalNotes']);
Route::post('/saveTemplateMilesWaypoints', [OrdersController::class, 'saveTemplateMilesWaypoints']);
Route::post('/getOrdersRelatedData', [OrdersController::class, 'getOrdersRelatedData']);
Route::post('/submitOrderImport', [OrdersController::class, 'submitOrderImport']);
Route::post('/submitOrderImport2', [OrdersController::class, 'submitOrderImport2']);
Route::post('/arrayTest', [OrdersController::class, 'arrayTest']);
Route::post('/saveInvoiceCustomerCheckNumber', [OrdersController::class, 'saveInvoiceCustomerCheckNumber']);
Route::post('/saveInvoiceCustomerDateReceived', [OrdersController::class, 'saveInvoiceCustomerDateReceived']);
Route::post('/saveInvoiceCarrierReceivedDate', [OrdersController::class, 'saveInvoiceCarrierReceivedDate']);
Route::post('/saveInvoiceNumber', [OrdersController::class, 'saveInvoiceNumber']);
Route::post('/saveInvoiceTerm', [OrdersController::class, 'saveInvoiceTerm']);
Route::post('/saveInvoiceDatePaid', [OrdersController::class, 'saveInvoiceDatePaid']);
Route::post('/saveInvoiceCarrierCheckNumber', [OrdersController::class, 'saveInvoiceCarrierCheckNumber']);
Route::post('/saveInvoiceAgentDatePaid', [OrdersController::class, 'saveInvoiceAgentDatePaid']);
Route::post('/saveInvoiceAgentCheckNumber', [OrdersController::class, 'saveInvoiceAgentCheckNumber']);
Route::post('/getOrderCarrierByCode', [OrdersController::class, 'getOrderCarrierByCode']);
Route::post('/getRoutingBol', [OrdersController::class, 'getRoutingBol']);

Route::post('/getLtlUnitById', [OrderLtlUnitsController::class, 'getLtlUnitById']);
Route::post('/getLtlUnitsByOrderId', [OrderLtlUnitsController::class, 'getLtlUnitsByOrderId']);
Route::post('/saveLtlUnit', [OrderLtlUnitsController::class, 'saveLtlUnit']);
Route::post('/deleteLtlUnit', [OrderLtlUnitsController::class, 'deleteLtlUnit']);
Route::post('/getHandlingUnits', [OrderLtlUnitsController::class, 'getHandlingUnits']);
Route::post('/getUnitClasses', [OrderLtlUnitsController::class, 'getUnitClasses']);
Route::post('/getHazmatPackagings', [OrderLtlUnitsController::class, 'getHazmatPackagings']);
Route::post('/getHazmatClasses', [OrderLtlUnitsController::class, 'getHazmatClasses']);
Route::post('/getAccessorials', [OrderLtlUnitsController::class, 'getAccessorials']);
Route::post('/getLtlUnitsAccessorialsByOrderId', [OrderLtlUnitsController::class, 'getLtlUnitsAccessorialsByOrderId']);
Route::post('/saveOrderAccessorials', [OrderLtlUnitsController::class, 'saveOrderAccessorials']);
Route::post('/deleteOrderAccessorial', [OrderLtlUnitsController::class, 'deleteOrderAccessorial']);
Route::post('/getEmergencyContacts', [OrderLtlUnitsController::class, 'getEmergencyContacts']);

Route::post('/getHazmats', [HazmatsController::class, 'getHazmats']);

Route::post('/getDivisions', [DivisionsController::class, 'getDivisions']);
Route::post('/getDivisionsDropdown', [DivisionsController::class, 'getDivisionsDropdown']);
Route::post('/getSalesmen', [SalesmenController::class, 'getSalesmen']);
Route::post('/getDivisionById', [DivisionsController::class, 'getDivisionById']);
Route::post('/getDivisionByCode', [DivisionsController::class, 'getDivisionByCode']);
Route::post('/divisionSearch', [DivisionsController::class, 'divisionSearch']);
Route::post('/getDivisionOrders', [DivisionsController::class, 'getDivisionOrders']);
Route::post('/saveDivision', [DivisionsController::class, 'saveDivision']);
Route::post('/getDivisionsList', [DivisionsController::class, 'getDivisionsList']);

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
Route::post('/getLoadTypesDropdown', [LoadTypesController::class, 'getLoadTypesDropdown']);
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
Route::post('/saveTemplateCustomerRating', [OrderCustomerRatingsController::class, 'saveTemplateCustomerRating']);
Route::post('/deleteTemplateCustomerRating', [OrderCustomerRatingsController::class, 'deleteTemplateCustomerRating']);

Route::post('/getRevenueCustomer', [OrdersController::class, 'getRevenueCustomer']);
Route::post('/getOrderHistoryCustomer', [OrdersController::class, 'getOrderHistoryCustomer']);
Route::post('/getRevenueCarrier', [OrdersController::class, 'getRevenueCarrier']);
Route::post('/getOrderHistoryCarrier', [OrdersController::class, 'getOrderHistoryCarrier']);
Route::post('/getRevenueDivision', [OrdersController::class, 'getRevenueDivision']);
Route::post('/getOrderHistoryDivision', [OrdersController::class, 'getOrderHistoryDivision']);

Route::post('/getOrderCarrierRatings', [OrderCarrierRatingsController::class, 'getOrderCarrierRatings']);
Route::post('/saveOrderCarrierRating', [OrderCarrierRatingsController::class, 'saveOrderCarrierRating']);
Route::post('/deleteOrderCarrierRating', [OrderCarrierRatingsController::class, 'deleteOrderCarrierRating']);
Route::post('/saveTemplateCarrierRating', [OrderCarrierRatingsController::class, 'saveTemplateCarrierRating']);
Route::post('/deleteTemplateCarrierRating', [OrderCarrierRatingsController::class, 'deleteTemplateCarrierRating']);

Route::post('/getCompanyById', [CompaniesController::class, 'getCompanyById']);
Route::post('/companies', [CompaniesController::class, 'companies']);
Route::post('/saveCompany', [CompaniesController::class, 'saveCompany']);
Route::post('/removeCompany', [CompaniesController::class, 'removeCompany']);
Route::post('/uploadCompanyLogo', [CompaniesController::class, 'uploadCompanyLogo']);
Route::post('/removeCompanyLogo', [CompaniesController::class, 'removeCompanyLogo']);

Route::post('/saveCompanyMailingAddress', [CompanyMailingAddressesController::class, 'saveCompanyMailingAddress']);
Route::post('/deleteCompanyMailingAddress', [CompanyMailingAddressesController::class, 'deleteCompanyMailingAddress']);

Route::post('/getEmployees', [EmployeesController::class, 'getEmployees']);
Route::post('/getEmployeeById', [EmployeesController::class, 'getEmployeeById']);
Route::post('/saveEmployee', [EmployeesController::class, 'saveEmployee']);
Route::post('/deleteEmployee', [EmployeesController::class, 'deleteEmployee']);
Route::post('/uploadEmployeeAvatar', [EmployeesController::class, 'uploadAvatar']);
Route::post('/removeEmployeeAvatar', [EmployeesController::class, 'removeAvatar']);
Route::post('/companyEmployeesSearch', [EmployeesController::class, 'companyEmployeesSearch']);
Route::post('/resetEmployeePassword', [EmployeesController::class, 'resetEmployeePassword']);

Route::post('/getAgentById', [AgentsController::class, 'getAgentById']);
Route::post('/getAgents', [AgentsController::class, 'getAgents']);
Route::post('/getAgentReport', [AgentsController::class, 'getAgentReport']);
Route::post('/agentSearch', [AgentsController::class, 'agentSearch']);
Route::post('/getAgentOrders', [AgentsController::class, 'getAgentOrders']);
Route::post('/saveAgent', [AgentsController::class, 'saveAgent']);
Route::post('/getAgentRevenue', [AgentsController::class, 'getAgentRevenue']);
Route::post('/saveAgentAchWiringInfo', [AgentsController::class, 'saveAgentAchWiringInfo']);

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

Route::post('/getDriverById', [DriversController::class, 'getDriverById']);
Route::post('/getDrivers', [DriversController::class, 'getDrivers']);
Route::post('/getDriverByCode', [DriversController::class, 'getDriverByCode']);
Route::post('/saveDriver', [DriversController::class, 'saveDriver']);
Route::post('/deleteDriver', [DriversController::class, 'deleteDriver']);
Route::post('/uploadDriverAvatar', [DriversController::class, 'uploadAvatar']);
Route::post('/removeDriverAvatar', [DriversController::class, 'removeAvatar']);
Route::post('/companyDriversSearch', [DriversController::class, 'companyDriversSearch']);
Route::post('/saveDriverMailingAddress', [DriversController::class, 'saveDriverMailingAddress']);
Route::post('/deleteDriverMailingAddress', [DriversController::class, 'deleteDriverMailingAddress']);
Route::post('/getDriverEmergencyContact', [DriversController::class, 'getDriverEmergencyContact']);
Route::post('/saveDriverEmergencyContact', [DriversController::class, 'saveDriverEmergencyContact']);
Route::post('/deleteDriverEmergencyContact', [DriversController::class, 'deleteDriverEmergencyContact']);
Route::post('/uploadDriverEmergencyContactAvatar', [DriversController::class, 'uploadDriverEmergencyContactAvatar']);
Route::post('/removeDriverEmergencyContactAvatar', [DriversController::class, 'removeDriverEmergencyContactAvatar']);
Route::post('/getRelationships', [DriversController::class, 'getRelationships']);
Route::post('/getDriverLicense', [DriversController::class, 'getDriverLicense']);
Route::post('/saveDriverLicense', [DriversController::class, 'saveDriverLicense']);
Route::post('/deleteDriverLicense', [DriversController::class, 'deleteDriverLicense']);
Route::post('/getLicenseEndorsements', [DriversController::class, 'getLicenseEndorsements']);
Route::post('/getLicenseClasses', [DriversController::class, 'getLicenseClasses']);
Route::post('/getLicenseRestrictions', [DriversController::class, 'getLicenseRestrictions']);
Route::post('/uploadDriverLicenseImage', [DriversController::class, 'uploadDriverLicenseImage']);
Route::post('/removeDriverLicenseImage', [DriversController::class, 'removeDriverLicenseImage']);
Route::post('/getDriverMedicalCard', [DriversController::class, 'getDriverMedicalCard']);
Route::post('/saveDriverMedicalCard', [DriversController::class, 'saveDriverMedicalCard']);
Route::post('/deleteDriverMedicalCard', [DriversController::class, 'deleteDriverMedicalCard']);
Route::post('/uploadDriverMedicalCardImage', [DriversController::class, 'uploadDriverMedicalCardImage']);
Route::post('/removeDriverMedicalCardImage', [DriversController::class, 'removeDriverMedicalCardImage']);
Route::post('/getDriverTractor', [DriversController::class, 'getDriverTractor']);
Route::post('/saveDriverTractor', [DriversController::class, 'saveDriverTractor']);
Route::post('/deleteDriverTractor', [DriversController::class, 'deleteDriverTractor']);
Route::post('/getDriverTrailer', [DriversController::class, 'getDriverTrailer']);
Route::post('/saveDriverTrailer', [DriversController::class, 'saveDriverTrailer']);
Route::post('/deleteDriverTrailer', [DriversController::class, 'deleteDriverTrailer']);
Route::post('/getDriversByCarrierId', [DriversController::class, 'getDriversByCarrierId']);
Route::post('/getContactsByDriverId', [DriversController::class, 'getContactsByDriverId']);


Route::post('/getDocumentsByDriverLicense', [DriverLicenseDocumentsController::class, 'getDocumentsByDriverLicense']);
Route::post('/saveDriverLicenseDocument', [DriverLicenseDocumentsController::class, 'saveDriverLicenseDocument']);
Route::post('/deleteDriverLicenseDocument', [DriverLicenseDocumentsController::class, 'deleteDriverLicenseDocument']);
Route::post('/getNotesByDriverLicenseDocument', [DriverLicenseDocumentsController::class, 'getNotesByDriverLicenseDocument']);
Route::post('/saveDriverLicenseDocumentNote', [DriverLicenseDocumentsController::class, 'saveDriverLicenseDocumentNote']);
Route::post('/deleteDriverLicenseDocumentNote', [DriverLicenseDocumentsController::class, 'deleteDriverLicenseDocumentNote']);

Route::post('/getDocumentsByDriverMedicalCard', [DriverMedicalCardDocumentsController::class, 'getDocumentsByDriverMedicalCard']);
Route::post('/saveDriverMedicalCardDocument', [DriverMedicalCardDocumentsController::class, 'saveDriverMedicalCardDocument']);
Route::post('/deleteDriverMedicalCardDocument', [DriverMedicalCardDocumentsController::class, 'deleteDriverMedicalCardDocument']);
Route::post('/getNotesByDriverMedicalCardDocument', [DriverMedicalCardDocumentsController::class, 'getNotesByDriverMedicalCardDocument']);
Route::post('/saveDriverMedicalCardDocumentNote', [DriverMedicalCardDocumentsController::class, 'saveDriverMedicalCardDocumentNote']);
Route::post('/deleteDriverMedicalCardDocumentNote', [DriverMedicalCardDocumentsController::class, 'deleteDriverMedicalCardDocumentNote']);

Route::post('/getDocumentsByDriverTractor', [DriverTractorDocumentsController::class, 'getDocumentsByDriverTractor']);
Route::post('/saveDriverTractorDocument', [DriverTractorDocumentsController::class, 'saveDriverTractorDocument']);
Route::post('/deleteDriverTractorDocument', [DriverTractorDocumentsController::class, 'deleteDriverTractorDocument']);
Route::post('/getNotesByDriverTractorDocument', [DriverTractorDocumentsController::class, 'getNotesByDriverTractorDocument']);
Route::post('/saveDriverTractorDocumentNote', [DriverTractorDocumentsController::class, 'saveDriverTractorDocumentNote']);
Route::post('/deleteDriverTractorDocumentNote', [DriverTractorDocumentsController::class, 'deleteDriverTractorDocumentNote']);

Route::post('/getDocumentsByDriverTrailer', [DriverTrailerDocumentsController::class, 'getDocumentsByDriverTrailer']);
Route::post('/saveDriverTrailerDocument', [DriverTrailerDocumentsController::class, 'saveDriverTrailerDocument']);
Route::post('/deleteDriverTrailerDocument', [DriverTrailerDocumentsController::class, 'deleteDriverTrailerDocument']);
Route::post('/getNotesByDriverTrailerDocument', [DriverTrailerDocumentsController::class, 'getNotesByDriverTrailerDocument']);
Route::post('/saveDriverTrailerDocumentNote', [DriverTrailerDocumentsController::class, 'saveDriverTrailerDocumentNote']);
Route::post('/deleteDriverTrailerDocumentNote', [DriverTrailerDocumentsController::class, 'deleteDriverTrailerDocumentNote']);

Route::post('/getOperatorById', [CompanyOperatorsController::class, 'getOperatorById']);
Route::post('/getOperators', [CompanyOperatorsController::class, 'getOperators']);
Route::post('/getOperatorByCode', [CompanyOperatorsController::class, 'getOperatorByCode']);
Route::post('/saveOperator', [CompanyOperatorsController::class, 'saveOperator']);
Route::post('/deleteOperator', [CompanyOperatorsController::class, 'deleteOperator']);
Route::post('/uploadOperatorAvatar', [CompanyOperatorsController::class, 'uploadAvatar']);
Route::post('/removeOperatorAvatar', [CompanyOperatorsController::class, 'removeAvatar']);
Route::post('/companyOperatorsSearch', [CompanyOperatorsController::class, 'companyOperatorsSearch']);
Route::post('/saveCompanyOperatorMailingAddress', [CompanyOperatorsController::class, 'saveCompanyOperatorMailingAddress']);
Route::post('/deleteCompanyOperatorMailingAddress', [CompanyOperatorsController::class, 'deleteCompanyOperatorMailingAddress']);
Route::post('/getCompanyOperatorEmergencyContact', [CompanyOperatorsController::class, 'getCompanyOperatorEmergencyContact']);
Route::post('/saveCompanyOperatorEmergencyContact', [CompanyOperatorsController::class, 'saveCompanyOperatorEmergencyContact']);
Route::post('/deleteCompanyOperatorEmergencyContact', [CompanyOperatorsController::class, 'deleteCompanyOperatorEmergencyContact']);
Route::post('/uploadCompanyOperatorEmergencyContactAvatar', [CompanyOperatorsController::class, 'uploadCompanyOperatorEmergencyContactAvatar']);
Route::post('/removeCompanyOperatorEmergencyContactAvatar', [CompanyOperatorsController::class, 'removeCompanyOperatorEmergencyContactAvatar']);
Route::post('/getCompanyOperatorLicense', [CompanyOperatorsController::class, 'getCompanyOperatorLicense']);
Route::post('/saveCompanyOperatorLicense', [CompanyOperatorsController::class, 'saveCompanyOperatorLicense']);
Route::post('/deleteCompanyOperatorLicense', [CompanyOperatorsController::class, 'deleteCompanyOperatorLicense']);
Route::post('/getLicenseEndorsements', [CompanyOperatorsController::class, 'getLicenseEndorsements']);
Route::post('/getLicenseClasses', [CompanyOperatorsController::class, 'getLicenseClasses']);
Route::post('/getLicenseRestrictions', [CompanyOperatorsController::class, 'getLicenseRestrictions']);
Route::post('/uploadOperatorLicenseImage', [CompanyOperatorsController::class, 'uploadOperatorLicenseImage']);
Route::post('/removeOperatorLicenseImage', [CompanyOperatorsController::class, 'removeOperatorLicenseImage']);
Route::post('/getCompanyOperatorMedicalCard', [CompanyOperatorsController::class, 'getCompanyOperatorMedicalCard']);
Route::post('/saveCompanyOperatorMedicalCard', [CompanyOperatorsController::class, 'saveCompanyOperatorMedicalCard']);
Route::post('/deleteCompanyOperatorMedicalCard', [CompanyOperatorsController::class, 'deleteCompanyOperatorMedicalCard']);
Route::post('/uploadOperatorMedicalCardImage', [CompanyOperatorsController::class, 'uploadOperatorMedicalCardImage']);
Route::post('/removeOperatorMedicalCardImage', [CompanyOperatorsController::class, 'removeOperatorMedicalCardImage']);
Route::post('/getCompanyOperatorTractor', [CompanyOperatorsController::class, 'getCompanyOperatorTractor']);
Route::post('/saveCompanyOperatorTractor', [CompanyOperatorsController::class, 'saveCompanyOperatorTractor']);
Route::post('/deleteCompanyOperatorTractor', [CompanyOperatorsController::class, 'deleteCompanyOperatorTractor']);
Route::post('/getCompanyOperatorTrailer', [CompanyOperatorsController::class, 'getCompanyOperatorTrailer']);
Route::post('/saveCompanyOperatorTrailer', [CompanyOperatorsController::class, 'saveCompanyOperatorTrailer']);
Route::post('/deleteCompanyOperatorTrailer', [CompanyOperatorsController::class, 'deleteCompanyOperatorTrailer']);

Route::post('/getDocumentsByCompanyOperatorLicense', [CompanyOperatorLicenseDocumentsController::class, 'getDocumentsByCompanyOperatorLicense']);
Route::post('/saveCompanyOperatorLicenseDocument', [CompanyOperatorLicenseDocumentsController::class, 'saveCompanyOperatorLicenseDocument']);
Route::post('/deleteCompanyOperatorLicenseDocument', [CompanyOperatorLicenseDocumentsController::class, 'deleteCompanyOperatorLicenseDocument']);
Route::post('/getNotesByCompanyOperatorLicenseDocument', [CompanyOperatorLicenseDocumentsController::class, 'getNotesByCompanyOperatorLicenseDocument']);
Route::post('/saveCompanyOperatorLicenseDocumentNote', [CompanyOperatorLicenseDocumentsController::class, 'saveCompanyOperatorLicenseDocumentNote']);
Route::post('/deleteCompanyOperatorLicenseDocumentNote', [CompanyOperatorLicenseDocumentsController::class, 'deleteCompanyOperatorLicenseDocumentNote']);

Route::post('/getDocumentsByCompanyOperatorMedicalCard', [CompanyOperatorMedicalCardDocumentsController::class, 'getDocumentsByCompanyOperatorMedicalCard']);
Route::post('/saveCompanyOperatorMedicalCardDocument', [CompanyOperatorMedicalCardDocumentsController::class, 'saveCompanyOperatorMedicalCardDocument']);
Route::post('/deleteCompanyOperatorMedicalCardDocument', [CompanyOperatorMedicalCardDocumentsController::class, 'deleteCompanyOperatorMedicalCardDocument']);
Route::post('/getNotesByCompanyOperatorMedicalCardDocument', [CompanyOperatorMedicalCardDocumentsController::class, 'getNotesByCompanyOperatorMedicalCardDocument']);
Route::post('/saveCompanyOperatorMedicalCardDocumentNote', [CompanyOperatorMedicalCardDocumentsController::class, 'saveCompanyOperatorMedicalCardDocumentNote']);
Route::post('/deleteCompanyOperatorMedicalCardDocumentNote', [CompanyOperatorMedicalCardDocumentsController::class, 'deleteCompanyOperatorMedicalCardDocumentNote']);

Route::post('/getDocumentsByCompanyOperatorTractor', [CompanyOperatorTractorDocumentsController::class, 'getDocumentsByCompanyOperatorTractor']);
Route::post('/saveCompanyOperatorTractorDocument', [CompanyOperatorTractorDocumentsController::class, 'saveCompanyOperatorTractorDocument']);
Route::post('/deleteCompanyOperatorTractorDocument', [CompanyOperatorTractorDocumentsController::class, 'deleteCompanyOperatorTractorDocument']);
Route::post('/getNotesByCompanyOperatorTractorDocument', [CompanyOperatorTractorDocumentsController::class, 'getNotesByCompanyOperatorTractorDocument']);
Route::post('/saveCompanyOperatorTractorDocumentNote', [CompanyOperatorTractorDocumentsController::class, 'saveCompanyOperatorTractorDocumentNote']);
Route::post('/deleteCompanyOperatorTractorDocumentNote', [CompanyOperatorTractorDocumentsController::class, 'deleteCompanyOperatorTractorDocumentNote']);

Route::post('/getDocumentsByCompanyOperatorTrailer', [CompanyOperatorTrailerDocumentsController::class, 'getDocumentsByCompanyOperatorTrailer']);
Route::post('/saveCompanyOperatorTrailerDocument', [CompanyOperatorTrailerDocumentsController::class, 'saveCompanyOperatorTrailerDocument']);
Route::post('/deleteCompanyOperatorTrailerDocument', [CompanyOperatorTrailerDocumentsController::class, 'deleteCompanyOperatorTrailerDocument']);
Route::post('/getNotesByCompanyOperatorTrailerDocument', [CompanyOperatorTrailerDocumentsController::class, 'getNotesByCompanyOperatorTrailerDocument']);
Route::post('/saveCompanyOperatorTrailerDocumentNote', [CompanyOperatorTrailerDocumentsController::class, 'saveCompanyOperatorTrailerDocumentNote']);
Route::post('/deleteCompanyOperatorTrailerDocumentNote', [CompanyOperatorTrailerDocumentsController::class, 'deleteCompanyOperatorTrailerDocumentNote']);


//Route::post('/saveOperator', [OwnerOperatorsController::class, 'saveOperator']);
//Route::post('/deleteOperator', [OwnerOperatorsController::class, 'deleteOperator']);
//Route::post('/uploadOperatorAvatar', [OwnerOperatorsController::class, 'uploadAvatar']);
//Route::post('/removeOperatorAvatar', [OwnerOperatorsController::class, 'removeAvatar']);
//Route::post('/companyOperatorsSearch', [OwnerOperatorsController::class, 'companyOperatorsSearch']);

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
Route::post('/sendCarrierCheckCallsEmail', [EmailsController::class, 'sendCarrierCheckCallsEmail']);
Route::post('/sendOrderEmail', [EmailsController::class, 'sendOrderEmail']);
Route::post('/sendBolEmail', [EmailsController::class, 'sendBolEmail']);
Route::post('/sendPasswordRecoveryEmail', [EmailsController::class, 'sendPasswordRecoveryEmail']);
Route::post('/validateRecoveryData', [EmailsController::class, 'validateRecoveryData']);
Route::post('/changePassword', [EmailsController::class, 'changePassword']);

Route::post('/getAgentDriverByCode', [AgentDriversController::class, 'getAgentDriverByCode']);
Route::post('/getDriversByAgentId', [AgentDriversController::class, 'getDriversByAgentId']);
Route::post('/saveAgentDriver', [AgentDriversController::class, 'saveAgentDriver']);
Route::post('/deleteAgentDriver', [AgentDriversController::class, 'deleteAgentDriver']);

Route::post('/getSaimeConfig', [SaimeController::class, 'getSaimeConfig']);
Route::post('/saveSaimeConfig', [SaimeController::class, 'saveSaimeConfig']);


Route::post('/testPdf', [EmailsController::class, 'testPdf']);
Route::post('/testView', [EmailsController::class, 'testView']);

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

// Show PHP info
Route::get('/phpinfo', function () {
    phpinfo();
});
