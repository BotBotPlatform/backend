<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Http\Controllers\BotController;

class Bot extends Model
{
  use SoftDeletes;

  public function User() {
    return $this->belongsTo('App\Models\User');
  }

  //Returns the pm2 data which corresponds to this bot, or null if there is no active process
  public function getData() {
    $processData = BotController::dumpProcessData();
    if(array_key_exists($this->uuid,$processData)) {
      return $processData[$this->uuid];
    } else {
      return null; //There is no pm2 process
    }
  }
}
