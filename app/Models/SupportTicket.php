<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
  protected $table = 'tickets';


  public function bot() {
    return $this->belongsTo('App\Models\Bot');
  }
}
