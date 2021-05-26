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
        'fund_category',
        'location',
        'sector',
        'range_fund',
        'range_year',
        'range_employees',
        'range_income',
        'burn_rate',
    ];

    protected $hidden = [
        'id',
        'id_user',
        'created_at',
        'updated_at',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
