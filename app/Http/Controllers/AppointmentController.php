<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Appointment;
use App\Models\Bot;
use Carbon\Carbon;
use Auth;
use \Eluceo\iCal\Component\Calendar;

class AppointmentController extends Controller
{
  public function createAppointment(Request $request) {
      $validator = Validator::make($request->all(), [
        'timestamp' => 'required|date_format:m/d/Y:H:i'
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }
      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $carbonTimestamp = Carbon::createFromFormat('m/d/Y:H:i',$request->timestamp);

      //Is this timestamp during business hours?
      $bot = Auth::user()->bot;
      $start = $bot->business_hours_min."00";
      $end   = $bot->business_hours_max."00";
      $time  = $carbonTimestamp->format('Hi');

      if (!($time > $start && $time < $end)) {
        $validator->errors()->add('timestamp', 'timestamp must be within business hours');
        return response()->json(['message' => 'validation', 'errors' => $validator->errors()],400);
      }

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

  public function getBusinessHours(Request $request) {
      $validator = Validator::make($request->all(), [
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $bot = Auth::user()->bot;
      return ['message' => 'success', 'min_hour' => $bot->business_hours_min,  'max_hour' => $bot->business_hours_max];
  }

  public function setBusinessHours(Request $request) {
      $validator = Validator::make($request->all(), [
        'min_hour' => 'required|integer|min:0|max:24',
        'max_hour' => 'required|integer|min:0|max:24'
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $bot = Auth::user()->bot;
      $bot->business_hours_min = $request->min_hour;
      $bot->business_hours_max = $request->max_hour;
      $bot->save();
      return ['message' => 'success', 'min_hour' => $bot->business_hours_min,  'max_hour' => $bot->business_hours_max];
  }

  public function generateCalendarForBot(Request $request) {
      $bot = Bot::where('uuid', '=', $request->uuid)->first();
      if(count($bot) <= 0) {
        return "invalid url";
      }

      $vCalendar = new Calendar('www.example.com');
      $appointments = $bot->appointments;

      // Iterate through all sections
      foreach($appointments as $appointment) {
          // Iterate through all events
          $vEvent = new \Eluceo\iCal\Component\Event();
          $startTime = new Carbon($appointment->timestamp);
          $endTime = new Carbon($appointment->timestamp);
          $endTime->addHours(1);
          $vEvent
              ->setDtStart(new \DateTime($startTime))
              ->setDtEnd(new \DateTime($endTime))
              ->setNoTime(false)
              ->setSummary("BotBot Event");
          $vCalendar->addComponent($vEvent);
      }
      // Headers that might not actually do anything
      header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' ); //date in the past
      header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' ); //tell it we just updated
      header( 'Cache-Control: no-store, no-cache, must-revalidate' ); //force revaidation
      header( 'Cache-Control: post-check=0, pre-check=0', false );
      header( 'Pragma: no-cache' );
      header('Content-Type: text/calendar; charset=utf-8');
      header('Content-Disposition: attachment; filename="cal.ics"');
      echo $vCalendar->render();
  }
}
