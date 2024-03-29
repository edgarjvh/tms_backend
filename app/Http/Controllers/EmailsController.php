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
use App\Models\Company;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Pickup;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

        $carrier_primary_contact = collect($order->carrier->contacts)->first(function ($item) {
            return $item["is_primary"] === 1;
        });

        $customer_primary_contact = collect($order->bill_to_company->contacts)->first(function ($item) {
            return $item["is_primary"] === 1;
        });

        $order->customer_contact_first_name = $customer_primary_contact["first_name"];
        $order->carrier_contact_name = $carrier_primary_contact["first_name"] . ' ' . $carrier_primary_contact["last_name"];

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
                } else {
                    Mail::send(new CustomerConfMailable($order->customer_contact_first_name, $origin_city, $origin_state, $destination_city, $destination_state, $user_first_name, $user_last_name, $user_phone, $user_email_address, $order_number, $recipient_to, $recipient_cc, $recipient_bcc, $pdf));
                }
                return response()->json(['result' => 'SENT']);
            } catch (\Exception $e) {
                return response()->json(['result' => 'ERROR', 'message' => $e]);
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

        $order_number = '32057';

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

        return view('mails.rate-conf.rate_conf_template');
//        return view('mails.rate-conf.rate_conf_document', ['order' => $order]);
    }
}
