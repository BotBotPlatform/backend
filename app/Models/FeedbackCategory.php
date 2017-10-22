<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackCategory extends Model
{
  protected $table = 'feedback_category';

  public function bot() {
    return $this->belongsTo('App\Models\Bot');
  }

  public function feedback() {
    return $this->hasMany('App\Models\Feedback');
  }
}
