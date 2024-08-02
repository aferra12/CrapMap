<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bathroom extends Model
{
    use HasFactory;

    protected $fillable=['name', 'location', 'code', 'note', 'latitude', 'longitude'];
}
