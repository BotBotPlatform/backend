<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Bot;
use Carbon\Carbon;
use Auth;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Cache;

class ShopController extends Controller
{
  public function getShopByUrl($shop) {
    $provider = "etsy";
    if(Cache::store('file')->has($shop)) {
      return Cache::store('file')->get($shop);
    }

    $client = new Client();
    // TODO transformations upon request items tthen pass to Goutte
    $crawler = $client->request('GET', "https://" . $provider . ".com/shop/" . $shop);
    $nodeValues = $crawler->filter('.js-merch-stash-check-listing')->each(function (Crawler $node, $i) {
        $img = $node->filter('.placeholder-content')->children()->eq(0)->attr('src');
        $title = trim($node->filter('.card-title')->text());
        $price = trim($node->filter('.currency')->text());
        $url = $node->filter('a')->attr('href');
        return ['img' => $img, 'title' => $title, 'price' => $price, 'url' => $url];
    });
    Cache::store('file')->put($shop, $nodeValues, Carbon::now()->addHours(1));
    return $nodeValues;    
  }

  public function getShop(Request $request) {
    if(empty(Auth::user()->bot->shop)) {
      return ['message' => 'No shop configured'];
    }
    return $this->getShopByUrl(Auth::user()->bot->shop);
  }

  public function setShopUrl(Request $request) {
      $validator = Validator::make($request->all(), [
          'shop' => 'required',
      ]);
      if ($validator->fails()) {
          return ['message' => 'validation', 'errors' => $validator->errors()];
      }

      //Does this user have a bot?
      if(count(Auth::user()->bot) <= 0) {
        return response()->json(['message' => 'no_bot_exists'],400);
      }

      $bot = Auth::user()->bot;
      $bot->shop = $request->shop;
      $bot->save();
      return ['message' => 'success', 'shop' => $bot->shop];
  }

  public function getShopName() {
    if(Auth::user()->bot->shop) {
      return ['message' => 'success', 'shop' => Auth::user()->bot->shop];
    }
    return ['message' => 'No shop configured'];
  }

}