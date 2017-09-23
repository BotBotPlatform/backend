<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
  public function User() {
    return $this->belongsTo('App\Models\User');
  }
}
