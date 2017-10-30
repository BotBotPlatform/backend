<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Bot;
use App\Models\User;
use Uuid;
use Auth;
use App\Jobs\SpinUpBot;
use App\Jobs\ShutDownBot;
use App\Jobs\ReloadBot;
use \GuzzleHttp\Client;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use File;

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

  /**
   * Create a bot
  */
  public function toggleBotFeatures(Request $request) {
      $validator = Validator::make($request->all(), [
        'feature_name' => 'required|in:bot_enabled,shopify_enabled,reservations_enabled,feedback_enabled,customer_support_enabled',
        'enabled' => 'required|boolean'
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user already have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $bot = Auth::user()->bot;
      $bot[$request->feature_name] = $request->enabled;
      $bot->save();
      return ['message' => 'success', 'bot' => $bot];
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

  public function authenticateBot(Request $request) {
    //Make sure the verification token matches
    $bot = Bot::where('uuid',$request->uuid)->first();
    if($request->hub_mode && $request->hub_verify_token) {
      if($request->hub_verify_token == $bot->user->verification_token) {
        //Send back the challenge
        return response($request->hub_challenge);
      } else {
        //This does not match ruh roh
        return response("invalid_auth",403);
      }
    } else {
      return response("invalid_request",400);
    }
  }

  public function forwardBotMessage(Request $request) {
    //Figure out the bot from the request
    $bot = Bot::where('uuid',$request->uuid)->first();
    if(count($bot) <= 0) {
      return response()->json(['message' => 'no_bot_exists'], 400);
    }
    if($bot->port == null || $bot->deploy_status !== "alive") {
      return response()->json(['message' => 'bot_offline'], 400);
    }
    //Forward this request to a local node instance
    $client = new Client();
    $res = $client->request('POST', 'localhost:'.$bot->port, [
        'json' => [
          'object' => $request->object,
          'entry' => $request->entry,
        ]
    ]);
    return $res->getBody();
  }

  public static function dumpProcessData() {
    $listCommand = "pm2 jlist";
    $process = new Process($listCommand);
    $process->run();
    if (!$process->isSuccessful()) {
      return response()->json(['message' => 'pm2_error'], 500);
    }
    $botInformation = json_decode($process->getOutput());
    $output = [];
    foreach($botInformation as $bot) {
      $output[$bot->name] = [
        'name' => $bot->name,
        'uptime' => $bot->pm2_env->pm_uptime,
        'status' => $bot->pm2_env->status,
        'creation_time' => $bot->pm2_env->created_at,
        'crash_count' => $bot->pm2_env->unstable_restarts,
        'restart_count' => $bot->pm2_env->restart_time,
        'output_log_path' => $bot->pm2_env->pm_out_log_path,
        'error_log_path' => $bot->pm2_env->pm_err_log_path,
        'memory_usage' => $bot->monit->memory,
        'cpu_usage' => $bot->monit->cpu,
      ];
    }
    return $output;
  }

  public function getBotData(Request $request) {
    //Check admin permissions
    if(!PermissionsController::hasRole('admin')) {
      return response()->json(['message' => 'insufficient_permissions'], 403);
    }
    $procData = BotController::dumpProcessData();
    $bots = Bot::with('user')->get();
    $output = [];
    foreach($bots as $bot) {
      // array_push($output,get_object_vars($bot));
      if(array_key_exists($bot->uuid,$procData)) {
        $bot['process_data'] = $procData[$bot->uuid];
      } else {
        $bot['process_data'] = null;
      }
    }
    return $bots;

  }

  public function getBotOutputLog(Request $request, $bot_uuid) {
    //Check admin permissions
    if(!PermissionsController::hasRole('admin')) {
      return response()->json(['message' => 'insufficient_permissions'], 403);
    }
    $bot = Bot::where('uuid',$bot_uuid)->first();
    if(count($bot) <= 0) {
      return response()->json(['message' => 'invalid_id'], 404);
    }

    $botData = $bot->getData();
    if(!$botData) {
      return response()->json(['message' => 'no_data'], 400);
    }
    $fileContents = File::get($botData['output_log_path']);
    return $fileContents;
  }

  public function getBotErrorLog(Request $request, $bot_uuid) {
    //Check admin permissions
    if(!PermissionsController::hasRole('admin')) {
      return response()->json(['message' => 'insufficient_permissions'], 403);
    }
    $bot = Bot::where('uuid',$bot_uuid)->first();
    if(count($bot) <= 0) {
      return response()->json(['message' => 'invalid_id'], 404);
    }

    $botData = $bot->getData();
    if(!$botData) {
      return response()->json(['message' => 'no_data'], 400);
    }
    $fileContents = File::get($botData['error_log_path']);
    return $fileContents;
  }
}
