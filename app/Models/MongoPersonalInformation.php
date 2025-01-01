<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class MongoPersonalInformation extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'personal_informations';
    
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'date_of_birth',
        'state',
        'city',
        'country',
        'image'
    ];

    protected $dates = ['date_of_birth'];
}