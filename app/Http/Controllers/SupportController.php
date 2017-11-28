<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupportTicket;
use Validator;
use Auth;

class SupportController extends Controller
{
  public function createSupportTicket(Request $request) {
      $validator = Validator::make($request->all(), [
        'message' => 'required|'
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }
      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      //Is this timestamp during business hours?
      $bot = Auth::user()->bot;
      $ticket = new SupportTicket;
      $ticket->message = $request->message;
      $ticket->bot_id = Auth::user()->bot->id;
      $ticket->save();
      return ['message' => 'success', 'ticket' => $ticket];
  }

  public function getSupportTickets(Request $request) {
      $validator = Validator::make($request->all(), [
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $tickets  = Auth::user()->bot->tickets;
      return ['message' => 'success', 'tickets' => $tickets];
  }

  public function resolveSupportTicket(Request $request, $ticket_id) {
    $validator = Validator::make($request->all(), [
    ]);
    if ($validator->fails()) {
        return ['message' => 'validation', 'errors' => $validator->errors()];
    }

    $ticket = SupportTicket::where('id',$ticket_id)->first();
    if(count($ticket) < 0) {
      return response()->json(['message' => 'invalid_ticket'],404);
    }
    $ticket->resolved = true;
    $ticket->save();
    return ['message' => 'success', 'ticket' => $ticket];
  }
}
