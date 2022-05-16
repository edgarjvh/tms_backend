<?php

use App\Models\Contact;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderCarrierRating;
use App\Models\OrderCustomerRating;
use App\Models\OrderInvoiceInternalNote;
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

Route::get('/gettingRevenue', function () {
    $date_start = '08/15/2021';
    $date_end = '08/31/2021';
    $customer_id = 126;

    $query1 = Customer::query();
    $query1
        ->with('orders', function ($query2) use ($date_start, $date_end) {
            return $query2
                ->without([
                    'bill_to_company',
                    'carrier',
                    'driver',
                    'notes_for_carrier',
                    'internal_notes',
                    'pickups',
                    'deliveries',
                    'routing',
                    'documents',
                    'events',
                    'division',
                    'load_type',
                    'template'
                ])
                ->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')")
                ->get();
        })
        ->where('id', $customer_id);

    $data = $query1->get();
    return view('welcome', compact('data'));
});

Route::get('/gettingOrders', function () {
    $order_number = 5;

    $query1 = Order::query();
    $query1
        ->where('order_number', $order_number)
        ->with([
            'bill_to_company' => function ($query2) {
                return $query2
                    ->without(['documents', 'directions', 'hours', 'automatic_emails', 'notes', 'zip_data', 'mailing_address'])
                    ->with(['contacts' => function ($query3) {
                        return $query3->where('is_primary', 1);
                    }]);
            },
            'carrier' => function ($query2) {
                return $query2
                    ->without(['drivers', 'notes', 'factoring_company', 'mailing_address', 'documents', 'equipments_information'])
                    ->with(['contacts' => function ($query3) {
                        return $query3->where('is_primary', 1);
                    }]);
            },
            'driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups' => function ($query2) {
                return $query2
                    ->with(['customer' => function ($query3) {
                        return $query3
                            ->without(['documents', 'directions', 'hours', 'automatic_emails', 'notes', 'mailing_address'])
                            ->with(['contacts' => function ($query4) {
                                return $query4->where('is_primary', 1);
                            }]);
                    }]);

            },
            'deliveries' => function ($query2) {
                return $query2
                    ->with(['customer' => function ($query3) {
                        return $query3
                            ->without(['documents', 'directions', 'hours', 'automatic_emails', 'notes', 'mailing_address'])
                            ->with(['contacts' => function ($query4) {
                                return $query4->where('is_primary', 1);
                            }]);
                    }]);

            },
            'routing',
            'documents',
            'events' => function ($query2) {
                return $query2
                    ->with([
                        'shipper' => function ($query3) {
                            return $query3->without(['contacts', 'documents', 'directions', 'hours', 'automatic_emails', 'notes', 'zip_data', 'mailing_address']);
                        },
                        'consignee' => function ($query3) {
                            return $query3->without(['contacts', 'documents', 'directions', 'hours', 'automatic_emails', 'notes', 'zip_data', 'mailing_address']);
                        },
                        'arrived_customer' => function ($query3) {
                            return $query3->without(['contacts', 'documents', 'directions', 'hours', 'automatic_emails', 'notes', 'zip_data', 'mailing_address']);
                        },
                        'departed_customer' => function ($query3) {
                            return $query3->without(['contacts', 'documents', 'directions', 'hours', 'automatic_emails', 'notes', 'zip_data', 'mailing_address']);
                        },
                        'new_carrier' => function ($query3) {
                            return $query3->without(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address', 'documents', 'equipments_information']);
                        },
                        'old_carrier' => function ($query3) {
                            return $query3->without(['contacts', 'drivers', 'notes', 'insurances', 'factoring_company', 'mailing_address', 'documents', 'equipments_information']);
                        }
                    ]);
            },
            'division',
            'load_type',
            'template'
        ]);

    $data = $query1->get();
    return view('welcome', compact('data'));
});

Route::get('/gettingOrderInvoiceNotes', function () {
    $order_id = 24;

    $query1 = OrderInvoiceInternalNote::query();
    $query1->where('order_id', $order_id);
    $data = $query1->get();
    return view('welcome', compact('data'));
});

Route::get('/phpinfo', function () {
    return view('welcome');
});

Route::post('/customers', [CustomersController::class, 'customers'])->name('customers');
Route::post('/getCustomerById', [CustomersController::class, 'getCustomerById']);
Route::post('/customerSearch', [CustomersController::class, 'customerSearch']);
Route::post('/getFullCustomers', [CustomersController::class, 'getFullCustomers'])->name('customers');
Route::post('/saveCustomer', [CustomersController::class, 'SaveCustomer']);
Route::post('/submitCustomerImport', [CustomersController::class, 'submitCustomerImport']);
Route::post('/submitCustomerImport2', [CustomersController::class, 'submitCustomerImport2']);
Route::post('/getCustomerPayload', [CustomersController::class, 'getCustomerPayload']);
Route::post('/getCustomerOrders', [CustomersController::class, 'getCustomerOrders']);

Route::post('/getAutomaticEmails', [AutomaticEmailsController::class, 'getAutomaticEmails']);
Route::post('/saveAutomaticEmails', [AutomaticEmailsController::class, 'saveAutomaticEmails']);
Route::post('/removeAutomaticEmail', [AutomaticEmailsController::class, 'removeAutomaticEmail']);

Route::post('/getCarrierById', [CarriersController::class, 'getCarrierById']);
Route::post('/carriers', [CarriersController::class, 'carriers'])->name('carriers');
Route::post('/carrierSearch', [CarriersController::class, 'carrierSearch']);
Route::post('/getFullCarriers', [CarriersController::class, 'getFullCarriers']);
Route::post('/saveCarrier', [CarriersController::class, 'saveCarrier']);
Route::post('/submitCarrierImport', [CarriersController::class, 'submitCarrierImport']);
Route::post('/getCarrierPayload', [CarriersController::class, 'getCarrierPayload']);
Route::post('/getCarrierPopupItems', [CarriersController::class, 'getCarrierPopupItems']);
Route::post('/saveCarrierEquipment', [CarrierEquipmentsController::class, 'saveCarrierEquipment']);

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

Route::post('/notes', [NotesController::class, 'notes'])->name('notes');
Route::post('/saveNote', [NotesController::class, 'saveNote']);
Route::post('/saveCustomerNote', [NotesController::class, 'saveCustomerNote']);

Route::post('/carrierNotes', [NotesController::class, 'carrierNotes'])->name('notes');
Route::post('/saveCarrierNote', [NotesController::class, 'saveCarrierNote']);

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

Route::post('/saveCustomerDocument', [CustomerDocumentsController::class, 'saveCustomerDocument']);
Route::post('/getDocumentsByCustomer', [CustomerDocumentsController::class, 'getDocumentsByCustomer']);
Route::post('/deleteCustomerDocument', [CustomerDocumentsController::class, 'deleteCustomerDocument']);
Route::post('/getNotesByCustomerDocument', [CustomerDocumentsController::class, 'getNotesByCustomerDocument']);
Route::post('/saveCustomerDocumentNote', [CustomerDocumentsController::class, 'saveCustomerDocumentNote']);

Route::post('/saveCarrierDocument', [CarrierDocumentsController::class, 'saveCarrierDocument']);
Route::post('/getDocumentsByCarrier', [CarrierDocumentsController::class, 'getDocumentsByCarrier']);
Route::post('/deleteCarrierDocument', [CarrierDocumentsController::class, 'deleteCarrierDocument']);
Route::post('/getNotesByCarrierDocument', [CarrierDocumentsController::class, 'getNotesByCarrierDocument']);
Route::post('/saveCarrierDocumentNote', [CarrierDocumentsController::class, 'saveCarrierDocumentNote']);

Route::post('/saveFactoringCompanyDocument', [FactoringCompanyDocumentsController::class, 'saveFactoringCompanyDocument']);
Route::post('/getDocumentsByFactoringCompany', [FactoringCompanyDocumentsController::class, 'getDocumentsByFactoringCompany']);
Route::post('/deleteFactoringCompanyDocument', [FactoringCompanyDocumentsController::class, 'deleteFactoringCompanyDocument']);
Route::post('/getNotesByFactoringCompanyDocument', [FactoringCompanyDocumentsController::class, 'getNotesByFactoringCompanyDocument']);
Route::post('/saveFactoringCompanyDocumentNote', [FactoringCompanyDocumentsController::class, 'saveFactoringCompanyDocumentNote']);

Route::post('/saveOrderDocument', [OrderDocumentsController::class, 'saveOrderDocument']);
Route::post('/getDocumentsByOrder', [OrderDocumentsController::class, 'getDocumentsByOrder']);
Route::post('/deleteOrderDocument', [OrderDocumentsController::class, 'deleteOrderDocument']);
Route::post('/getNotesByOrderDocument', [OrderDocumentsController::class, 'getNotesByOrderDocument']);
Route::post('/saveOrderDocumentNote', [OrderDocumentsController::class, 'saveOrderDocumentNote']);

Route::post('/saveOrderBillingDocument', [OrderDocumentsController::class, 'saveOrderBillingDocument']);
Route::post('/getOrderBillingDocumentsByOrder', [OrderDocumentsController::class, 'getOrderBillingDocumentsByOrder']);
Route::post('/deleteOrderBillingDocument', [OrderDocumentsController::class, 'deleteOrderBillingDocument']);
Route::post('/getNotesByOrderBillingDocument', [OrderDocumentsController::class, 'getNotesByOrderBillingDocument']);
Route::post('/saveOrderBillingDocumentNote', [OrderDocumentsController::class, 'saveOrderBillingDocumentNote']);

Route::post('/saveOrderInvoiceCarrierDocument', [OrderDocumentsController::class, 'saveOrderInvoiceCarrierDocument']);
Route::post('/getInvoiceCarrierDocumentsByOrder', [OrderDocumentsController::class, 'getInvoiceCarrierDocumentsByOrder']);
Route::post('/deleteOrderInvoiceCarrierDocument', [OrderDocumentsController::class, 'deleteOrderInvoiceCarrierDocument']);
Route::post('/getNotesByOrderInvoiceCarrierDocument', [OrderDocumentsController::class, 'getNotesByOrderInvoiceCarrierDocument']);
Route::post('/saveOrderInvoiceCarrierDocumentNote', [OrderDocumentsController::class, 'saveOrderInvoiceCarrierDocumentNote']);

Route::post('/getDispatchNotes', [DispatchNotesController::class, 'getDispatchNotes']);
Route::post('/getInternalNotes', [DispatchNotesController::class, 'getInternalNotes']);
Route::post('/saveInternalNotes', [DispatchNotesController::class, 'saveInternalNotes']);
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

Route::post('/saveCarrierMailingAddress', [CarrierMailingAddressesController::class, 'saveCarrierMailingAddress']);
Route::post('/deleteCarrierMailingAddress', [CarrierMailingAddressesController::class, 'deleteCarrierMailingAddress']);

Route::post('/getOrders', [OrdersController::class, 'getOrders']);
Route::post('/getOrderById', [OrdersController::class, 'getOrderById']);
Route::post('/getOrderByOrderNumber', [OrdersController::class, 'getOrderByOrderNumber']);
Route::post('/getOrderByTripNumber', [OrdersController::class, 'getOrderByTripNumber']);
Route::post('/getLastOrderNumber', [OrdersController::class, 'getLastOrderNumber']);
Route::post('/saveOrder', [OrdersController::class, 'saveOrder']);
Route::post('/saveOrderEvent', [OrdersController::class, 'saveOrderEvent']);
Route::post('/removeOrderPickup', [OrdersController::class, 'removeOrderPickup']);
Route::post('/removeOrderDelivery', [OrdersController::class, 'removeOrderDelivery']);
Route::post('/saveOrderPickup', [OrdersController::class, 'saveOrderPickup']);
Route::post('/saveOrderDelivery', [OrdersController::class, 'saveOrderDelivery']);
Route::post('/saveOrderRouting', [OrdersController::class, 'saveOrderRouting']);
Route::post('/getOrdersRelatedData', [OrdersController::class, 'getOrdersRelatedData']);
Route::post('/submitOrderImport', [OrdersController::class, 'submitOrderImport']);

Route::post('/getDivisions', [DivisionsController::class, 'getDivisions']);
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

Route::post('/companies', [CompaniesController::class, 'companies']);
Route::post('/saveCompany', [CompaniesController::class, 'saveCompany']);
Route::post('/removeCompany', [CompaniesController::class, 'removeCompany']);
Route::post('/uploadCompanyLogo', [CompaniesController::class, 'uploadCompanyLogo']);
Route::post('/removeCompanyLogo', [CompaniesController::class, 'removeCompanyLogo']);

Route::post('/saveCompanyMailingAddress', [CompanyMailingAddressesController::class, 'saveCompanyMailingAddress']);
Route::post('/deleteCompanyMailingAddress', [CompanyMailingAddressesController::class, 'deleteCompanyMailingAddress']);

Route::post('/saveEmployee', [EmployeesController::class, 'saveEmployee']);
Route::post('/deleteEmployee', [EmployeesController::class, 'deleteEmployee']);
Route::post('/uploadEmployeeAvatar', [EmployeesController::class, 'uploadAvatar']);
Route::post('/removeEmployeeAvatar', [EmployeesController::class, 'removeAvatar']);
Route::post('/companyEmployeesSearch', [EmployeesController::class, 'companyEmployeesSearch']);
Route::post('/resetEmployeePassword', [EmployeesController::class, 'resetEmployeePassword']);

Route::post('/saveAgent', [AgentsController::class, 'saveAgent']);
Route::post('/deleteAgent', [AgentsController::class, 'deleteAgent']);
Route::post('/uploadAgentAvatar', [AgentsController::class, 'uploadAvatar']);
Route::post('/removeAgentAvatar', [AgentsController::class, 'removeAvatar']);
Route::post('/companyAgentsSearch', [AgentsController::class, 'companyAgentsSearch']);
Route::post('/resetAgentPassword', [AgentsController::class, 'resetAgentPassword']);

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


