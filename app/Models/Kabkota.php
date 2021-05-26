<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use\App\Models\Provinsi;

class Kabkota extends Model
{
    use HasFactory;
    protected $table = 'kabkota';

    protected $fillable = [
        'id_provinsi',
        'kabkota',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'id_provinsi', 'id');
    }
}
