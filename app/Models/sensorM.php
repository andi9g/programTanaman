<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sensorM extends Model
{
    use HasFactory;
    protected $table = 'sensor';
    protected $primaryKey = 'idsensor';
    protected $guarded = [];
}
