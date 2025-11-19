<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Item;
use App\Models\Type;
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

        $descriptions = collect($response['descriptions'])->unique('classid');

        $storedItemIds = Item::all()
            ->pluck('classid');
        $storedTypes = Type::all();

        $items = [];
        foreach ($descriptions as $description) {
            if (!$storedItemIds->has($description['classid'])) {
                $itemType = collect($description['tags'])->firstWhere('category', 'Type');

                $storedType = $storedTypes->firstWhere('internal_name', $itemType['internal_name']);

                if ($storedType === null && !$storedTypes->has($itemType['internal_name'])) {
                    $storedType = Type::query()
                    ->create([
                        'internal_name' => $itemType['internal_name'],
                        'name' => $itemType['localized_tag_name']
                    ]);
                    $storedTypes->push($storedType);
                }

                $items[] = [
                    'classid' => $description['classid'],
                    'name' => $description['name'],
                    'market_name' => $description['market_name'],
                    'name_color' => $description['name_color'],
                    'icon_url' => $description['icon_url'],
                    'type_id' => $storedType->id,
                ];
            }
        }
        Item::query()->insert($items);

        $userStoredAssetsIds = Asset::query()
            ->where('user_id', auth()->user()->id)
            ->pluck('id')
            ->toArray();
        $userInventoryAssetIds = array_column($response['assets'], 'assetid');

        //assets that are not in the user inventory anymore
        $assetsToDelete = array_diff($userStoredAssetsIds, $userInventoryAssetIds);
        Asset::destroy($assetsToDelete);

        $newAssets = array_diff($userInventoryAssetIds, $userStoredAssetsIds);

        $assets = [];
        foreach ($response['assets'] as $asset) {
            if (in_array($asset['assetid'], $newAssets)) {
                $assets[] = [
                    'id' => $asset['assetid'],
                    'classid' => $asset['classid'],
                    'user_id' => auth()->user()->id,
                ];
            }
        }
        Asset::query()->insert($assets);

        $assets = Asset::query()
            ->with('item')
            ->where('user_id', auth()->user()->id)
            ->get();

        return view('test', compact('assets'));
    }
}
