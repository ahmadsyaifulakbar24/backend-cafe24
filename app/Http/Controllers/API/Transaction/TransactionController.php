<?php

namespace App\Http\Controllers\API\Transaction;

use App\Helpers\FileHelpers;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\Transaction\PaymentResource;
use App\Http\Resources\Transaction\TransactionDetailResource;
use App\Http\Resources\Transaction\TransactionResource;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\ProductCombination;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\MootapayRepository;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function get(Request $request)
    {
        $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'till_date' => ['nullable', 'date_format:Y-m-d'],
            'limit_page' => ['required', 'in:0,1'],
            'limit' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:pending,paid_off,expired,sent,canceled,finish'],
            'except_pending_status' => ['nullable', 'in:0,1'],
            'payment_status' => ['nullable', 'in:paid,not_paid'],
            'invoice_number' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
        ]);
        $limit = $request->input('limit', 10);
        $search = $request->search;

        $transaction = Transaction::query();

        if($request->payment_status) {
            if($request->payment_status == 'paid') {
                $transaction->whereNotNull('paid_off_time');
            } else if($request->payment_status == 'not_paid') {
                $transaction->whereNull('paid_off_time');
            }
        }

        if($request->invoice_number) {
            $transaction->where('invoice_number', $request->invoice_number);
        }

        if($request->user_id) {
            $transaction->where('user_id', $request->user_id);
        }

        if($request->status) {
            $transaction->where('status', $request->status);
        }

        if($request->from_date) 
        {
            $transaction->where(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '>=', $request->from_date);
        }

        if($request->till_date) 
        {
            $transaction->where(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"), '<=', $request->till_date);
        }

        if($request->except_pending_status) {
            $transaction->where('status', '!=', 'pending');
        }

        if($search) {
            $transaction->whereHas('transaction_product', function($query) use ($search) {
                $query->where('product_name', 'like', '%'.$search.'%');
            });
        }
        
        $transaction->orderBy('created_at', 'desc');
        $result = ($request->limit_page == 1) ? $transaction->paginate($limit) : $transaction->get();
        return ResponseFormatter::success(TransactionResource::collection($result)->response()->getData(true), 'success get transaction data');
    }

    public function search(Request $request)
    {
        $request->validate([
            'search' => ['required', 'string'],
            'user_id' => ['nullable', 'exists:users,id'],
            'limit' => ['nullable', 'integer']
        ]);
        $limit = $request->input('limit', 10);

        $transaction = Transaction::joinProduct()->where('product_name', 'like', '%'.$request->search.'%');
        if($request->user_id) {
            $transaction->where('user_id', $request->user_id);
        }
        
        $transaction_ids = $transaction->groupBy('id')->pluck('id')->toArray();
        $new_transaction = Transaction::whereIn('id', $transaction_ids)->limit($limit)->get();
        return ResponseFormatter::success(TransactionResource::collection($new_transaction), 'success search transaction');
    }

    public function show(Transaction $transaction)
    {
        return ResponseFormatter::success(new TransactionDetailResource($transaction));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'type' => ['required', 'in:marketplace,store'],
            'marketplace_resi' => [
                Rule::requiredIf($request->type == 'marketplace'),
                'file'
            ],
            'transaction.*.payment_method' => ['required', 'in:cod,transfer,po'],
            'transaction.*.shipping_discount' => ['required', 'integer'],
            'transaction.*.shipping_cost' => ['required', 'integer'],
            'address' => ['required', 'string'],
            'transaction.*.expedition' => ['required', 'string'],
            'transaction.*.transaction_product' => ['required', 'array'],
            'transaction.*.transaction_product.*.product_slug' => [
                'required', 
                Rule::exists('product_combinations', 'product_slug')->where(function($query) {
                    return $query->where('status', 'active')->whereNull('deleted_at');
                })
            ],
            'transaction.*.transaction_product.*.image' => ['required', 'string'],
            'transaction.*.transaction_product.*.product_name' => ['required', 'string'],
            'transaction.*.transaction_product.*.discount_product' => ['required', 'integer'],
            'transaction.*.transaction_product.*.discount_group' => ['required', 'integer'],
            'transaction.*.transaction_product.*.discount_customer' => ['required', 'integer'],
            'transaction.*.transaction_product.*.price' => ['required', 'integer'],
            'transaction.*.transaction_product.*.new_price' => ['required', 'integer'],
            'transaction.*.transaction_product.*.description' => ['required', 'string'],
            'transaction.*.transaction_product.*.quantity' => ['required', 'integer'],
            'transaction.*.transaction_product.*.notes' => ['nullable', 'string'],
            'transaction.*.sub_total' => ['required', 'integer'],
            'total_price' => ['required', 'integer'],
        ]);

        foreach($request->transaction as $transaction_data2) {
            $payment_methods[] = $transaction_data2['payment_method'];
        }
        $bank_required = (in_array('transfer', $payment_methods) || in_array('po', $payment_methods)) ? 'required' : 'nullable'; 
        $request->validate([
            'bank_name' => [
                $bank_required, 
                'string'
            ],
            'no_rek' => [
                $bank_required, 
                'string'
            ],
            'payment_method_id' => [
                $bank_required, 
                'string'
            ],
        ]);
        

        $result = DB::transaction(function () use ($request, $payment_methods) {
            $new_request = $request->except(['marketplace_resi']);
            if($request->type == 'marketplace') {
                $marketplace_resi = FileHelpers::upload_file('resi', $request->marketplace_resi);
            }
    
            $expired_date = Carbon::now()->addHour(24);
            $unique_code = rand(1,env("MAX_UNIQUE_CODE"));
            $new_unique_code = $request->no_rek != 'escrow' ? $unique_code : 0;
            
            // create payment total table
            if(in_array('transfer', $payment_methods) || in_array('po', $payment_methods)) {
                $expired_time = $expired_date; 
                $status = 'process';
                $status_transaction = 'pending';
            } else {
                $expired_time = null; 
                $status = 'paid_off';
                $status_transaction = 'paid_off';
            }
            $input_payment = [
                'user_id' => $request->user_id,
                'unique_code' => $new_unique_code,
                'total' => $request->total_price + $new_unique_code,
                'expired_time' => $expired_time,
                'order_payment' => 0,
                'status' => $status,
            ];
            $all_payment = Payment::create($input_payment);
            // end create payment total table
    
            $mootapay_items = [];
            $total_all_shipping = 0;
            foreach($new_request['transaction'] as $transaction_data) {
                // create transaction table
                $new_shipping = $transaction_data['shipping_cost'] - $transaction_data['shipping_discount'];
                $total_all_shipping += $new_shipping;
    
                $input = $transaction_data;
                $input['user_id'] = $request->user_id;
                $input['type'] = $request->type;
                $input['address'] = $request->address;
                $input['bank_name'] = $request->bank_name;
                $input['no_rek'] = $request->no_rek;
                $input['invoice_number'] = Transaction::max('invoice_number') + 1;
                if($request->type == 'marketplace') {
                    $input['marketplace_resi'] = $marketplace_resi;
                }
                $input['status'] = $status_transaction;
                $total_payment = ($input['payment_method'] == 'po') ? 2 : 1;
                $input['total_payment'] = $total_payment;
                $transaction = Transaction::create($input);
                // end create transaction table
    
                // create transaction product table
                $transaction->transaction_product()->createMany($input['transaction_product']);
                // end create transaction product table
    
                // create payment table
                    $po_payment = env('PO_PAYMENT');
                    if($input['payment_method'] == 'po') {
                        $po1 = $input['sub_total'] * $po_payment / 100;
                        if($po_payment < 100) {
                            $input_payment = [
                                [
                                    'user_id' => $request->user_id,
                                    'parent_id' => $all_payment->id,
                                    'total' => $po1,
                                    'order_payment' => 1,
                                    'status' => $status,
                                ],
                                [
                                    'user_id' => $request->user_id,
                                    'parent_id' => $all_payment->id,
                                    'total' => $input['sub_total'] - $po1,
                                    'order_payment' => 2,
                                    'status' => 'pending',
                                ],
                            ];
                        } else {
                            $input_payment = [
                                [
                                    'user_id' => $request->user_id,
                                    'parent_id' => $all_payment->id,
                                    'total' => $po1,
                                    'order_payment' => 1,
                                    'status' => $status,
                                ],
                            ];
                        }
                    } else {
                        $input_payment = [
                            [
                                'user_id' => $request->user_id,
                                'parent_id' => $all_payment->id,
                                'total' => $request->total_price,
                                'expired_time' => ($input['payment_method'] == 'cod') ? null : $expired_time,
                                'order_payment' => 1,
                                'status' => $status,
                            ]
                        ];
                    }
                    $transaction->payments()->createMany($input_payment);
                // end create payment table
    
                // update stock after chechout
                foreach ($input['transaction_product'] as $transaction_product) {
                    $product_combination = ProductCombination::where('product_slug', $transaction_product['product_slug'])->first();
                    $product_combination->update([
                        'stock' => $product_combination->stock - $transaction_product['quantity']
                    ]);
                    $product_slugs[] = $transaction_product['product_slug'];
                }
                // end update stock after chechout
    
                // delete cart after success checkout
                Cart::where('user_id', $request->user_id)->whereIn('product_slug', $product_slugs)->delete();
                // end delete cart after success checkout
    
                // mootapay item
                if($input['payment_method'] != 'cod' ) {
                    foreach ($input['transaction_product'] as $transaction_product) {
                        $mootapay_items[] = [
                            'name' => $transaction_product['product_name'],
                            'qty' => $transaction_product['quantity'],
                            'price' => $transaction_product['new_price'],
                            'sku' => $transaction_product['product_slug'],
                            'image_url' => $transaction_product['image']
                        ];
                    }
                }
            }
            $mootapay_items[] = [
                'name' => 'Ongkos Kirim',
                'qty' => 1,
                'price' => $total_all_shipping,
                'sku' => 'ongkos-kirim',
                'image_url' => asset('assets/images/deliver.png'),
            ];
    
            // mootapay data
            if(in_array('transfer', $payment_methods) || in_array('po', $payment_methods)) {
                $customer = User::find($request->user_id);
                $mootapay_data = [
                    'invoice_number' => $all_payment->id,
                    'amount' => $request->total_price,
                    'payment_method_id' => $request->payment_method_id,
                    'expired_date' => $expired_time,
                    'customer' => [
                        'name' => $customer->name,
                        'email' => $customer->email,
                        'phone' => '0' . $customer->phone_number,
                    ],
                    'items' => $mootapay_items,
                    'unique_code' => $new_unique_code,
                ];
                
                $this->moota_transaction($mootapay_data, $all_payment);
            }
    
            return ResponseFormatter::success(new PaymentResource($all_payment), 'success create transaction data');
            
        });
        return $result;
    }

    public function update_status(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status' => ['required', 'in:pending,paid_off,expired,sent,canceled,finish']
        ]);
        if($request->status == 'paid_off') {
            $transaction->update([ 
                'status' => $request->status,
                'paid_off_time' => Carbon::now(),
            ]);
        } else {
            $transaction->update([ 'status' => $request->status ]);
        }
        return ResponseFormatter::success(new TransactionDetailResource($transaction), 'success update status transaction data');
    }

    public function update_resi(Request $request, Transaction $transaction)
    {
        $request->validate([
            'number_resi' => ['required', 'string']
        ]);
        $transaction->update(['number_resi' => $request->number_resi]);
        return ResponseFormatter::success(new TransactionDetailResource($transaction), 'success update number resi transaction data');
    }

    public function handle_mootapay(Request $request) {
        $secret = MootapayRepository::getToken();
        $mootapay_signature = $request->header('Signature');
        $data = $request->json()->all();
        $data2 = $request->getContent();
        $signature = hash_hmac('sha256', $data2, $secret);

        Log::info($data);
        Log::info($signature);
        Log::info($mootapay_signature);

        if($signature == $mootapay_signature) {
            if($data) {
                // get payment
                $payment = Payment::where([
                    ['status', 'process'], 
                    ['expired_time', '>=' , Carbon::now()]
                ])
                ->whereJsonContains('mootapay_response->data->trx_id', $data['trx_id'])
                ->whereNotNull('unique_code')->first();
    
                if(!empty($payment)) {
                    $result = DB::transaction(function () use ($payment) {
                        // update payment status from moota
                        $payment->update([
                            'status' => 'paid_off',
                            'paid_off_time' => Carbon::now()
                        ]);
                        if($payment->order_payment == 0) {
                            Payment::where([['parent_id', $payment->id], ['order_payment', 1]])->update([
                                'status' => 'paid_off',
                                'paid_off_time' => Carbon::now()
                            ]);
                            $transaction_ids = Payment::where('parent_id', $payment->id)->distinct()->pluck('transaction_id')->toArray();
                        } else {
                            $transaction_ids = [$payment->transaction_id];
                        }
    
                        // cek payment if all paid off
                        foreach($transaction_ids as $transaction_id) {
                            $cek_payment = Payment::where([
                                ['transaction_id', $transaction_id],
                                ['status', '!=', 'paid_off']
                            ])->count();
        
                            if($cek_payment == 0) {
                                Transaction::find($transaction_id)->update([
                                    'status' => 'paid_off',
                                    'paid_off_time' => Carbon::now()
                                ]);
                            }
                        }
                        // end cek payment if all paid of
    
                        Log::notice("success update status payment data");
                        return ResponseFormatter::success(null, "success update status payment data");
                    });
                    return $result;
                } else {
                    Log::error("failed update status payment data");
                    return ResponseFormatter::error([
                        'message' => 'failed update status payment data'
                    ], 'update payment failed', 422);
                }
            }
        } else {
            Log::error('signature is invalid');
            return ResponseFormatter::error([
                'message' => 'signature is invalid'
            ], 'update payment failed', 422);
        }
    }

    public function notification(Request $request)
    {
        $request->validate([
            'user_id' => ['nullable', 'exists:users,id']
        ]);

        $query_pe = Transaction::query();
        $query_pa = Transaction::query();
        // $query_ex = Transaction::query();
        $query_se = Transaction::query();
        // $query_ca = Transaction::query();
        // $query_fi = Transaction::query();

        if($request->user_id) {
            $query_pe->where('user_id', $request->user_id);
            $query_pa->where('user_id', $request->user_id);
            // $query_ex->where('user_id', $request->user_id);
            $query_se->where('user_id', $request->user_id);
            // $query_ca->where('user_id', $request->user_id);
            // $query_fi->where('user_id', $request->user_id);
        }
        $result = [
            'pending' => $query_pe->where('status', 'pending')->count(),
            'paid_off' => $query_pa->where('status', 'paid_off')->count(),
            // 'expired' => $query_ex->where('status', 'expired')->count(),
            'sent' => $query_se->where('status', 'sent')->count(),
            // 'canceled' => $query_ca->where('status', 'canceled')->count(),
            // 'finish' => $query_fi->where('status', 'finish')->count(),
        ];
        return ResponseFormatter::success($result, 'success get notification data');
    }

    public function moota_transaction($data, Payment $payment) 
    {
        $input = [
            'invoice_number' => $data['invoice_number'],
            'merchant_id' => env("MOOTAPAY_MERCHANT_ID"),
            'amount' => $data['amount'],
            'payment_method_id' => $data['payment_method_id'],
            'type' => 'payment',
            'callback_url' => url('') . '/api/transaction/handle_mootapay',
            // 'expired_date' => Carbon::parse($data['expired_date'])->format('Y-m-d H:i:00'),
            'customer' => [
                'name' => $data['customer']['name'],
                'email' => $data['customer']['email'],
                'phone' => $data['customer']['phone'],
            ],
            'items' => $data['items'],
            'unique_code' => $data['unique_code'],
        ];

        $response = MootapayRepository::transaction($input);

        if($response->successful()) {
            $payment->update([
                'mootapay_response' => json_decode($response),
                'expired_time' => Carbon::parse($response['data']['expired_date'])
            ]);
        } else {
            $response->throw();
        }
    }
}
