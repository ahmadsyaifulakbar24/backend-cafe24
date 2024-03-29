<?php

namespace App\Http\Resources\Transaction;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if($this->transaction_id == null) {
            $transaction_id = Payment::where('parent_id', $this->id)->first()->transaction_id;
            $transaction_ids = $this->child()->pluck('transaction_id')->toArray();
        } else {
            $transaction_id = $this->transaction_id;
            $transaction_ids = Payment::where('id', $this->id)->pluck('transaction_id')->toArray();
        }
        $transaction_product = Transaction::whereIn('id', $transaction_ids)->get();
        $transaction_with_product = TransactionResource::collection($transaction_product);

        $transaction = Transaction::find($transaction_id);
        return [
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ],
            'bank_name' => $transaction->bank_name,
            'no_rek' => $transaction->no_rek,
            'transaction_id' => $this->transaction_id,
            'unique_code' => $this->unique_code,
            'total' => intval($this->total),
            'expired_time' => $this->expired_time,
            'paid_off_time' => $this->paid_off_time,
            'order_payment' => $this->order_payment,
            'status' => $this->status,
            'evidence' => new EvidenceResource($this->evidence),
            'transaction_with_product' => $transaction_with_product,
            'snap_token' => $this->snap_token,
            'midtrans_notification' => $this->midtrans_notification,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
