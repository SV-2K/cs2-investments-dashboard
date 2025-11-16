<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemType;
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

        //removing duplicates
        $descriptions = [];
        $seen = [];
        foreach ($response['descriptions'] as $description) {
            if (!in_array($description['classid'], $seen)) {
                $descriptions[] = $description;
                $seen[] = $description['classid'];
            }
        }

        $storedTypeIds = ItemType::all()
            ->pluck('classid')
            ->toArray();

        $itemTypes = [];
        foreach ($descriptions as $description) {
            if (!in_array($description['classid'], $storedTypeIds)) {
                $itemTypes[] = [
                    'classid' => $description['classid'],
                    'name' => $description['name'],
                    'market_name' => $description['market_name'],
                    'name_color' => $description['name_color'],
                    'icon_url' => $description['icon_url'],
                ];
            }
        }
        ItemType::query()->insert($itemTypes);

        $userStoredItemIds = Item::query()
            ->where('user_id', auth()->user()->id)
            ->pluck('classid')
            ->toArray();
        $userInventoryItemIds = array_column($response['assets'], 'classid');

        //items that are not in the user inventory anymore
        $itemsToDelete = array_diff($userStoredItemIds, $userInventoryItemIds);
        Item::destroy($itemsToDelete);

        $newItems = array_diff($userInventoryItemIds, $userStoredItemIds);
        $items = [];
        foreach ($response['assets'] as $asset) {
            if (!in_array($asset['classid'], $newItems) && !in_array($asset['classid'], $userStoredItemIds)) {
                $items[] = [
                    'classid' => $asset['classid'],
                    'assetid' => $asset['assetid'],
                    'user_id' => auth()->user()->id,
                ];
            }
        }
        Item::query()->insert($items);


        $items = Item::query()
            ->where('user_id', auth()->user()->id)
            ->get();

        return view('test', compact('items'));
    }
}
