<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class DetailUser extends Model
{
    use HasFactory;
    protected $table = 'detail_users';
    protected $primaryKey = 'id_detail';
    
    protected $fillable = [
        'id_user',
        'nama',
        'nik',
        'tgl_lahir',
        'alamat',
        'no_tlp',
        'status_online',
        'jenis_kelamin',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
