<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $primaryKey = 'id_order';
    protected $fillable = [
        'id_order', 'id_user', 'id_konselor', 'nama_layanan', 
        'voucher', 'total', 'payment_status', 'bukti_transfer'
    ];
}
