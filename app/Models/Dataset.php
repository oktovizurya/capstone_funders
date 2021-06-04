<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;

class Dataset extends Model
{
    use HasFactory;
    protected $table = 'dataset';

    protected $fillable = [
        'id_user',
        'id_lokasi',
        'id_kategori',
        'id_range_funds',
        'id_range_employees'
    ];

    protected $hidden = [
        'id',
        'id_user',
        'created_at',
        'updated_at',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    
    public function lokasi()
    {
        return $this->belongsTo(Provinsi::class, 'id_lokasi', 'id');
    }
    
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id');
    }
    
    public function range_fund()
    {
        return $this->belongsTo(RangeFunds::class, 'id_range_funds', 'id');
    }
    
    public function range_employee()
    {
        return $this->belongsTo(RangeEmployees::class, 'id_range_employees', 'id');
    }
}
