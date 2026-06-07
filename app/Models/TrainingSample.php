<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'sentiment',
        'source',
    ];
}
