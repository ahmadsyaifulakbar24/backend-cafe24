<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $fillable = [
        'user_id',
        'transaction_id',
        'parent_id',
        'unique_code',
        'total',
        'expired_time',
        'paid_off_time',
        'order_payment',
        'status',
        'snap_token',
        'midtrans_notification',
    ];

    protected $casts = [
        'midtrans_notification' => 'array',
    ];

    public function getCreatedAtAttribute($date) {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }

    public function getUpdatedAtAttribute($date) {
        return Carbon::parse($date)->format('Y-m-d H:i:s');
    }
    
    public function user() {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }
    
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function evidence()
    {
        return $this->hasOne(Evidence::class, 'payment_id');
    }

    public function child() 
    {
        return $this->hasMany(Payment::class, 'parent_id');
    }
}
