<?php

namespace App\Http\Controllers;

use App\Mail\CarrierArrivedShipperMailable;
use App\Mail\CarrierLoadedShipperMailable;
use App\Mail\CarrierArrivedConsigneeMailable;
use App\Mail\CarrierUnloadedConsigneeMailable;
use App\Mail\CustomerBolMailable;
use App\Mail\CustomerBookedLoadMailable;
use App\Mail\CustomerCheckCallMailable;
use App\Mail\CustomerConfMailable;
use App\Mail\CustomerOrderMailable;
use App\Mail\RateConfMailable;
use App\Mail\RecoverPasswordMailable;
use App\Models\AgentContact;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Order;
use App\Models\Pickup;
use App\Models\Recovery;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class EmailsController extends Controller
{
    public function sendRateConfEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $qrcode = $request->qrcode ?? '';
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $user_email_address = $request->user_email_address ?? '';
        $user_phone = $request->user_phone ?? '';
        $type = $request->type ?? 'carrier';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'pickups',
            'deliveries',
            'routing',
            'order_customer_ratings',
            'order_carrier_ratings',
            'user_code'
        ]);

        $order = $ORDER->first();

        $carrier_primary_contact = null;

        if ($order->carrier) {
            $carrier_primary_contact = collect($order->carrier->contacts)->first(function ($item) {
                return $item["is_primary"] === 1;
            });
        }

        $customer_primary_contact = collect($order->bill_to_company->contacts)->first(function ($item) {
            if (isset($item['pivot'])) {
                if ($item['pivot']['is_primary'] === 1) {
                    return true;
                }
            }

            return $item["is_primary"] === 1;
        });

        if ($type === 'carrier' && !$carrier_primary_contact) {
            return response()->json(['result' => 'NO CARRIER PRIMARY CONTACT']);
        }
        if ($type === 'customer' && !$customer_primary_contact) {
            return response()->json(['result' => 'NO CUSTOMER PRIMARY CONTACT']);
        }

        $order->customer_contact_first_name = $customer_primary_contact["first_name"];

        if ($carrier_primary_contact) {
            $order->carrier_contact_name = $carrier_primary_contact["first_name"] . ' ' . $carrier_primary_contact["last_name"];
        }

        $order->carrier_contact_email = ($carrier_primary_contact->primary_email ?? 'work') === 'work'
            ? ($carrier_primary_contact["email_work"] ?? '')
            : (($carrier_primary_contact["primary_email"] ?? 'work') === 'personal'
                ? ($carrier_primary_contact["email_personal"] ?? '')
                : (($carrier_primary_contact["primary_email"] ?? 'work') === 'other'
                    ? ($carrier_primary_contact["email_other"] ?? '')
                    : ''));

        $origin_city = "";
        $origin_state = "";
        $destination_city = "";
        $destination_state = "";

        if (count($order->routing) > 0) {
            $origin = $order->routing[0];
            if ($origin->type === 'pickup') {
                $pickup = $order->pickups->first(function ($item) use ($order, $origin) {
                    return $item->id === $origin->pickup_id;
                });

                $origin_city = $pickup->customer->city;
                $origin_state = $pickup->customer->state;
            } else {
                $delivery = $order->deliveries->first(function ($item) use ($order, $origin) {
                    return $item->id === $origin->delivery_id;
                });

                $origin_city = $delivery->customer->city;
                $origin_state = $delivery->customer->state;
            }
        }

        if (count($order->routing) > 1) {
            $destination = $order->routing[count($order->routing) - 1];

            if ($destination->type === 'pickup') {
                $pickup = $order->pickups->first(function ($item) use ($order, $destination) {
                    return $item->id === $destination->pickup_id;
                });

                $origin_city = $pickup->customer->city;
                $origin_state = $pickup->customer->state;
            } else {
                $delivery = $order->deliveries->first(function ($item) use ($order, $destination) {
                    return $item->id === $destination->delivery_id;
                });

                $destination_city = $delivery->customer->city;
                $destination_state = $delivery->customer->state;
            }
        }

        $company = $COMPANY->first();
        $order->company_name = $company->name;
        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;
        $order->user_phone = $user_phone;

        $order->qrcode = $qrcode;

        if ($type === 'carrier') {
            $pdf = Pdf::loadView('mails.rate-conf.rate_conf_document', ['order' => $order]);
        } else {
            $pdf = Pdf::loadView('mails.rate-conf.customer_rate_conf_document', ['order' => $order]);
        }

        //        return $pdf->setWarnings(false)->download('test.pdf');

        if (trim($order->carrier_contact_email) !== '') {
            try {
                if ($type === 'carrier') {
                    Mail::send(new RateConfMailable($order->carrier->name, $origin_city, $origin_state, $destination_city, $destination_state, $user_first_name, $user_last_name, $user_phone, $user_email_address, $order_number, $recipient_to, $recipient_cc, $recipient_bcc, $pdf));

                    $accountId = '87t1Bhx9NiBeJ1wO21l7jQ==';
                    $partnerId = 143;

                    $driverPhone = preg_replace('/[^0-9]/', '', $order->driver->contact_phone ?? '');

                    if (strlen($driverPhone) === 10) {
                        $stops = [];
                        $stopOrder = 0;

                        foreach ($order->routing as $route) {
                            if ($route->type === 'pickup') {
                                $pickup = $order->pickups->first(function ($item) use ($route) {
                                    return $item->id === $route->pickup_id;
                                });
                                if ($pickup) {
                                    $pu_time1 = str_pad(substr($pickup->pu_time1, 0, 2), 2, '0', STR_PAD_LEFT) . ':' . str_pad(substr($pickup->pu_time1, 2, 2), 2, '0', STR_PAD_LEFT);
                                    $lat = $pickup->customer->zip_data->latitude ?? null;
                                    $lon = $pickup->customer->zip_data->longitude ?? null;

                                    $stops[] = [
                                        'orderNumber' => $stopOrder++,
                                        'locationName' => $pickup->customer->name,
                                        'locationId' => $pickup->customer->code . (($pickup->customer->code_number > 0) ? $pickup->customer->code_number : ''),
                                        'address' => $pickup->customer->address1,
                                        'city' => $pickup->customer->city,
                                        'state' => $pickup->customer->state,
                                        'zipcode' => $pickup->customer->zip,
                                        'datetime' => $pickup->pu_date1 . ' ' . $pu_time1 . ':00 EST',
                                        'lat' => $lat,
                                        'lon' => $lon,
                                        'geofenceRadius' => 2
                                    ];
                                }
                            }

                            if ($route->type === 'delivery') {
                                $delivery = $order->deliveries->first(function ($item) use ($route) {
                                    return $item->id === $route->delivery_id;
                                });
                                if ($delivery) {
                                    $delivery_time1 = str_pad(substr($delivery->delivery_time1, 0, 2), 2, '0', STR_PAD_LEFT) . ':' . str_pad(substr($delivery->delivery_time1, 2, 2), 2, '0', STR_PAD_LEFT);
                                    $lat = $delivery->customer->zip_data->latitude ?? null;
                                    $lon = $delivery->customer->zip_data->longitude ?? null;

                                    $stops[] = [
                                        'orderNumber' => $stopOrder++,
                                        'locationName' => $delivery->customer->name,
                                        'locationId' => $delivery->customer->code . (($delivery->customer->code_number > 0) ? $delivery->customer->code_number : ''),
                                        'address' => $delivery->customer->address1,
                                        'city' => $delivery->customer->city,
                                        'state' => $delivery->customer->state,
                                        'zipcode' => $delivery->customer->zip,
                                        'datetime' => $delivery->delivery_date1 . ' ' . $delivery_time1 . ':00 EST',
                                        'lat' => $lat,
                                        'lon' => $lon,
                                        'geofenceRadius' => 2
                                    ];
                                }
                            }
                        }

                        $tt_data = [
                            'accountId' => $accountId,
                            'partnerId' => $partnerId,
                            'loadNumber' => strval($order->order_number),
                            'loadTrackExternalId' => $order->id,
                            'loadType' => $order->equipment->name ?? '',
                            'trailerType' => $order->equipment->name ?? '',
                            'revenueType' => 'TMS',
                            'driverCell' => $driverPhone,
                            'trailerNumber' => $order->driver->trailer->number ?? '',
                            'truckNumber' => $order->driver->tractor->number ?? '',
                            'driverName' => $order->driver->name ?? '',
                            'driverType' => 'companyDriver',
                            'carrier' => [
                                'companyName' => $order->carrier->name,
                                'docketNumber' => $order->carrier->mc_number,
                                'contactName' => trim((collect($order->carrier->contacts)->firstWhere('id', $order->carrier_contact_id)['first_name'] ?? '')
                                    . ' ' . (collect($order->carrier->contacts)->firstWhere('id', $order->carrier_contact_id)['last_name'] ?? '')),
                                'contactPhone' => (function () use ($order) {
                                    $contact = collect($order->carrier->contacts)->firstWhere('id', $order->carrier_contact_id);
                                    $primaryPhone = $order->carrier_contact_primary_phone ?? 'work';
                                    $phoneFields = [
                                        'work' => 'phone_work',
                                        'fax' => 'phone_work_fax',
                                        'mobile' => 'phone_mobile',
                                        'direct' => 'phone_direct',
                                        'other' => 'phone_other',
                                    ];
                                    $field = $phoneFields[$primaryPhone] ?? 'phone_work';
                                    return preg_replace('/[^0-9]/', '', $contact[$field] ?? '');
                                })(),
                                'contactPhoneExt' => '',
                            ],
                            'stops' => $stops
                        ];

                        $responsejson = '';

                        if (($order->tt_load_id ?? 0) === 0) { // if load id is not set
                            $responsePost = Http::post('https://loadtracking.truckertools.com/loadtrackservice/LTL', $tt_data);
                        }else{
                            $responsePost = Http::put('https://loadtracking.truckertools.com/loadtrackservice/LTL', $tt_data);
                        }

                        $responsejson = $responsePost->json();

                        $sql =
                            /** @lang text */
                            "UPDATE orders SET
                                    tt_load_id = ?,
                                    tt_tracking_method = ?,
                                    tt_map_link = ?,
                                    tt_status_page_link = ?,
                                    tt_details_link = ?,
                                    tt_details_link_no_auth = ?,
                                    tt_carrier_link = ?,
                                    tt_shipper_link = ?
                                WHERE id = ?";

                        $params = [
                            $responsejson['response']['loadId'] ?? null,
                            $responsejson['response']['trackingMethod'] ?? null,
                            $responsejson['response']['mapLink'] ?? null,
                            $responsejson['response']['statusPageLink'] ?? null,
                            $responsejson['response']['detailsLink'] ?? null,
                            $responsejson['response']['detailsLinkNoAuth'] ?? null,
                            $responsejson['response']['carrierLink'] ?? null,
                            $responsejson['response']['shipperLink'] ?? null,
                            $order->id
                        ];

                        $db = DB::update($sql, $params);

                        return response()->json(['result' => 'SENT', 'response' => $responsejson, 'db' => $db]);
                    }
                } else {
                    Mail::send(new CustomerConfMailable($order->customer_contact_first_name, $origin_city, $origin_state, $destination_city, $destination_state, $user_first_name, $user_last_name, $user_phone, $user_email_address, $order_number, $recipient_to, $recipient_cc, $recipient_bcc, $pdf));
                }
                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR', 'message' => $e->getMessage()]);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendBookedLoadEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'user_code'
        ]);

        $order = $ORDER->first();

        $origin_city = "";
        $origin_state = "";
        $destination_city = "";
        $destination_state = "";

        if (count($order->routing) > 0) {
            $origin = $order->routing[0];
            if ($origin->type === 'pickup') {
                $pickup = $order->pickups->first(function ($item) use ($order, $origin) {
                    return $item->id === $origin->pickup_id;
                });

                $origin_city = $pickup->customer->city;
                $origin_state = $pickup->customer->state;
            } else {
                $delivery = $order->deliveries->first(function ($item) use ($order, $origin) {
                    return $item->id === $origin->delivery_id;
                });

                $origin_city = $delivery->customer->city;
                $origin_state = $delivery->customer->state;
            }
        }

        if (count($order->routing) > 1) {
            $destination = $order->routing[count($order->routing) - 1];

            if ($destination->type === 'pickup') {
                $pickup = $order->pickups->first(function ($item) use ($order, $destination) {
                    return $item->id === $destination->pickup_id;
                });

                $origin_city = $pickup->customer->city;
                $origin_state = $pickup->customer->state;
            } else {
                $delivery = $order->deliveries->first(function ($item) use ($order, $destination) {
                    return $item->id === $destination->delivery_id;
                });

                $destination_city = $delivery->customer->city;
                $destination_state = $delivery->customer->state;
            }
        }

        $company = $COMPANY->first();
        $order->company_name = $company->name;
        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;

        if (count($recipient_to) > 0) {
            try {
                Mail::send(new CustomerBookedLoadMailable($order->carrier->name, $origin_city, $origin_state, $destination_city, $destination_state, $user_first_name, $user_last_name, $order_number, $recipient_to, $recipient_cc, $recipient_bcc));

                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR']);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendCarrierArrivedShipperEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $customer_id = $request->customer_id ?? null;
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $CUSTOMER = Customer::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'user_code'
        ]);

        $order = $ORDER->first();

        $shipper = $CUSTOMER->where('id', $customer_id)->first();

        $company = $COMPANY->first();
        $order->company_name = $company->name;
        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;

        if (count($recipient_to) > 0) {
            try {
                Mail::send(new  CarrierArrivedShipperMailable($order->carrier->name, $shipper->name, $shipper->city, $shipper->state, $user_first_name, $user_last_name, $order_number, $recipient_to, $recipient_cc, $recipient_bcc));

                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR']);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendCarrierArrivedConsigneeEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $customer_id = $request->customer_id ?? null;
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $CUSTOMER = Customer::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'user_code'
        ]);

        $order = $ORDER->first();

        $consignee = $CUSTOMER->where('id', $customer_id)->first();

        $company = $COMPANY->first();
        $order->company_name = $company->name;
        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;

        if (count($recipient_to) > 0) {
            try {
                Mail::send(new  CarrierArrivedConsigneeMailable($order->carrier->name, $consignee->name, $consignee->city, $consignee->state, $user_first_name, $user_last_name, $order_number, $recipient_to, $recipient_cc, $recipient_bcc));

                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR']);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendCarrierLoadedShipperEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $customer_id = $request->customer_id ?? null;
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $CUSTOMER = Customer::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'user_code'
        ]);

        $order = $ORDER->first();

        $shipper = $CUSTOMER->where('id', $customer_id)->first();

        $company = $COMPANY->first();
        $order->company_name = $company->name;
        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;

        if (count($recipient_to) > 0) {
            try {
                Mail::send(new  CarrierLoadedShipperMailable($order->carrier->name, $shipper->name, $shipper->city, $shipper->state, $user_first_name, $user_last_name, $order_number, $recipient_to, $recipient_cc, $recipient_bcc));

                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR']);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendCarrierCheckCallsEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $customer_id = $request->customer_id ?? null;
        $event_location = $request->event_location ?? null;
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $CUSTOMER = Customer::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'user_code'
        ]);

        $order = $ORDER->first();

        $consignee = $CUSTOMER->where('id', $customer_id)->first();

        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;

        if (count($recipient_to) > 0) {
            try {
                Mail::send(new CustomerCheckCallMailable($order_number, $consignee->city, $consignee->state, $order->carrier->name, $event_location, $user_first_name, $user_last_name, $recipient_to, $recipient_cc, $recipient_bcc));

                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR']);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendCarrierUnloadedConsigneeEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $customer_id = $request->customer_id ?? null;
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $CUSTOMER = Customer::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'user_code'
        ]);

        $order = $ORDER->first();

        $consignee = $CUSTOMER->where('id', $customer_id)->first();

        $company = $COMPANY->first();
        $order->company_name = $company->name;
        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;

        if (count($recipient_to) > 0) {
            try {
                Mail::send(new  CarrierUnloadedConsigneeMailable($order->carrier->name, $consignee->name, $consignee->city, $consignee->state, $user_first_name, $user_last_name, $order_number, $company->name, $recipient_to, $recipient_cc, $recipient_bcc));

                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR']);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendOrderEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $user_email_address = $request->user_email_address ?? '';
        $user_phone = $request->user_phone ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_carrier',
            'pickups',
            'deliveries',
            'routing',
            'events',
            'division',
            'load_type',
            'order_customer_ratings',
            'order_carrier_ratings',
            'user_code'
        ]);

        $order = $ORDER->first();

        $carrier_primary_contact = $order->carrier->contacts->first(function ($item) {
            return $item->is_primary === 1;
        });

        $customer_primary_contact = collect($order->bill_to_company->contacts)->first(function ($item) {
            return $item["is_primary"] === 1;
        });

        $order->customer_contact_first_name = $customer_primary_contact["first_name"];

        $order->carrier_contact_name = $carrier_primary_contact->first_name . ' ' . $carrier_primary_contact->last_name;

        $order->carrier_contact_email = ($carrier_primary_contact->primary_email ?? 'work') === 'work'
            ? ($carrier_primary_contact->email_work ?? '')
            : (($carrier_primary_contact->primary_email ?? 'work') === 'personal'
                ? ($carrier_primary_contact->email_personal ?? '')
                : (($carrier_primary_contact->primary_email ?? 'work') === 'other'
                    ? ($carrier_primary_contact->email_other ?? '')
                    : ''));

        $origin_city = "";
        $origin_state = "";
        $destination_city = "";
        $destination_state = "";

        if (count($order->routing) > 0) {
            $origin = $order->routing[0];
            if ($origin->type === 'pickup') {
                $pickup = $order->pickups->first(function ($item) use ($order, $origin) {
                    return $item->id === $origin->pickup_id;
                });

                $origin_city = $pickup->customer->city;
                $origin_state = $pickup->customer->state;
            } else {
                $delivery = $order->deliveries->first(function ($item) use ($order, $origin) {
                    return $item->id === $origin->delivery_id;
                });

                $origin_city = $delivery->customer->city;
                $origin_state = $delivery->customer->state;
            }
        }

        if (count($order->routing) > 1) {
            $destination = $order->routing[count($order->routing) - 1];

            if ($destination->type === 'pickup') {
                $pickup = $order->pickups->first(function ($item) use ($order, $destination) {
                    return $item->id === $destination->pickup_id;
                });

                $origin_city = $pickup->customer->city;
                $origin_state = $pickup->customer->state;
            } else {
                $delivery = $order->deliveries->first(function ($item) use ($order, $destination) {
                    return $item->id === $destination->delivery_id;
                });

                $destination_city = $delivery->customer->city;
                $destination_state = $delivery->customer->state;
            }
        }

        $company = $COMPANY->first();
        $order->company_name = $company->name;
        $order->user_first_name = $user_first_name;
        $order->user_last_name = $user_last_name;
        $order->user_phone = $user_phone;

        $pdf = Pdf::loadView('mails.rate-conf.customer_order_document', ['order' => $order]);

        //        return $pdf->setWarnings(false)->download('test.pdf');

        if (trim($order->carrier_contact_email) !== '') {
            try {
                Mail::send(new CustomerOrderMailable($order->customer_contact_first_name, $origin_city, $origin_state, $destination_city, $destination_state, $user_first_name, $user_last_name, $user_phone, $user_email_address, $order_number, $recipient_to, $recipient_cc, $recipient_bcc, $pdf));
                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR', 'message' => $e]);
            }
        } else {
            return response()->json(['result' => 'NO EMAIL ADDRESS']);
        }
    }

    public function sendBolEmail(Request $request)
    {
        $order_number = $request->order_number ?? '';
        $pickup_id = $request->pickup_id ?? null;
        $delivery_id = $request->delivery_id ?? null;
        $freight_charge_terms_prepaid = $request->freight_charge_terms_prepaid ?? false;
        $freight_charge_terms_collect = $request->freight_charge_terms_collect ?? false;
        $freight_charge_terms_3rd_party = $request->freight_charge_terms_3rd_party ?? false;
        $freight_charge_terms_master = $request->freight_charge_terms_master ?? false;
        $fee_terms_collect = $request->fee_terms_collect ?? false;
        $fee_terms_prepaid = $request->fee_terms_prepaid ?? false;
        $fee_terms_check = $request->fee_terms_check ?? false;
        $trailer_loaded_by_shipper = $request->trailer_loaded_by_shipper ?? false;
        $trailer_loaded_by_driver = $request->trailer_loaded_by_driver ?? false;
        $freight_counted_by_shipper = $request->freight_counted_by_shipper ?? false;
        $freight_counted_by_driver_pallets = $request->freight_counted_by_driver_pallets ?? false;
        $freight_counted_by_driver_pieces = $request->freight_counted_by_driver_pieces ?? false;
        $rating_items = $request->rating_items ?? [];
        $user_first_name = $request->user_first_name ?? '';
        $user_last_name = $request->user_last_name ?? '';
        $user_email_address = $request->user_email_address ?? '';
        $user_phone = $request->user_phone ?? '';
        $recipient_to = $request->recipient_to ?? [];
        $recipient_cc = $request->recipient_cc ?? [];
        $recipient_bcc = $request->recipient_bcc ?? [];

        $ORDER = Order::query();
        $COMPANY = Company::query();
        $PICKUP = Pickup::query();
        $DELIVERY = Delivery::query();
        $COMPANY = Company::query();

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'driver'
        ]);
        $order = $ORDER->first();

        $customer_primary_contact = collect($order->bill_to_company->contacts)->first(function ($item) {
            return $item["is_primary"] === 1;
        });

        $order->customer_contact_first_name = $customer_primary_contact["first_name"];

        $pickup = $PICKUP->where('id', $pickup_id)->with(['customer'])->first();
        $delivery = $DELIVERY->where('id', $delivery_id)->with(['customer'])->first();

        $order->pickup = $pickup;
        $order->delivery = $delivery;
        $order->freight_charge_terms_prepaid = $freight_charge_terms_prepaid;
        $order->freight_charge_terms_collect = $freight_charge_terms_collect;
        $order->freight_charge_terms_3rd_party = $freight_charge_terms_3rd_party;
        $order->freight_charge_terms_master = $freight_charge_terms_master;
        $order->fee_terms_collect = $fee_terms_collect;
        $order->fee_terms_prepaid = $fee_terms_prepaid;
        $order->fee_terms_check = $fee_terms_check;
        $order->trailer_loaded_by_shipper = $trailer_loaded_by_shipper;
        $order->trailer_loaded_by_driver = $trailer_loaded_by_driver;
        $order->freight_counted_by_shipper = $freight_counted_by_shipper;
        $order->freight_counted_by_driver_pallets = $freight_counted_by_driver_pallets;
        $order->freight_counted_by_driver_pieces = $freight_counted_by_driver_pieces;
        $order->rating_items = $rating_items;

        $company = $COMPANY->first();
        $order->company = $company;

        $origin_city = $pickup->customer->city ?? '';
        $origin_state = $pickup->customer->state ?? '';
        $destination_city = $delivery->customer->city ?? '';
        $destination_state = $delivery->customer->state ?? '';

        $pdf = Pdf::loadView('mails.rate-conf.customer_bol_document', ['order' => $order]);

        //        return response()->json(['order'=>$order]);
        //        return $pdf->setWarnings(false)->download('test.pdf');

        try {
            Mail::send(new CustomerBolMailable($order->customer_contact_first_name, $origin_city, $origin_state, $destination_city, $destination_state, $user_first_name, $user_last_name, $user_phone, $user_email_address, $order_number, $recipient_to, $recipient_cc, $recipient_bcc, $pdf));
            return response()->json(['result' => 'SENT']);
        } catch (\Exception $e) {
            return response()->json(['result' => 'ERROR', 'message' => $e]);
        }
    }

    public function sendPasswordRecoveryEmail(Request $request)
    {
        $email = $request->email ?? '';
        $recovery_data = '';

        $user = Employee::where('email_work', $email)->first();

        if ($user) {
            Recovery::where('email', $email)->where('type', 'employee')->delete(); // Remove any existing recovery requests for this email

            $recovery_obj = Recovery::create([
                'email' => $email,
                'type' => 'employee',
                'expiration_date' => now()->addMinutes(5)
            ]);

            $payload = [
                'id' => $recovery_obj->id,
                'email' => $email
            ];
            $json = json_encode($payload);
            $random_string = Str::random(10);
            $recovery_data = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
            $recovery_data_full = substr($recovery_data, 0, 10) . $random_string . substr($recovery_data, 10);

            if (trim($email) !== '') {
                try {
                    Mail::send(new RecoverPasswordMailable(env('APP_URL_FRONTEND') . '/?q=' . $recovery_data_full, $email));
                    return response()->json(['result' => 'SENT']);
                } catch (\Exception $e) {
                    return response()->json(['result' => 'ERROR', 'message' => $e->getMessage()], 400);
                }
            } else {
                return response()->json(['result' => 'NO EMAIL ADDRESS']);
            }

            return response()->json(['result' => 'employee', 'recovery_data' => $recovery_data, 'random_string' => $random_string, 'recovery_data_full' => $recovery_data_full]);
        } else {
            $user = AgentContact::where('email_work', $email)->whereNotNull('agent_id')->first();
            if ($user) {
                Recovery::where('email', $email)->where('type', 'agent')->delete(); // Remove any existing recovery requests for this email

                $recovery_obj = Recovery::create([
                    'email' => $email,
                    'type' => 'agent',
                    'expiration_date' => now()->addMinutes(5), // 5 minutes expiration
                ]);

                $payload = [
                    'id' => $recovery_obj->id,
                    'email' => $email
                ];
                $json = json_encode($payload);
                $random_string = Str::random(10);
                $recovery_data = rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
                $recovery_data_full = substr($recovery_data, 0, 10) . $random_string . substr($recovery_data, 10);

                if (trim($email) !== '') {
                    try {
                        Mail::send(new RecoverPasswordMailable(env('APP_URL_FRONTEND') . '/?q=' . $recovery_data_full, $email));
                        return response()->json(['result' => 'SENT']);
                    } catch (\Exception $e) {
                        Log::error('Error sending recovery email: ' . $e->getMessage());
                        return response()->json(['result' => 'ERROR', 'message' => $e->getMessage()], 400);
                    }
                } else {
                    return response()->json(['result' => 'NO EMAIL ADDRESS']);
                }

                return response()->json(['result' => 'agent', 'recovery_data' => $recovery_data, 'random_string' => $random_string, 'recovery_data_full' => $recovery_data_full]);
            } else {
                return response()->json(['result' => 'NO USER FOUND']);
            }
        }
    }

    public function validateRecoveryData(Request $request)
    {
        $id = $request->id ?? '';

        $exists = Recovery::where('id', $id)
            ->where('expiration_date', '>', now())
            ->exists();

        return response()->json(['result' => $exists ? 'OK' : 'Token expired']);
    }

    public function changePassword(Request $request)
    {
        $id = $request->id ?? '';
        $email = $request->email ?? '';
        $password = $request->password ?? '';

        $recovery = Recovery::where('id', $id)
            ->where('email', $email)
            ->where('expiration_date', '>', now())
            ->first();

        if ($recovery) {
            if ($recovery->type === 'employee') {
                $user = Employee::where('email_work', $email)->first();
            } else {
                $user = AgentContact::where('email_work', $email)->whereNotNull('agent_id')->first();
            }

            if ($user) {
                $user->password = Hash::make($password);
                $user->save();

                // Delete the recovery record after successful password change
                Recovery::where('id', $id)->delete();

                return response()->json(['result' => 'OK']);
            } else {
                return response()->json(['result' => 'User not found']);
            }
        } else {
            return response()->json(['result' => 'Token expired']);
        }
    }

    public function testPdf(Request $request)
    {
        $fileData = $_FILES['files'];

        for ($i = 0; $i < count($fileData['name']); $i++) {
            $doc_name = $fileData['name'][$i];
            $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
            $doc_id = uniqid() . '.' . $doc_extension;

            move_uploaded_file($fileData['tmp_name'][$i], public_path('test-documents/' . $doc_id));
        }

        return response()->json(['result' => 'OK']);
    }

    public function testView()
    {
        $ORDER = Order::query();
        $COMPANY = Company::query();

        $order_number = '32267';

        $ORDER->where('order_number', $order_number);
        $ORDER->with([
            'bill_to_company',
            'carrier',
            'equipment',
            'driver',
            'notes_for_driver',
            'notes_for_carrier',
            'internal_notes',
            'pickups',
            'deliveries',
            'routing',
            'documents',
            'events',
            'division',
            'load_type',
            'template',
            'order_customer_ratings',
            'order_carrier_ratings',
            'billing_documents',
            'billing_notes',
            'term',
            'user_code'
        ]);

        $order = $ORDER->first();

        $carrier_primary_contact = $order->carrier->contacts->first(function ($item) {
            return $item->is_primary === 1;
        });

        $order->carrier_contact_name = $carrier_primary_contact->first_name . ' ' . $carrier_primary_contact->last_name;

        $order->carrier_contact_email = ($carrier_primary_contact->primary_email ?? 'work') === 'work'
            ? ($carrier_primary_contact->email_work ?? '')
            : (($carrier_primary_contact->primary_email ?? 'work') === 'personal'
                ? ($carrier_primary_contact->email_personal ?? '')
                : (($carrier_primary_contact->primary_email ?? 'work') === 'other'
                    ? ($carrier_primary_contact->email_other ?? '')
                    : ''));

        $company = $COMPANY->first();
        $order->company_name = $company->name;

        // return view('mails.rate-conf.rate_conf_template');
        return Pdf::loadView('mails.rate-conf.rate_conf_document', ['order' => $order])->stream('rate_conf_document.pdf');
    }
}
