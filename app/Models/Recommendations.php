<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendations extends Model
{
    use HasFactory;
    protected $table = 'recommendations';

    protected $fillable = [
        'id_user',
        'id_recommended'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }

    public function rekomendasi()
    {
        return $this->belongsTo(User::class, 'id_recommended', 'id');
    }
}
