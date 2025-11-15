<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InventoryController extends Controller
{
    public function index()
    {
        $steamId64 = auth()->user()->steamid64;
        $url = "https://steamcommunity.com/inventory/{$steamId64}/730/2?l=english&count=2000";

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Referer' => "https://steamcommunity.com/profiles/{$steamId64}/inventory",
        ])->get($url)->json();

//        dd($response);

        $items = [];
        foreach ($response['assets'] as $asset) {
            foreach ($response['descriptions'] as $description) {
                if ($description['classid'] === $asset['classid']) {
                    $items[$asset['assetid']] = [
                        'classid' => $asset['classid'],
                        'icon_url' => $description['icon_url'],
                        'name' => $description['market_name'],
                    ];
                }
            }
        }

        ksort($items);

        return view('test', compact('items'));
    }
}
