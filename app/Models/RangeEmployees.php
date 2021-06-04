<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RangeEmployees extends Model
{
    use HasFactory;
    protected $table = 'range_employees';

    protected $fillable = [
        'range_employee'
    ];
}
