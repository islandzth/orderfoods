<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WaiterFood extends Model
{

    public function food() {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function board() {
        return $this->belongsTo(Board::class, 'board_id');
    }
}
