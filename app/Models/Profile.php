<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Kabkota;
use App\Models\User;

class Profile extends Model
{
    use HasFactory;
    protected $table = 'profile';

    protected $fillable = [
        'id_user',
        'id_kabkota',
        'no_telp',
        'gambar',
        'alamat',
        'deskripsi'
    ];

    public function user(){
        return $this->belongsTo(Profile::class, 'id_user', 'id');
    }

    public function kabkota(){
        return $this->belongsTo(Kabkota::class, 'id_kabkota', 'id');
    }
}
