<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transfer_many extends Model
{
    use HasFactory;

    public $table='transfer_manies';

    public $fillable=['money','Visa_number','status','transaction_id','user_id','admin_id','transaction_id_binance','ip_user','otp','check_otp','type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
