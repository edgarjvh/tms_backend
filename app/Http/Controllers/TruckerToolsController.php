<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderComment;
use App\Models\OrderDocument;
use App\Models\OrderEvent;
use App\Models\OrderLocation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\StatusUpdateTTMailable;
use App\Models\Order;

class TruckerToolsController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function createLoadOnTT(Request $request): JsonResponse
    {
        $order_number = $request->order_number ?? '';
        $accountId = '87t1Bhx9NiBeJ1wO21l7jQ==';
        $partnerId = 143;

        $ORDER = Order::query();

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

            if (($order->tt_load_id ?? 0) === 0) { // if load id is not set
                $responsePost = \Illuminate\Support\Facades\Http::post('https://loadtracking.truckertools.com/loadtrackservice/LTL', [
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
                ]);

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

                return response()->json(['result' => 'OK', 'response' => $responsejson['response'], 'db' => $db]);
            }

            return response()->json(['result' => 'ALREADY_EXISTS']);
        }

        return response()->json(['result' => 'NO_DRIVER_PHONE']);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ttLocationUpdate(Request $request): JsonResponse
    {
        DB::table('api_logs')->insert([
            'endpoint' => 'ttLocationUpdate',
            'payload' => json_encode($request->all()),
        ]);

        $accountId = $request->accountId ?? null;
        $partnerId = $request->partnerId ?? null;
        $loadTrackId = $request->loadTrackId ?? null;
        $loadTrackExternalId = $request->loadTrackExternalId ?? null;
        $eventType = $request->eventType ?? null;
        $latestLocation = $request->latestLocation ?? null;
        $lat = $latestLocation['lat'] ?? null;
        $lon = $latestLocation['lon'] ?? null;
        $timestampUTC = $latestLocation['timestampUTC'] ?? null;

        $account_id = '87t1Bhx9NiBeJ1wO21l7jQ==';
        $partner_id = 143;

        $location = null;

        if ($accountId === $account_id && $partnerId === $partner_id && $eventType === 'LocationUpdate') {
            $ORDER_LOCATION = OrderLocation::query();

            $date = \DateTime::createFromFormat('m/d/Y H:i:s T', $timestampUTC);
            $serverTimezone = date_default_timezone_get();
            $date->setTimezone(new \DateTimeZone($serverTimezone));
            $datetime = $date->format('Y-m-d H:i:s');

            $location = $ORDER_LOCATION->updateOrCreate(
                [
                    'id' => 0
                ],
                [
                    'order_id' => $loadTrackExternalId,
                    'tt_load_id' => $loadTrackId,
                    'lat' => $lat,
                    'lon' => $lon,
                    'date_time' => $datetime,
                ]
            );
        }

        return response()->json(['result' => 'OK', 'location' => $location]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ttCommentUpdate(Request $request): JsonResponse
    {
        DB::table('api_logs')->insert([
            'endpoint' => 'ttCommentUpdate',
            'payload' => json_encode($request->all()),
        ]);

        $accountId = $request->accountId ?? null;
        $partnerId = $request->partnerId ?? null;
        $loadTrackId = $request->loadTrackId ?? null;
        $loadTrackExternalId = $request->loadTrackExternalId ?? null;
        $eventType = $request->eventType ?? null;
        $comments = $request->comments ?? null;
        $comment = $comments['comment'] ?? null;
        $commentBy = $comments['commentBy'] ?? null;
        $timestampUTC = $comments['timestampUTC'] ?? null;
        $location = $comments['location'] ?? null;
        $lat = $location['lat'] ?? null;
        $lon = $location['lon'] ?? null;
        $locationTimestampUTC = $location['timestampUTC'] ?? null;

        $account_id = '87t1Bhx9NiBeJ1wO21l7jQ==';
        $partner_id = '143';

        if ($accountId === $account_id && $partnerId === $partner_id && $eventType === 'CommentUpdate') {
            $ORDER_COMMENT = OrderComment::query();

            $date = \DateTime::createFromFormat('m/d/Y H:i:s T', $timestampUTC);
            $serverTimezone = date_default_timezone_get();
            $date->setTimezone(new \DateTimeZone($serverTimezone));
            $datetime = $date->format('Y-m-d H:i:s');

            $ORDER_COMMENT->updateOrCreate(
                [
                    'id' => 0
                ],
                [
                    'order_id' => $loadTrackExternalId,
                    'tt_load_id' => $loadTrackId,
                    'comment' => $comment,
                    'comment_by' => $commentBy,
                    'date_time' => $datetime,
                    'sender' => 'driver'
                ]
            );

            $ORDER_LOCATION = OrderLocation::query();

            $locationDate = \DateTime::createFromFormat('m/d/Y H:i:s T', $locationTimestampUTC);
            $serverTimezone = date_default_timezone_get();
            $locationDate->setTimezone(new \DateTimeZone($serverTimezone));
            $locationDatetime = $locationDate->format('Y-m-d H:i:s');

            $ORDER_LOCATION->updateOrCreate(
                [
                    'id' => 0
                ],
                [
                    'order_id' => $loadTrackExternalId,
                    'tt_load_id' => $loadTrackId,
                    'lat' => $lat,
                    'lon' => $lon,
                    'date_time' => $locationDatetime,
                ]
            );
        }

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ttDocumentUpdate(Request $request): JsonResponse
    {
        DB::table('api_logs')->insert([
            'endpoint' => 'ttDocumentUpdate',
            'payload' => json_encode($request->all()),
        ]);

        $accountId = $request->accountId ?? null;
        $partnerId = $request->partnerId ?? null;
        $loadTrackId = $request->loadTrackId ?? null;
        $loadTrackExternalId = $request->loadTrackExternalId ?? null;
        $eventType = $request->eventType ?? null;
        $document = $request->document ?? null; // this is an object
        $type = $document['type'] ?? null;
        $timestamp = $document['timestamp'] ?? null;
        $url = $document['url'] ?? null;

        if (
            strtolower($type) === 'proof of delivery' ||
            strtolower($type) === 'bill of lading' ||
            strtolower($type) === 'photograph' ||
            strtolower($type) === 'seal'
        ) {
            $tags = match (strtolower($type)) {
                'proof of delivery' => 'POD',
                'bill of lading' => 'BOL',
                'photograph' => 'Photo',
                'seal' => 'Seal',
                default => '',
            };

            $account_id = '87t1Bhx9NiBeJ1wO21l7jQ==';
            $partner_id = '143';

            $ORDER_DOCUMENT = new OrderDocument();

            if ($accountId === $account_id && $partnerId === $partner_id && $eventType === 'DocumentUpdate') {
                $date = \DateTime::createFromFormat('m/d/Y H:i:s T', $timestamp);
                $serverTimezone = date_default_timezone_get();
                $date->setTimezone(new \DateTimeZone($serverTimezone));
                $datetime = $date->format('Y-m-d');

                $doc_name = basename($url);
                $doc_extension = pathinfo($doc_name, PATHINFO_EXTENSION);
                $doc_id = uniqid() . '.' . $doc_extension;

                $ORDER_DOCUMENT->updateOrCreate([
                    'id' => 0
                ], [
                    'order_id' => $loadTrackExternalId,
                    'doc_id' => $doc_id,
                    'doc_name' => $doc_name,
                    'doc_extension' => $doc_extension,
                    'date_entered' => $datetime,
                    'title' => $type,
                    'subject' => $type,
                    'tags' => $tags,
                    'origin' => 'tt',
                    'external_url' => $url
                ]);
            }
        }

        return response()->json(['result' => 'OK']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function ttStatusUpdate(Request $request): JsonResponse
    {
        DB::table('api_logs')->insert([
            'endpoint' => 'ttStatusUpdate',
            'payload' => json_encode($request->all()),
        ]);

        $accountId = $request->accountId ?? null;
        $partnerId = $request->partnerId ?? null;
        $loadTrackId = $request->loadTrackId ?? null;
        $loadNumber = $request->loadNumber ?? null;
        $loadTrackExternalId = $request->loadTrackExternalId ?? null;
        $eventType = $request->eventType ?? null;
        $latestStatus = $request->latestStatus ?? null; // this is an object
        $code = $latestStatus['code'] ?? null;
        $account_id = '87t1Bhx9NiBeJ1wO21l7jQ==';
        $partner_id = 143;

        $codes = [
            "ASTF",
            "CA",
            "CL",
            "DDAGP",
            "DE",
            "DEC",
            "DHA",
            "DHTDA",
            "DIA",
            "DNTS",
            "DOFUD",
            "DTAL",
            "DUIA",
            "DX",
            "DXC",
            "EAPP",
            "ENSTS",
            "ESET",
            "ESETIA",
            "ESETLNF",
            "ESETNA",
            "ESETNR",
            "ESETOOD",
            "LRBD",
            "LRBS",
            "NAGT",
            "NALT",
            "NAM",
            "NIGT",
            "NILT",
            "NINF",
            "PE",
            "PEC",
            "PX",
            "PXC",
            "RA",
            "RS",
            "SADUE",
            "SD",
            "SDP",
            "SE",
            "SEC",
            "SI",
            "SS",
            "ST",
            "SX",
            "SXC",
            "TILTA",
            "TILTI",
            "TILTNS",
            "TILTO",
            "TILTTS",
            "TLT",
            "TWSA",
            "TWSS"
        ];

        if (
            $accountId === $account_id &&
            $partnerId === $partner_id &&
            $eventType === 'StatusUpdate' &&
            in_array($code, $codes)
        ) {
            $ORDER_EVENT = OrderEvent::query();

            $date = \DateTime::createFromFormat('m/d/Y H:i:s T', $latestStatus['timestampUTC']);
            $serverTimezone = date_default_timezone_get();
            $date->setTimezone(new \DateTimeZone($serverTimezone));
            $event_date = $date->format('m/d/Y');
            $event_time = $date->format('Hi');
            $currentDate = date('m/d/Y');
            $currentTime = date('Hi');

            $ORDER_EVENT->updateOrCreate(
                [
                    'id' => 0
                ],
                [
                    'order_id' => $loadTrackExternalId,
                    'event_notes' => $latestStatus['name'],
                    'event_date' => $event_date,
                    'event_time' => $event_time,
                    'date' => $currentDate,
                    'time' => $currentTime,
                    'origin' => 'tt',
                    'lat' => $latestStatus['location']['lat'] ?? null,
                    'lon' => $latestStatus['location']['lon'] ?? null,
                    'tt_status_code' => $latestStatus['code'] ?? null,
                ]
            );
        }

        if ($code === 'TWSA' || $code === 'TWSS') {
            // Send email notification
            Mail::send(new StatusUpdateTTMailable($loadNumber, $latestStatus['name']));
        }

        return response()->json(['result' => 'OK']);
    }

    public function testUpdateStatus(Request $request): JsonResponse
    {
        // Simulate a status update
        $orderNumber = $request->input('orderNumber');
        $status = $request->input('status');

        // Send the email
        Mail::send(new StatusUpdateTTMailable($orderNumber, $status));

        return response()->json(['result' => 'OK']);
    }
}
