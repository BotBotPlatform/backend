<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
  public function bot() {
    return $this->belongsTo('App\Models\Bot');
  }
}
