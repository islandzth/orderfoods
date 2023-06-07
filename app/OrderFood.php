<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderFood extends Model
{
    const NOT_CHECKOUT = 1;
    const DID_CHECKOUT = 2;


    public function food() {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function board() {
        return $this->belongsTo(Board::class, 'board_id');
    }
}
