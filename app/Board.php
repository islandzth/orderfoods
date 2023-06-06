<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    const STATUS = [
        'BUSY' => 1,
        'CHECKOUT' => 2,
    ];
}
