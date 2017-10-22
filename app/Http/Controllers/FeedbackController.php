<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use App\Models\FeedbackCategory;
use App\Models\Feedback;

class FeedbackController extends Controller
{
  /**
   * Create a bot
 */
  public function createFeedbackCategory(Request $request) {
      $validator = Validator::make($request->all(), [
          'name' => 'required|unique:feedback_category,name',
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $category = new FeedbackCategory;
      $category->bot_id = Auth::user()->bot->id;
      $category->name = $request->name;
      $category->save();
      return ['message' => 'success', 'category' => $category];
  }

  public function getFeedbackCategories(Request $request) {
      $validator = Validator::make($request->all(), [
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $categories = Auth::user()->bot->feedbackCategories;
      return ['message' => 'success', 'categories' => $categories];
  }

  public function createFeedback(Request $request) {
      $validator = Validator::make($request->all(), [
        'category_id' => 'required|exists:feedback_category,id',
        'message' => 'required|max:2000',
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      $feedback = new Feedback;
      $feedback->feedback_category_id = $request->category_id;
      $feedback->message = $request->message;
      $feedback->save();
      return ['message' => 'success'];
  }

  public function getFeedback(Request $request) {
      $validator = Validator::make($request->all(), [
        'category_id' => 'exists:feedback_category,id',
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      //Filter by category id if provided
      if($request->category_id) {
        $feedback = Auth::user()->bot->feedbackCategories()->where('id',$request->category_id)->with('feedback')->get();
      } else {
        $feedback = Auth::user()->bot->feedbackCategories()->with('feedback')->get();
      }

      return ['message' => 'success', 'feedback' => $feedback];
  }
}
