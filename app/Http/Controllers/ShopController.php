<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use App\Models\Bot;
use Carbon\Carbon;
use Auth;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
class ShopController extends Controller
{
  public function getShopByUrl($provider, $shop) {
    $client = new Client();
    // TODO transformations upon request items tthen pass to Goutte
    $crawler = $client->request('GET', "https://" . $provider . ".com/shop/" . $shop);
    $nodeValues = $crawler->filter('.js-merch-stash-check-listing')->each(function (Crawler $node, $i) {
        $img = $node->filter('.placeholder-content')->children()->eq(0)->attr('src');
        $title = trim($node->filter('.card-title')->text());
        $price = trim($node->filter('.currency')->text());
        return ['img' => $img, 'title' => $title, 'price' => $price];
    });
    return $nodeValues;    
  }

  public function getShop(Request $request) {
    return $this->getShopByUrl($request->provider, $request->shop);
  }
}