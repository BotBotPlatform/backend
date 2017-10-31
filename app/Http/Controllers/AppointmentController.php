<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Appointment;
use App\Models\Bot;
use Carbon\Carbon;
use Auth;

class AppointmentController extends Controller
{
  public function createAppointment(Request $request) {
      $validator = Validator::make($request->all(), [
        'timestamp' => 'required|date_format:m/d/Y:Gi'
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      $carbonTimestamp = Carbon::createFromFormat('m/d/Y:Gi',$request->timestamp);
      $appointment = new Appointment;
      $appointment->bot_id = Auth::user()->bot->id;
      $appointment->timestamp = $carbonTimestamp;
      $appointment->save();
      return ['message' => 'success', 'appointment' => $appointment];
  }

  public function getAppointments(Request $request) {
      $validator = Validator::make($request->all(), [
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $appointments  = Auth::user()->bot->appointments()->get();
      return ['message' => 'success', 'appointments' => $appointments];
  }
}
