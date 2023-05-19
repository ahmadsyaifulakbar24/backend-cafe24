<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivationUserToken extends Model
{
    use HasFactory;

    protected $table = 'activation_user_tokens';
    protected $fillable = [
        'user_id',
        'token'
    ];
    public $timestamps = false;

    public function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('Y-m-d H:i:s'),
        );
    }
}
