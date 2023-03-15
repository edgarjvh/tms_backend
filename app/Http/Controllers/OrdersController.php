<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\InternalNotes;
use App\Models\NotesForCarrier;
use App\Models\TemplateDelivery;
use App\Models\Equipment;
use App\Models\EventType;
use App\Models\LoadType;
use App\Models\Order;
use App\Models\OrderCarrierRating;
use App\Models\OrderCustomerRating;
use App\Models\OrderEvent;
use App\Models\Pickup;
use App\Models\TemplatePickup;
use App\Models\RateType;
use App\Models\Route;
use App\Models\TemplateRoute;
use DateTime;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrdersController extends Controller
{
    public function getOrders2(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $user_code = $request->user_code ?? '';

        $ORDER->where('is_imported', 0);
//        $ORDER->select([
//            'id',
//            'order_number',
//            'total_loaded_events',
//            'total_delivered_events',
//            'total_deliveries'
//        ]);
        // AVAILABLE ===========================
//        $ORDER->whereDoesntHave('carrier');

        // BOOKED
//        $ORDER->whereHas('carrier');
//        $ORDER->whereDoesntHave('events', function ($query1){
//           return $query1->whereHas('event_type', function($query2){
//              return $query2->where('name', 'loaded');
//           });
//        });

        // IN TRANSIT
        $ORDER->whereHas('carrier');
        $ORDER->totalDeliveries();
//        $ORDER->whereColumn('total_delivered_events', '<', 'total_deliveries');

        if ($user_code !== '') {
            $ORDER->whereHas('user_code', function ($query1) use ($user_code) {
                return $query1->where('code', $user_code);
            });
        }

        $ORDER->with([
            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'routing',
            'events',
            'user_code'
        ]);

        $ORDER->orderBy('order_number');

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    public function getOrders(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $user_code = $request->user_code ?? '';

        $ORDER->where('is_imported', 0);

        if ($user_code !== '') {
            $ORDER->whereHas('user_code', function ($query1) use ($user_code) {
                return $query1->where('code', $user_code);
            });
        }

        $ORDER->with([
            'bill_to_company',
            'carrier',
            'pickups',
            'deliveries',
            'routing',
            'events',
            'user_code'
        ]);

        $ORDER->orderBy('order_number');

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueCustomer(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $bill_to_code = trim($request->bill_to_code ?? '');
        $customer_id = trim($request->customer_id ?? 0);
        $customer_code = trim($request->customer_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $customer_code = strlen($customer_code) === 7 ? $customer_code . '0' : $customer_code;

        $ORDER->select(['id', 'order_number', 'bill_to_customer_id', 'order_date_time']);

        $bill_to_company = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_company_id = $bill_to_company->id ?? 0;

        if ($customer_id > 0) {
            $ORDER->where(function ($query) use ($customer_id, $bill_to_code, $bill_to_company_id) {
                if ($bill_to_code !== '') {
                    $query->whereHas('bill_to_customer', function ($query1) use ($bill_to_company_id) {
                        return $query1->where('id', $bill_to_company_id);
                    });

                    $query->where(function ($query1) use ($customer_id) {
                        $query1->whereHas('pickups', function ($query2) use ($customer_id) {
                            return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                return $query3->where('id', $customer_id);
                            });
                        });

                        $query1->orWhereHas('deliveries', function ($query2) use ($customer_id) {
                            return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                return $query3->where('id', $customer_id);
                            });
                        });
                    });
                } else {
                    $query->whereHas('bill_to_customer', function ($query1) use ($customer_id) {
                        return $query1->where('id', $customer_id);
                    });

                    $query->orWhereHas('pickups', function ($query1) use ($customer_id) {
                        return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                            return $query2->where('id', $customer_id);
                        });
                    });

                    $query->orWhereHas('deliveries', function ($query1) use ($customer_id) {
                        return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                            return $query2->where('id', $customer_id);
                        });
                    });
                }
            });
        } else {
            if ($customer_code !== '') {
                $customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$customer_code'")->first();

                if ($customer) {
                    $customer_id = $customer->id;

                    $ORDER->where(function ($query) use ($customer_id, $bill_to_code, $bill_to_company_id) {
                        if ($bill_to_code !== '') {
                            $query->whereHas('bill_to_customer', function ($query1) use ($bill_to_company_id) {
                                return $query1->where('id', $bill_to_company_id);
                            });

                            $query->where(function ($query1) use ($customer_id) {
                                $query1->whereHas('pickups', function ($query2) use ($customer_id) {
                                    return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                        return $query3->where('id', $customer_id);
                                    });
                                });

                                $query1->orWhereHas('deliveries', function ($query2) use ($customer_id) {
                                    return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                        return $query3->where('id', $customer_id);
                                    });
                                });
                            });
                        } else {
                            $query->whereHas('bill_to_customer', function ($query1) use ($customer_id) {
                                return $query1->where('id', $customer_id);
                            });

                            $query->orWhereHas('pickups', function ($query1) use ($customer_id) {
                                return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                                    return $query2->where('id', $customer_id);
                                });
                            });

                            $query->orWhereHas('deliveries', function ($query1) use ($customer_id) {
                                return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                                    return $query2->where('id', $customer_id);
                                });
                            });
                        }
                    });
                }
            }
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->where(function ($query) use ($date_start, $date_end) {
                $query->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
            });
        } else {
            if ($date_start !== '') {
                $ORDER->where(function ($query) use ($date_start) {
                    $query->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
                });
            }

            if ($date_end !== '') {
                $ORDER->where(function ($query) use ($date_end) {
                    $query->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
                });
            }
        }

        if ($city_origin !== '') {
            $ORDER->where(function ($query) use ($city_origin) {
                $query->whereHas('routing', function ($query1) use ($city_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($city_destination !== '') {
            $ORDER->where(function ($query) use ($city_destination) {
                $query->whereHas('routing', function ($query1) use ($city_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($state_origin !== '') {
            $ORDER->where(function ($query) use ($state_origin) {
                $query->whereHas('routing', function ($query1) use ($state_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($state_destination !== '') {
            $ORDER->where(function ($query) use ($state_destination) {
                $query->whereHas('routing', function ($query1) use ($state_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($zip_origin !== '') {
            $ORDER->where(function ($query) use ($zip_origin) {
                $query->whereHas('routing', function ($query1) use ($zip_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($zip_destination !== '') {
            $ORDER->where(function ($query) use ($zip_destination) {
                $query->whereHas('routing', function ($query1) use ($zip_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        $ORDER->with('bill_to_customer', function ($query) {
//           return $query->without(['contacts', 'term'])->orderBy('code')->orderBy('code_number');
            return $query->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
        });

//        $ORDER->orderBy('order_date_time', 'desc');

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRevenueCarrier(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $bill_to_code = trim($request->bill_to_code ?? '');
        $carrier_id = trim($request->carrier_id ?? 0);
        $carrier_code = trim($request->carrier_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $carrier_code = strlen($carrier_code) === 7 ? $carrier_code . '0' : $carrier_code;

        $ORDER->select(['id', 'order_number', 'bill_to_customer_id', 'order_date_time']);

        $bill_to_company = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_company_id = $bill_to_company->id ?? 0;

        if ($carrier_id > 0) {
            $ORDER->where(function ($query) use ($carrier_id) {
                $query->whereHas('carrier', function ($query1) use ($carrier_id) {
                    return $query1->where('id', $carrier_id);
                });
            });
        } else {
            if ($carrier_code !== '') {
                $carrier = Carrier::query()->whereRaw("CONCAT(`code`, `code_number`) = '$carrier_code'")->first();

                if ($carrier) {
                    $carrier_id = $carrier->id;

                    $ORDER->where(function ($query) use ($carrier_id) {
                        $query->whereHas('carrier', function ($query1) use ($carrier_id) {
                            return $query1->where('id', $carrier_id);
                        });
                    });
                }
            }
        }

        if ($bill_to_code !== '') {
            $ORDER->whereHas('bill_to_customer', function ($query1) use ($bill_to_company_id) {
                return $query1->where('id', $bill_to_company_id);
            });
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->where(function ($query) use ($date_start, $date_end) {
                $query->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
            });
        } else {
            if ($date_start !== '') {
                $ORDER->where(function ($query) use ($date_start) {
                    $query->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
                });
            }

            if ($date_end !== '') {
                $ORDER->where(function ($query) use ($date_end) {
                    $query->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
                });
            }
        }

        if ($city_origin !== '') {
            $ORDER->where(function ($query) use ($city_origin) {
                $query->whereHas('routing', function ($query1) use ($city_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($city_destination !== '') {
            $ORDER->where(function ($query) use ($city_destination) {
                $query->whereHas('routing', function ($query1) use ($city_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($state_origin !== '') {
            $ORDER->where(function ($query) use ($state_origin) {
                $query->whereHas('routing', function ($query1) use ($state_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($state_destination !== '') {
            $ORDER->where(function ($query) use ($state_destination) {
                $query->whereHas('routing', function ($query1) use ($state_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($zip_origin !== '') {
            $ORDER->where(function ($query) use ($zip_origin) {
                $query->whereHas('routing', function ($query1) use ($zip_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($zip_destination !== '') {
            $ORDER->where(function ($query) use ($zip_destination) {
                $query->whereHas('routing', function ($query1) use ($zip_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        $ORDER->with('bill_to_customer', function ($query) {
//           return $query->without(['contacts', 'term'])->orderBy('code')->orderBy('code_number');
            return $query->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
        });

//        $ORDER->orderBy('order_date_time', 'desc');

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistoryCustomer(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $bill_to_code = trim($request->bill_to_code ?? '');
        $customer_id = trim($request->customer_id ?? 0);
        $customer_code = trim($request->customer_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $customer_code = strlen($customer_code) === 7 ? $customer_code . '0' : $customer_code;

        $ORDER->select(['id', 'order_number', 'bill_to_customer_id', 'order_date_time']);

        $bill_to_company = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_company_id = $bill_to_company->id ?? 0;

        if ($customer_id > 0) {
            $ORDER->where(function ($query) use ($customer_id, $bill_to_code, $bill_to_company_id) {
                if ($bill_to_code !== '') {
                    $query->whereHas('bill_to_customer', function ($query1) use ($bill_to_company_id) {
                        return $query1->where('id', $bill_to_company_id);
                    });

                    $query->where(function ($query1) use ($customer_id) {
                        $query1->whereHas('pickups', function ($query2) use ($customer_id) {
                            return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                return $query3->where('id', $customer_id);
                            });
                        });

                        $query1->orWhereHas('deliveries', function ($query2) use ($customer_id) {
                            return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                return $query3->where('id', $customer_id);
                            });
                        });
                    });
                } else {
                    $query->whereHas('bill_to_customer', function ($query1) use ($customer_id) {
                        return $query1->where('id', $customer_id);
                    });

                    $query->orWhereHas('pickups', function ($query1) use ($customer_id) {
                        return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                            return $query2->where('id', $customer_id);
                        });
                    });

                    $query->orWhereHas('deliveries', function ($query1) use ($customer_id) {
                        return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                            return $query2->where('id', $customer_id);
                        });
                    });
                }
            });
        } else {
            if ($customer_code !== '') {
                $customer = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$customer_code'")->first();

                if ($customer) {
                    $customer_id = $customer->id;

                    $ORDER->where(function ($query) use ($customer_id, $bill_to_code, $bill_to_company_id) {
                        if ($bill_to_code !== '') {
                            $query->whereHas('bill_to_customer', function ($query1) use ($bill_to_company_id) {
                                return $query1->where('id', $bill_to_company_id);
                            });

                            $query->where(function ($query1) use ($customer_id) {
                                $query1->whereHas('pickups', function ($query2) use ($customer_id) {
                                    return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                        return $query3->where('id', $customer_id);
                                    });
                                });

                                $query1->orWhereHas('deliveries', function ($query2) use ($customer_id) {
                                    return $query2->whereHas('customer', function ($query3) use ($customer_id) {
                                        return $query3->where('id', $customer_id);
                                    });
                                });
                            });
                        } else {
                            $query->whereHas('bill_to_customer', function ($query1) use ($customer_id) {
                                return $query1->where('id', $customer_id);
                            });

                            $query->orWhereHas('pickups', function ($query1) use ($customer_id) {
                                return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                                    return $query2->where('id', $customer_id);
                                });
                            });

                            $query->orWhereHas('deliveries', function ($query1) use ($customer_id) {
                                return $query1->whereHas('customer', function ($query2) use ($customer_id) {
                                    return $query2->where('id', $customer_id);
                                });
                            });
                        }
                    });
                }
            }
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->where(function ($query) use ($date_start, $date_end) {
                $query->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
            });
        } else {
            if ($date_start !== '') {
                $ORDER->where(function ($query) use ($date_start) {
                    $query->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
                });
            }

            if ($date_end !== '') {
                $ORDER->where(function ($query) use ($date_end) {
                    $query->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
                });
            }
        }

        if ($city_origin !== '') {
            $ORDER->where(function ($query) use ($city_origin) {
                $query->whereHas('routing', function ($query1) use ($city_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($city_destination !== '') {
            $ORDER->where(function ($query) use ($city_destination) {
                $query->whereHas('routing', function ($query1) use ($city_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($state_origin !== '') {
            $ORDER->where(function ($query) use ($state_origin) {
                $query->whereHas('routing', function ($query1) use ($state_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($state_destination !== '') {
            $ORDER->where(function ($query) use ($state_destination) {
                $query->whereHas('routing', function ($query1) use ($state_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($zip_origin !== '') {
            $ORDER->where(function ($query) use ($zip_origin) {
                $query->whereHas('routing', function ($query1) use ($zip_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($zip_destination !== '') {
            $ORDER->where(function ($query) use ($zip_destination) {
                $query->whereHas('routing', function ($query1) use ($zip_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        $ORDER->with('bill_to_customer', function ($query) {
            return $query->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
        });

        $ORDER->with('pickups', function ($query) {
            $query->with('customer', function ($query1) {
                return $query1->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
            });
        });

        $ORDER->with('deliveries', function ($query) {
            $query->with('customer', function ($query1) {
                return $query1->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
            });
        });

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderHistoryCarrier(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $bill_to_code = trim($request->bill_to_code ?? '');
        $carrier_id = trim($request->carrier_id ?? 0);
        $carrier_code = trim($request->carrier_code ?? '');
        $date_start = trim($request->date_start ?? '');
        $date_end = trim($request->date_end ?? '');
        $city_origin = trim(strtolower($request->city_origin ?? ''));
        $city_destination = trim(strtolower($request->city_destination ?? ''));
        $state_origin = trim(strtolower($request->state_origin ?? ''));
        $state_destination = trim(strtolower($request->state_destination ?? ''));
        $zip_origin = trim(strtolower($request->zip_origin ?? ''));
        $zip_destination = trim(strtolower($request->zip_destination ?? ''));

        $bill_to_code = strlen($bill_to_code) === 7 ? $bill_to_code . '0' : $bill_to_code;
        $carrier_code = strlen($carrier_code) === 7 ? $carrier_code . '0' : $carrier_code;

        $ORDER->select(['id', 'order_number', 'bill_to_customer_id', 'order_date_time']);

        $bill_to_company = Customer::query()->whereRaw("CONCAT(`code`, `code_number`) = '$bill_to_code'")->first();
        $bill_to_company_id = $bill_to_company->id ?? 0;

        if ($carrier_id > 0) {
            $ORDER->where(function ($query) use ($carrier_id) {
                $query->whereHas('carrier', function ($query1) use ($carrier_id) {
                    return $query1->where('id', $carrier_id);
                });
            });
        } else {
            if ($carrier_code !== '') {
                $carrier = Carrier::query()->whereRaw("CONCAT(`code`, `code_number`) = '$carrier_code'")->first();

                if ($carrier) {
                    $carrier_id = $carrier->id;

                    $ORDER->where(function ($query) use ($carrier_id) {
                        $query->whereHas('carrier', function ($query1) use ($carrier_id) {
                            return $query1->where('id', $carrier_id);
                        });
                    });
                }
            }
        }

        if ($bill_to_code !== '') {
            $ORDER->whereHas('bill_to_customer', function ($query1) use ($bill_to_company_id) {
                return $query1->where('id', $bill_to_company_id);
            });
        }

        if ($date_start !== '' && $date_end !== '') {
            $ORDER->where(function ($query) use ($date_start, $date_end) {
                $query->whereRaw("DATE(order_date_time) BETWEEN STR_TO_DATE('$date_start', '%m/%d/%Y') AND STR_TO_DATE('$date_end', '%m/%d/%Y')");
            });
        } else {
            if ($date_start !== '') {
                $ORDER->where(function ($query) use ($date_start) {
                    $query->whereRaw("DATE(order_date_time) >= STR_TO_DATE('$date_start', '%m/%d/%Y')");
                });
            }

            if ($date_end !== '') {
                $ORDER->where(function ($query) use ($date_end) {
                    $query->whereRaw("DATE(order_date_time) <= STR_TO_DATE('$date_end', '%m/%d/%Y')");
                });
            }
        }

        if ($city_origin !== '') {
            $ORDER->where(function ($query) use ($city_origin) {
                $query->whereHas('routing', function ($query1) use ($city_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($city_origin) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($city_destination !== '') {
            $ORDER->where(function ($query) use ($city_destination) {
                $query->whereHas('routing', function ($query1) use ($city_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($city_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($city_destination) {
                                return $query3->whereRaw("LOWER(`city`) = '$city_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($state_origin !== '') {
            $ORDER->where(function ($query) use ($state_origin) {
                $query->whereHas('routing', function ($query1) use ($state_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($state_origin) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($state_destination !== '') {
            $ORDER->where(function ($query) use ($state_destination) {
                $query->whereHas('routing', function ($query1) use ($state_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($state_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($state_destination) {
                                return $query3->whereRaw("LOWER(`state`) = '$state_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        if ($zip_origin !== '') {
            $ORDER->where(function ($query) use ($zip_origin) {
                $query->whereHas('routing', function ($query1) use ($zip_origin) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_origin) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_origin) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_origin'");
                            });
                        })
                        ->orderBy('id')->limit(1);
                });
            });
        }

        if ($zip_destination !== '') {
            $ORDER->where(function ($query) use ($zip_destination) {
                $query->whereHas('routing', function ($query1) use ($zip_destination) {
                    return $query1
                        ->whereHas('pickup', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orWhereHas('delivery', function ($query2) use ($zip_destination) {
                            return $query2->whereHas('customer', function ($query3) use ($zip_destination) {
                                return $query3->whereRaw("LOWER(`zip`) = '$zip_destination'");
                            });
                        })
                        ->orderBy('id', 'desc')->limit(1);
                });
            });
        }

        $ORDER->with('bill_to_customer', function ($query) {
            return $query->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
        });

        $ORDER->with('pickups', function ($query) {
            $query->with('customer', function ($query1) {
                return $query1->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
            });
        });

        $ORDER->with('deliveries', function ($query) {
            $query->with('customer', function ($query1) {
                return $query1->select(['id', 'code', 'code_number', 'name', 'city', 'state']);
            });
        });

        $orders = $ORDER->get();

        return response()->json(['result' => 'OK', 'orders' => $orders]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderById(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $id = $request->id ?? 0;

        $order = $ORDER->where('id', $id)
            ->with([
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
            ])
            ->first();

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderByOrderNumber(Request $request): JsonResponse
    {
        $ORDER = Order::query();

        $order_number = $request->order_number ?? 0;
        $user_code = $request->user_code ?? '';

        if ($user_code !== '') {
            $ORDER->whereHas('bill_to_company', function ($query) use ($user_code){
                return $query->where('agent_code', $user_code);
            });
        }

        $ORDER->where('order_number', $order_number)
            ->with([
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

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderByTripNumber(Request $request): JsonResponse
    {
        $ORDER = new Order();

        $trip_number = $request->trip_number ?? 0;
        $user_code = $request->user_code ?? '';

        if ($user_code !== '') {
            $ORDER->whereHas('bill_to_company', function ($query) use ($user_code){
                return $query->where('agent_code', $user_code);
            });
        }

        $ORDER->where('trip_number', $trip_number)
            ->with([
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

        $result = $order ? 'OK' : 'NOT FOUND';

        return response()->json(['result' => $result, 'order' => $order]);
    }

    /**
     * @throws Exception
     */
    public function getLastOrderNumber(): JsonResponse
    {
        $ORDER = new Order();

        $last_order_number = $ORDER->max('order_number');

        if (!$last_order_number) {
            $last_order_number = 0;
        }

        return response()->json(['result' => 'OK', 'last_order_number' => $last_order_number]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrder(Request $request): JsonResponse
    {
        $ORDER = new Order();

        $order_number = (int)($request->order_number ?? 0);
        $user_code_id = $request->user_code_id ?? null;
        $trip_number = (int)($request->trip_number ?? 0);
        $division_id = isset($request->division_id) ? $request->division_id > 0 ? $request->division_id : null : null;
        $load_type_id = isset($request->load_type_id) ? $request->load_type_id > 0 ? $request->load_type_id : null : null;
        $template_id = isset($request->template_id) ? $request->template_id > 0 ? $request->template_id : null : null;
        $bill_to_customer_id = isset($request->bill_to_customer_id) ? $request->bill_to_customer_id > 0 ? $request->bill_to_customer_id : null : null;
        $carrier_id = isset($request->carrier_id) ? $request->carrier_id > 0 ? $request->carrier_id : null : null;
        $carrier_load = $request->carrier_load ?? '';

        $carrier_contact_id = $request->carrier_contact_id ?? null;
        $carrier_contact_primary_phone = $request->carrier_contact_primary_phone ?? 'work';

        $equipment_id = $request->equipment_id ?? null;
        $carrier_driver_id = isset($request->carrier_driver_id) ? $request->carrier_driver_id > 0 ? $request->carrier_driver_id : null : null;
        $agent_code = $request->agent_code ?? '';
        $agent_commission = $request->agent_commission ?? '';
        $salesman_code = $request->salesman_code ?? '';
        $salesman_commission = $request->salesman_commission ?? '';
        $miles = $request->miles ?? 0;
        $charges = $request->charges ?? '';
        $order_cost = $request->order_cost ?? '';
        $profit = $request->profit ?? '';
        $percentage = $request->percentage ?? '';
        $haz_mat = $request->haz_mat ?? 0;
        $expedited = $request->expedited ?? 0;
        $is_cancelled = $request->is_cancelled ?? 0;

        $customer_check_number = $request->customer_check_number ?? null;
        $customer_date_received = $request->customer_date_received ?? null;
        $invoice_received_date = $request->invoice_received_date ?? null;
        $invoice_number = $request->invoice_number ?? null;
        $term_id = $request->term_id ?? null;
        $invoice_date_paid = $request->invoice_date_paid ?? null;
        $carrier_check_number = $request->carrier_check_number ?? null;
        $invoice_customer_reviewed = $request->invoice_customer_reviewed ?? 0;
        $order_invoiced = $request->order_invoiced ?? 0;
        $invoice_carrier_previewed = $request->invoice_carrier_previewed ?? 0;
        $invoice_carrier_received = $request->invoice_carrier_received ?? 0;
        $invoice_bol_received = $request->invoice_bol_received ?? 0;
        $invoice_rate_conf_received = $request->invoice_rate_conf_received ?? 0;
        $invoice_carrier_approved = $request->invoice_carrier_approved ?? 0;

        $pickups = $request->pickups ?? [];

        $last_order_number = $ORDER->max('order_number');
        $last_trip_number = $ORDER->max('trip_number');

        if ($order_number === 0) {
            if ($last_order_number && $last_order_number >= 32000) {
                $order_number = $last_order_number + 1;
            } else {
                $order_number = 32000;
            }

            if ($carrier_id > 0) {
                if ($last_trip_number) {
                    $trip_number = $last_trip_number + 1;
                } else {
                    $trip_number = 1;
                }
            }
        } else {
            if ($carrier_id > 0 && $trip_number === 0) {
                if ($last_trip_number) {
                    $trip_number = $last_trip_number + 1;
                } else {
                    $trip_number = 1;
                }
            }
        }

        $order = $ORDER->updateOrCreate([
            'order_number' => $order_number
        ], [
            'user_code_id' => $user_code_id,
            'trip_number' => $trip_number,
            'division_id' => $division_id,
            'load_type_id' => $load_type_id,
            'template_id' => $template_id,
            'bill_to_customer_id' => $bill_to_customer_id,
            'carrier_id' => $carrier_id,
            'carrier_contact_id' => $carrier_contact_id,
            'carrier_contact_primary_phone' => $carrier_contact_primary_phone,
            'carrier_load' => $carrier_load,
            'equipment_id' => $equipment_id,
            'carrier_driver_id' => $carrier_driver_id,
            'agent_code' => $agent_code,
            'agent_commission' => $agent_commission,
            'salesman_code' => $salesman_code,
            'salesman_commission' => $salesman_commission,
            'miles' => $miles,
            'charges' => $charges,
            'order_cost' => $order_cost,
            'profit' => $profit,
            'percentage' => $percentage,
            'haz_mat' => $haz_mat,
            'expedited' => $expedited,
            'customer_check_number' => $customer_check_number,
            'customer_date_received' => $customer_date_received,
            'invoice_received_date' => $invoice_received_date,
            'invoice_number' => $invoice_number,
            'term_id' => $term_id,
            'invoice_date_paid' => $invoice_date_paid,
            'carrier_check_number' => $carrier_check_number,
            'invoice_customer_reviewed' => $invoice_customer_reviewed,
            'order_invoiced' => $order_invoiced,
            'invoice_carrier_previewed' => $invoice_carrier_previewed,
            'invoice_carrier_received' => $invoice_carrier_received,
            'invoice_bol_received' => $invoice_bol_received,
            'invoice_rate_conf_received' => $invoice_rate_conf_received,
            'invoice_carrier_approved' => $invoice_carrier_approved,
            'is_cancelled' => $is_cancelled
        ]);

        if (count($pickups) > 0) {
            $PICKUP = new Pickup();

            for ($i = 0; $i < count($pickups); $i++) {
                $pickup = $pickups[$i];

                if (($pickup['toSave'] ?? false)) {
                    if (($pickup['customer_id'] ?? 0) > 0) {
                        $PICKUP->updateOrCreate([
                            'id' => $pickup['id'] ?? 0
                        ], [
                            'order_id' => $order->id,
                            'customer_id' => $pickup['customer_id'],
                            'type' => 'pickup',
                            'pu_date1' => $pickup['pu_date1'] ?? '',
                            'pu_date2' => $pickup['pu_date2'] ?? '',
                            'pu_time1' => $pickup['pu_time1'] ?? '',
                            'pu_time2' => $pickup['pu_time2'] ?? '',
                            'bol_numbers' => $pickup['bol_numbers'] ?? '',
                            'po_numbers' => $pickup['po_numbers'] ?? '',
                            'ref_numbers' => $pickup['ref_numbers'] ?? '',
                            'seal_number' => $pickup['seal_number'] ?? '',
                            'special_instructions' => $pickup['special_instructions'] ?? ''
                        ]);
                    }
                }
            }
        }

        $newOrder = $ORDER->where('order_number', $order->order_number ?? 0)
            ->with([
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
            ])->first();

        return response()->json(['result' => 'OK', 'order' => $newOrder, 'order_number' => $order_number]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderEvent(Request $request): JsonResponse
    {
        $ORDER_EVENT = new OrderEvent();
        $order_id = $request->order_id ?? 0;
        $event_type_id = $request->event_type_id ?? null;
        $shipper_id = isset($request->shipper_id) ? $request->shipper_id > 0 ? $request->shipper_id : null : null;
        $consignee_id = isset($request->consignee_id) ? $request->consignee_id > 0 ? $request->consignee_id : null : null;
        $arrived_customer_id = isset($request->arrived_customer_id) ? $request->arrived_customer_id > 0 ? $request->arrived_customer_id : null : null;
        $departed_customer_id = isset($request->departed_customer_id) ? $request->departed_customer_id > 0 ? $request->departed_customer_id : null : null;
        $old_carrier_id = isset($request->old_carrier_id) ? $request->old_carrier_id > 0 ? $request->old_carrier_id : null : null;
        $new_carrier_id = isset($request->new_carrier_id) ? $request->new_carrier_id > 0 ? $request->new_carrier_id : null : null;
        $time = $request->time ?? '';
        $event_time = $request->event_time ?? '';
        $date = $request->date ?? '';
        $event_date = $request->event_date ?? '';
        $user_code_id = $request->user_code_id ?? null;
        $event_location = $request->event_location ?? '';
        $event_notes = $request->event_notes ?? '';

        if ($order_id === 0) {
            return response()->json(['result' => 'ORDER ID NOT VALID', 'order_id' => $order_id]);
        }

        $order_event = $ORDER_EVENT->updateOrCreate([
            'order_id' => $order_id,
            'event_type_id' => $event_type_id,
            'shipper_id' => $shipper_id,
            'consignee_id' => $consignee_id,
            'arrived_customer_id' => $arrived_customer_id,
            'departed_customer_id' => $departed_customer_id,
            'old_carrier_id' => $old_carrier_id,
            'new_carrier_id' => $new_carrier_id,
        ], [
            'order_id' => $order_id,
            'event_type_id' => $event_type_id,
            'shipper_id' => $shipper_id,
            'consignee_id' => $consignee_id,
            'arrived_customer_id' => $arrived_customer_id,
            'departed_customer_id' => $departed_customer_id,
            'old_carrier_id' => $old_carrier_id,
            'new_carrier_id' => $new_carrier_id,
            'time' => $time,
            'event_time' => $event_time,
            'date' => $date,
            'event_date' => $event_date,
            'user_code_id' => $user_code_id,
            'event_location' => $event_location,
            'event_notes' => $event_notes
        ]);

        $order_events = $ORDER_EVENT->where('order_id', $order_id)
            ->with(['shipper', 'consignee', 'arrived_customer', 'departed_customer', 'old_carrier', 'new_carrier', 'event_type', 'user_code'])
            ->orderBy('updated_at', 'desc')->get();

        return response()->json(['result' => 'OK', 'order_event' => $order_event, 'order_events' => $order_events]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderPickup(Request $request): JsonResponse
    {
        $PICKUP = new Pickup();
        $ORDER = new Order();

        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $contact_id = $request->contact_id ?? null;
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $contact_phone_ext = $request->contact_phone_ext ?? '';
        $contact_primary_phone = $request->contact_primary_phone ?? 'work';
        $pu_date1 = $request->pu_date1 ?? '';
        $pu_date2 = $request->pu_date2 ?? '';
        $pu_time1 = $request->pu_time1 ?? '';
        $pu_time2 = $request->pu_time2 ?? '';
        $bol_numbers = $request->bol_numbers ?? '';
        $po_numbers = $request->po_numbers ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $seal_number = $request->seal_number ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'pickup';

        if ($order_id > 0) {
            if ($customer_id > 0) {
                $pickup = $PICKUP->updateOrCreate([
                    'id' => $id
                ], [
                    'order_id' => $order_id,
                    'customer_id' => $customer_id,
                    'type' => $type,
                    'contact_id' => $contact_id === '' ? null : $contact_id,
                    'contact_name' => $contact_name,
                    'contact_phone' => $contact_phone,
                    'contact_phone_ext' => $contact_phone_ext,
                    'contact_primary_phone' => $contact_primary_phone,
                    'pu_date1' => $pu_date1,
                    'pu_date2' => $pu_date2,
                    'pu_time1' => $pu_time1,
                    'pu_time2' => $pu_time2,
                    'bol_numbers' => $bol_numbers,
                    'po_numbers' => $po_numbers,
                    'ref_numbers' => $ref_numbers,
                    'seal_number' => $seal_number,
                    'special_instructions' => $special_instructions
                ]);

                $pickup = $PICKUP->where('id', $pickup->id ?? 0)->with(['customer'])->first();

                $order = $ORDER->where('id', $order_id)->with([
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
                ])->first();

                return response()->json(['result' => 'OK', 'pickup' => $pickup, 'order' => $order]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateOrderPickup(Request $request): JsonResponse
    {
        $TEMPLATE_PICKUP = new TemplatePickup();
        $TEMPLATE = new Template();

        $template_id = $request->template_id ?? 0;
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $pu_date1 = $request->pu_date1 ?? '';
        $pu_date2 = $request->pu_date2 ?? '';
        $pu_time1 = $request->pu_time1 ?? '';
        $pu_time2 = $request->pu_time2 ?? '';
        $bol_numbers = $request->bol_numbers ?? '';
        $po_numbers = $request->po_numbers ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $seal_number = $request->seal_number ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'pickup';

        if ($template_id > 0) {
            if ($customer_id > 0) {
                $pickup = $TEMPLATE_PICKUP->updateOrCreate([
                    'id' => $id
                ], [
                    'template_id' => $template_id,
                    'customer_id' => $customer_id,
                    'type' => $type,
                    'pu_date1' => $pu_date1,
                    'pu_date2' => $pu_date2,
                    'pu_time1' => $pu_time1,
                    'pu_time2' => $pu_time2,
                    'bol_numbers' => $bol_numbers,
                    'po_numbers' => $po_numbers,
                    'ref_numbers' => $ref_numbers,
                    'seal_number' => $seal_number,
                    'special_instructions' => $special_instructions
                ]);

                $pickup = $TEMPLATE_PICKUP->where('id', $pickup->id ?? 0)->with(['customer'])->first();

                $template = $TEMPLATE->where('id', $template_id)->with([
                    'bill_to_company',
                    'carrier',
                    'equipment',
                    'driver',
                    'notes_for_carrier',
                    'internal_notes',
                    'pickups',
                    'deliveries',
                    'routing',
                    'division',
                    'load_type',
                    'order_customer_ratings',
                    'order_carrier_ratings'
                ])->first();

                return response()->json(['result' => 'OK', 'pickup' => $pickup, 'template' => $template]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderDelivery(Request $request): JsonResponse
    {
        $DELIVERY = new Delivery();
        $ORDER = new Order();

        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $contact_id = $request->contact_id ?? null;
        $contact_name = $request->contact_name ?? '';
        $contact_phone = $request->contact_phone ?? '';
        $contact_phone_ext = $request->contact_phone_ext ?? '';
        $contact_primary_phone = $request->contact_primary_phone ?? 'work';
        $delivery_date1 = $request->delivery_date1 ?? '';
        $delivery_date2 = $request->delivery_date2 ?? '';
        $delivery_time1 = $request->delivery_time1 ?? '';
        $delivery_time2 = $request->delivery_time2 ?? '';
        $bol_numbers = $request->bol_numbers ?? '';
        $po_numbers = $request->po_numbers ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $seal_number = $request->seal_number ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'delivery';

        if ($order_id > 0) {
            if ($customer_id > 0) {
                $delivery = $DELIVERY->updateOrCreate([
                    'id' => $id
                ], [
                    'order_id' => $order_id,
                    'customer_id' => $customer_id,
                    'type' => $type,
                    'contact_id' => $contact_id === '' ? null : $contact_id,
                    'contact_name' => $contact_name,
                    'contact_phone' => $contact_phone,
                    'contact_phone_ext' => $contact_phone_ext,
                    'contact_primary_phone' => $contact_primary_phone,
                    'delivery_date1' => $delivery_date1,
                    'delivery_date2' => $delivery_date2,
                    'delivery_time1' => $delivery_time1,
                    'delivery_time2' => $delivery_time2,
                    'bol_numbers' => $bol_numbers,
                    'po_numbers' => $po_numbers,
                    'ref_numbers' => $ref_numbers,
                    'seal_number' => $seal_number,
                    'special_instructions' => $special_instructions
                ]);

                $delivery = $DELIVERY->where('id', $delivery->id ?? 0)->with(['customer'])->first();

                $order = $ORDER->where('id', $order_id)->with([
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
                ])->first();

                return response()->json(['result' => 'OK', 'delivery' => $delivery, 'order' => $order]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateOrderDelivery(Request $request): JsonResponse
    {
        $TEMPLATE_DELIVERY = new TemplateDelivery();
        $TEMPLATE = new Template();

        $template_id = $request->template_id ?? 0;
        $id = $request->id ?? 0;
        $customer_id = $request->customer_id ?? 0;
        $delivery_date1 = $request->delivery_date1 ?? '';
        $delivery_date2 = $request->delivery_date2 ?? '';
        $delivery_time1 = $request->delivery_time1 ?? '';
        $delivery_time2 = $request->delivery_time2 ?? '';
        $special_instructions = $request->special_instructions ?? null;
        $type = $request->type ?? 'delivery';

        if ($template_id > 0) {
            if ($customer_id > 0) {
                $delivery = $TEMPLATE_DELIVERY->updateOrCreate([
                    'id' => $id
                ], [
                    'template_id' => $template_id,
                    'customer_id' => $customer_id,
                    'type' => $type,
                    'delivery_date1' => $delivery_date1,
                    'delivery_date2' => $delivery_date2,
                    'delivery_time1' => $delivery_time1,
                    'delivery_time2' => $delivery_time2,
                    'special_instructions' => $special_instructions
                ]);

                $delivery = $TEMPLATE_DELIVERY->where('id', $delivery->id ?? 0)->with(['customer'])->first();

                $template = $TEMPLATE->where('id', $template_id)->with([
                    'bill_to_company',
                    'carrier',
                    'equipment',
                    'driver',
                    'notes_for_carrier',
                    'internal_notes',
                    'pickups',
                    'deliveries',
                    'routing',
                    'division',
                    'load_type',
                    'order_customer_ratings',
                    'order_carrier_ratings',
                ])->first();

                return response()->json(['result' => 'OK', 'delivery' => $delivery, 'template' => $template]);
            } else {
                return response()->json(['result' => 'NO CUSTOMER']);
            }
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeOrderPickup(Request $request): JsonResponse
    {
        $PICKUP = new Pickup();
        $ORDER = new Order();
        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;

        $pickup = $PICKUP->where('id', $id)->delete();
        $order = $ORDER->where('id', $order_id)->with([
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
        ])->first();

        return response()->json(['result' => 'OK', 'pickup' => $pickup, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeOrderDelivery(Request $request): JsonResponse
    {
        $DELIVERY = new Delivery();
        $ORDER = new Order();
        $order_id = $request->order_id ?? 0;
        $id = $request->id ?? 0;

        $delivery = $DELIVERY->where('id', $id)->delete();
        $order = $ORDER->where('id', $order_id)->with([
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
        ])->first();

        return response()->json(['result' => 'OK', 'delivery' => $delivery, 'order' => $order]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveOrderRouting(Request $request): JsonResponse
    {
        $ROUTE = new Route();
        $ORDER = new Order();
        $order_id = $request->order_id ?? 0;
        $routing = $request->routing ?? [];

        if ($order_id > 0) {
            $ROUTE->where('order_id', $order_id)->delete();

            if (count($routing) > 0) {
                for ($i = 0; $i < count($routing); $i++) {
                    $route = $routing[$i];

                    $ROUTE->updateOrCreate([
                        'id' => 0
                    ], [
                        'order_id' => $order_id,
                        'pickup_id' => $route['pickup_id'] ?? null,
                        'delivery_id' => $route['delivery_id'] ?? null,
                        'type' => $route['type'] ?? 'pickup'
                    ]);
                }
            }
            $order = $ORDER->where('id', $order_id)->with([
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
            ])->first();

            return response()->json(['result' => 'OK', 'order' => $order]);
        } else {
            return response()->json(['result' => 'NO ORDER']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saveTemplateOrderRouting(Request $request): JsonResponse
    {
        $TEMPLATE_ROUTE = new TemplateRoute();
        $TEMPLATE = new Template();
        $template_id = $request->template_id ?? 0;
        $routing = $request->routing ?? [];

        if ($template_id > 0) {
            $TEMPLATE_ROUTE->where('template_id', $template_id)->delete();

            if (count($routing) > 0) {
                for ($i = 0; $i < count($routing); $i++) {
                    $route = $routing[$i];

                    $TEMPLATE_ROUTE->updateOrCreate([
                        'id' => 0
                    ], [
                        'template_id' => $template_id,
                        'pickup_id' => $route['pickup_id'] ?? null,
                        'delivery_id' => $route['delivery_id'] ?? null,
                        'type' => $route['type'] ?? 'pickup'
                    ]);
                }
            }
            $template = $TEMPLATE->where('id', $template_id)->with([
                'bill_to_company',
                'carrier',
                'equipment',
                'driver',
                'notes_for_carrier',
                'internal_notes',
                'pickups',
                'deliveries',
                'routing',
                'division',
                'load_type',
                'order_customer_ratings',
                'order_carrier_ratings'
            ])->first();

            return response()->json(['result' => 'OK', 'template' => $template]);
        } else {
            return response()->json(['result' => 'NO TEMPLATE']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrdersRelatedData()
    {
        $CUSTOMER = new Customer();
        $CARRIER = new Carrier();
        $LOAD_TYPE = new LoadType();
        $EQUIPMENT = new Equipment();
        $RATE_TYPE = new RateType();
        $EVENT_TYPE = new EventType();

        $customers = $CUSTOMER->get();
        $carriers = $CARRIER->get();
        $load_types = $LOAD_TYPE->get();
        $equipments = $EQUIPMENT->get();
        $rate_types = $RATE_TYPE->get();
        $event_types = $EVENT_TYPE->get();

        return response()->json([
            'result' => 'OK',
            'customers' => $customers,
            'carriers' => $carriers,
            'load_types' => $load_types,
            'equipments' => $equipments,
            'rate_types' => $rate_types,
            'event_types' => $event_types
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitOrderImport(Request $request)
    {
        $order_number = $request->order ?? '';
        $trip_number = $request->trip ?? '';
        $load_type_id = $request->loadTypeId ?? 0;
        $haz_mat = $request->hazMat ?? 0;
        $expedited = $request->expedited ?? 0;
        $miles = $request->miles ?? 0;
        $order_date_time = $request->orderDateTime ?? '';
        $bill_to_customer_id = $request->billToCustomerId ?? 0;
        $carrier_id = $request->carrierId ?? 0;
        $equipment_type_id = $request->equipmentTypeId ?? 0;
        $shipper_customer_id = $request->shipperCustomerId ?? 0;
        $pu_date1 = $request->pu_date1 ?? '';
        $pu_date2 = $request->pu_date2 ?? '';
        $pu_time1 = $request->pu_time1 ?? '';
        $pu_time2 = $request->pu_time2 ?? '';
        $ref_numbers = $request->ref_numbers ?? '';
        $consignee_customer_id = $request->consigneeCustomerId ?? 0;
        $delivery_date1 = $request->delivery_date1 ?? '';
        $delivery_date2 = $request->delivery_date2 ?? '';
        $delivery_time1 = $request->delivery_time1 ?? '';
        $delivery_time2 = $request->delivery_time2 ?? '';
        $customer_rating = $request->customerRating ?? null;
        $carrier_rating = $request->carrierRating ?? null;
        $loaded_event = $request->loadedEvent ?? null;
        $delivered_event = $request->deliveredEvent ?? null;
        $user_code_id = $request->user_code_id ?? null;

        $ORDER = new Order();

        $order = $ORDER->updateOrCreate([
            'id' => 0
        ], [
            'order_number' => $order_number,
            'trip_number' => $trip_number,
            'user_code_id' => $user_code_id,
            'order_date_time' => $order_date_time,
            'load_type_id' => $load_type_id,
            'bill_to_customer_id' => $bill_to_customer_id,
            'carrier_id' => $carrier_id,
            'equipment_id' => $equipment_type_id,
            'miles' => $miles,
            'haz_mat' => $haz_mat,
            'expedited' => $expedited
        ]);

        $order_id = $order->id;

        if ($shipper_customer_id > 0) {
            $PICKUP = new Pickup();

            $pickup = $PICKUP->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'customer_id' => $shipper_customer_id,
                'pu_date1' => $pu_date1,
                'pu_time1' => $pu_time1,
                'pu_date2' => $pu_date2,
                'pu_time2' => $pu_time2,
                'ref_numbers' => $ref_numbers
            ]);
        }

        if ($consignee_customer_id > 0) {
            $DELIVERY = new Delivery();

            $delivery = $DELIVERY->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'customer_id' => $consignee_customer_id,
                'delivery_date1' => $delivery_date1,
                'delivery_time1' => $delivery_time1,
                'delivery_date2' => $delivery_date2,
                'delivery_time2' => $delivery_time2
            ]);
        }

        if ($shipper_customer_id > 0 && $consignee_customer_id > 0) {
            $ROUTE = new Route();

            $route = $ROUTE->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'pickup_id' => $pickup->id,
                'type' => 'pickup'
            ]);

            $route = $ROUTE->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'delivery_id' => $delivery->id,
                'type' => 'delivery'
            ]);
        }

        if ($customer_rating['total_charges'] > 0) {
            $ORDER_CUSTOMER_RATING = new OrderCustomerRating();

            $ORDER_CUSTOMER_RATING->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'rate_type_id' => $customer_rating['rateTypeId'],
                'description' => $customer_rating['description'],
                'pieces' => $customer_rating['pieces'],
                'pieces_unit' => 'sk',
                'weight' => $customer_rating['weight'],
                'total_charges' => $customer_rating['total_charges']
            ]);
        }

        if ($carrier_rating['total_charges'] > 0) {
            $ORDER_CARRIER_RATING = new OrderCarrierRating();

            $ORDER_CARRIER_RATING->updateOrCreate([
                'id' => 0
            ], [
                'order_id' => $order_id,
                'rate_type_id' => $carrier_rating['rateTypeId'],
                'description' => $carrier_rating['description'],
                'pieces' => $carrier_rating['pieces'],
                'pieces_unit' => 'sk',
                'weight' => $carrier_rating['weight'],
                'total_charges' => $carrier_rating['total_charges']
            ]);
        }

        $ORDER_EVENT = new OrderEvent();

        $ORDER_EVENT->updateOrCreate([
            'id' => 0
        ], [
            'order_id' => $order_id,
            'event_type_id' => $loaded_event['eventTypeId'],
            'time' => $loaded_event['time'],
            'event_time' => $loaded_event['eventTime'],
            'date' => $loaded_event['date'],
            'event_date' => $loaded_event['eventDate'],
            'event_location' => $loaded_event['eventLocation'],
            'event_notes' => $loaded_event['eventNotes'],
            'user_code_id' => $user_code_id
        ]);

        $ORDER_EVENT->updateOrCreate([
            'id' => 0
        ], [
            'order_id' => $order_id,
            'event_type_id' => $delivered_event['eventTypeId'],
            'time' => $delivered_event['time'],
            'event_time' => $delivered_event['eventTime'],
            'date' => $delivered_event['date'],
            'event_date' => $delivered_event['eventDate'],
            'event_location' => $delivered_event['eventLocation'],
            'event_notes' => $delivered_event['eventNotes'],
            'user_code_id' => $user_code_id
        ]);

        $newOrder = $ORDER->where('id', $order_id)->with([
            'pickups',
            'deliveries',
            'events',
            'order_customer_ratings',
            'order_carrier_ratings',
            'routing',
            'user_code'
        ]);

        return response()->json([
            'result' => 'OK',
            'order' => $newOrder
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function submitOrderImport2(Request $request): JsonResponse
    {
        $list = $request->list ?? [];

        if (count($list) > 0) {
            for ($i = 0; $i < count($list); $i++) {
                $item = $list[$i];

                $user_code_id = $item['user_code_id'] ?? null;
                $order_number = $item['order_number'];
                $trip_number = $item['trip_number'];
                $load_type_id = ($item['load_type_id'] ?? 0) === 0 ? null : $item['load_type_id'];
                $haz_mat = $item['haz_mat'];
                $expedited = $item['expedited'];
                $miles = ($item['miles'] ?? 0) * 1609.34;
                $order_date_time = $item['order_date_time'];
                $bill_to_customer_id = ($item['bill_to_customer_id'] ?? 0) === 0 ? null : $item['bill_to_customer_id'];
                $carrier_id = ($item['carrier_id'] ?? 0) === 0 ? null : $item['carrier_id'];
                $equipment_id = ($item['equipment_id'] ?? 0) === 0 ? null : $item['equipment_id'];
//                $order_pickups = $item['pickups'] ?? [];
//                $order_deliveries = $item['deliveries'] ?? [];
                $order_routing = $item['routing'] ?? [];
                $order_customer_rating = $item['customer_rating'] ?? [];
                $order_carrier_rating = $item['carrier_rating'] ?? [];
                $order_events = $item['events'] ?? [];
                $order_internal_notes = $item['internal_notes'] ?? [];
                $order_notes_for_carrier = $item['notes_for_carrier'] ?? [];

                $order = Order::updateOrCreate([
                    'id' => 0
                ], [
                    'order_number' => $order_number,
                    'trip_number' => $trip_number,
                    'load_type_id' => $load_type_id,
                    'haz_mat' => $haz_mat,
                    'expedited' => $expedited,
                    'miles' => $miles,
                    'order_date_time' => $order_date_time,
                    'bill_to_customer_id' => $bill_to_customer_id,
                    'carrier_id' => $carrier_id,
                    'equipment_id' => $equipment_id,
                    'is_imported' => 1,
                    'user_code_id' => $user_code_id
                ]);

                $order_id = $order->id;

                if ($order_id > 0) {
                    for ($p = 0; $p < count($order_routing); $p++) {
                        $route_item = $order_routing[$p];
                        $route_type = $route_item['type'];

                        if ($route_type === 'pickup') {
                            $pickup = Pickup::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'customer_id' => $route_item['customer_id'],
                                'pu_date1' => $route_item['pu_date1'] ?? '',
                                'pu_time1' => $route_item['pu_time1'] ?? '',
                                'pu_date2' => $route_item['pu_date2'] ?? '',
                                'pu_time2' => $route_item['pu_time2'] ?? '',
                                'po_numbers' => $route_item['po_numbers'] ?? '',
                                'bol_numbers' => $route_item['bol_numbers'] ?? '',
                                'ref_numbers' => $route_item['ref_numbers'] ?? '',
                                'seal_number' => $route_item['seal_number'] ?? ''
                            ]);

                            Route::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'pickup_id' => $pickup->id,
                                'delivery_id' => null,
                                'type' => $route_type
                            ]);
                        } else {
                            $delivery = Delivery::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'customer_id' => $route_item['customer_id'],
                                'delivery_date1' => $route_item['delivery_date1'] ?? '',
                                'delivery_time1' => $route_item['delivery_time1'] ?? '',
                                'delivery_date2' => $route_item['delivery_date2'] ?? '',
                                'delivery_time2' => $route_item['delivery_time2'] ?? '',
                                'po_numbers' => $route_item['po_numbers'] ?? '',
                                'bol_numbers' => $route_item['bol_numbers'] ?? '',
                                'ref_numbers' => $route_item['ref_numbers'] ?? '',
                                'seal_number' => $route_item['seal_number'] ?? ''
                            ]);

                            Route::updateOrCreate([
                                'id' => 0
                            ], [
                                'order_id' => $order_id,
                                'pickup_id' => null,
                                'delivery_id' => $delivery->id,
                                'type' => $route_type
                            ]);
                        }
                    }

                    for ($cus = 0; $cus < count($order_customer_rating); $cus++) {
                        $customer_rating_item = $order_customer_rating[$cus];

                        OrderCustomerRating::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'rate_type_id' => $customer_rating_item['rate_type_id'],
                            'description' => $customer_rating_item['description'],
                            'pieces' => $customer_rating_item['pieces'] ?? 0.00,
                            'pieces_unit' => $item['pieces_unit'] ?? 'sk',
                            'weight' => $customer_rating_item['weight'] ?? 0.00,
                            'total_charges' => $customer_rating_item['total_charges'] ?? 0.00
                        ]);
                    }

                    for ($car = 0; $car < count($order_carrier_rating); $car++) {
                        $carrier_rating_item = $order_carrier_rating[$car];

                        OrderCarrierRating::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'rate_type_id' => $carrier_rating_item['rate_type_id'],
                            'description' => $carrier_rating_item['description'],
                            'pieces' => $carrier_rating_item['pieces'] ?? 0.00,
                            'pieces_unit' => $carrier_rating_item['pieces_unit'] ?? 'sk',
                            'weight' => $carrier_rating_item['weight'] ?? 0.00,
                            'total_charges' => $carrier_rating_item['total_charges'] ?? 0.00
                        ]);
                    }

                    for ($e = 0; $e < count($order_events); $e++) {
                        $event_item = $order_events[$e];

                        OrderEvent::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'user_code_id' => $event_item['user_code_id'] ?? null,
                            'event_type_id' => $event_item['event_type_id'],
                            'shipper_id' => $event_item['shipper_id'] ?? null,
                            'consignee_id' => $event_item['consignee_id'] ?? null,
                            'old_carrier_id' => $event_item['old_carrier_id'] ?? null,
                            'new_carrier_id' => $event_item['new_carrier_id'] ?? null,
                            'time' => $event_item['time'] ?? '',
                            'date' => $event_item['date'] ?? '',
                            'event_time' => $event_item['event_time'] ?? '',
                            'event_date' => $event_item['event_date'] ?? '',
                            'event_location' => $event_item['event_location'] ?? '',
                            'event_notes' => $event_item['event_notes'] ?? ''
                        ]);
                    }

                    for ($n = 0; $n < count($order_internal_notes); $n++) {
                        $internal_note = $order_internal_notes[$n];

                        InternalNotes::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'user_code_id' => $internal_note['user_code_id'] ?? null,
                            'date_time' => $internal_note['date_time'] ?? null,
                            'text' => $internal_note['text'] ?? '',
                        ]);
                    }

                    for ($n = 0; $n < count($order_notes_for_carrier); $n++) {
                        $note_for_carrier = $order_notes_for_carrier[$n];

                        NotesForCarrier::updateOrCreate([
                            'id' => 0
                        ], [
                            'order_id' => $order_id,
                            'user_code_id' => $note_for_carrier['user_code_id'] ?? null,
                            'date_time' => $note_for_carrier['date_time'] ?? null,
                            'text' => $note_for_carrier['text'] ?? '',
                        ]);
                    }
                }


            }

            return response()->json(['result' => 'OK']);
        } else {
            return response()->json(['result' => 'NO LIST']);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function arrayTest(): JsonResponse
    {
        $date_time1 = '08/20/2015 09:58:11';
        $date_time2 = '08/20/2015 09:58:11';
        $date_time3 = '11/06/2021 07:45:00';
        $date_time4 = '11/06/2021 07:45:00';

        $date1 = DateTime::createFromFormat('m/d/Y H:i:s', $date_time1)->getTimestamp();
        $date2 = strtotime($date_time2);
        $date3 = DateTime::createFromFormat('m/d/Y H:i:s', $date_time3)->getTimestamp();
        $date4 = strtotime($date_time4);

        return response()->json([
            'date1' => $date1,
            'date2' => $date2,
            'date3' => $date3,
            'date4' => $date4
        ]);
    }


}


class OrderRouting
{

}

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}
