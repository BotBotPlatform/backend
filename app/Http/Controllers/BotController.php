<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Bot;
use Uuid;
use Auth;
use App\Jobs\SpinUpBot;
use App\Jobs\ShutDownBot;
use App\Jobs\ReloadBot;

class BotController extends Controller
{
  /**
   * Create a bot
 */
  public function createBot(Request $request) {
      $validator = Validator::make($request->all(), [
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user already have a bot?
      if(count(Auth::user()->bot) > 0) {
        return response()->json(['message' => 'bot_already_exists'],400);
      }

      $bot = new Bot;
      $bot->uuid = Uuid::generate()->string;
      $bot->user_id = Auth::id();
      $bot->save();
      return ['message' => 'success', 'bot' => $bot];
  }

  public function getBot(Request $request) {
      $validator = Validator::make($request->all(), [
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      $bot = Auth::user()->bot;
      return ['message' => 'success', 'bot' => $bot];
  }

  public function deleteBot(Request $request) {
      $validator = Validator::make($request->all(), [
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      $bot = Auth::user()->bot;
      if(count($bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }
      $bot->delete();
      return ['message' => 'success'];
  }

  public function spinUpBot(Request $request) {
    $this->dispatch(new SpinUpBot(Auth::user()));
    return ['message' => 'success'];
  }

  public function shutDownBot(Request $request) {
    $this->dispatch(new ShutDownBot(Auth::user()));
    return ['message' => 'success'];
  }

  public function reloadBot(Request $request) {
    $this->dispatch(new ReloadBot(Auth::user()));
    return ['message' => 'success'];
  }


  public static function getFreePort() {
    while(true) {
      $tempPort = rand(8050,9000);
      $bot = Bot::where('port',$tempPort)->first();
      if(count($bot) > 0) {
        //This port is already taken
      } else {
        return $tempPort;
      }
    }

  }
}
