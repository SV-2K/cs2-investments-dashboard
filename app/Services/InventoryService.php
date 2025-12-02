<?php

namespace App\Services;

use App\Helpers\DateHelpers;
use App\Models\Asset;
use App\Models\Item;
use App\Models\Price;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class InventoryService
{
    public function updateUserInventory(User $user): void
    {
        $nextAllowedTimeToUpdate = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $user->last_inventory_update,
        )->addMinutes(10);

        if ($nextAllowedTimeToUpdate->isFuture()) {
            return;
        }


        $steamId64 = $user->steamid64;
        $url = "https://steamcommunity.com/inventory/{$steamId64}/730/2?l=english&count=2000";

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Referer' => "https://steamcommunity.com/profiles/{$steamId64}/inventory",
        ])->get($url)->json();

        $storedItemIds = Item::all()
            ->pluck('classid');
        $storedTypes = Type::all();

        $items = [];
        $descriptions = collect($response['descriptions']);
        foreach ($descriptions as $description) {
            if (!$storedItemIds->contains($description['classid'])) {

                $itemType = collect($description['tags'])->firstWhere('category', 'Type');
                $storedType = $storedTypes->firstWhere('internal_name', $itemType['internal_name']);

                if ($storedType === null) {
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
                    'marketable' => $description['marketable'],
                    'name_color' => $description['name_color'],
                    'icon_url' => $description['icon_url'],
                    'type_id' => $storedType->id,
                ];
                $storedItemIds->push($description['classid']);
            }
        }
        Item::query()->insert($items);

        $userStoredAssetsIds = Asset::query()
            ->where('user_id', $user->id)
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
                    'user_id' => $user->id,
                ];
            }
        }
        Asset::query()->insert($assets);

        $user->update([
            'last_inventory_update' => Carbon::now()
        ]);
    }

    public function updateItemsPriceHistory(array $classIds): void
    {
        ini_set('memory_limit', '512M');

        $items = Item::query()
            ->where('marketable', true)
            ->whereDate('price_last_checked', '<', Carbon::today())
            ->findMany($classIds);

        $storedPrices = Price::query()
            ->whereIn('classid', $classIds)
            ->get();

        $existingDates = [];
        foreach ($storedPrices as $storedPrice) {
            $existingDates[$storedPrice->classid . '|' . $storedPrice->date] = true;
        }

        $responses = Http::pool(function (Pool $pool) use ($items) {
            foreach ($items as $item) {
                $marketLink = 'https://steamcommunity.com/market/pricehistory/?appid=730&l=english&currency=3&market_hash_name=' . rawurlencode($item->market_name);

                $pool->as($item->classid)->withHeaders([
                        'Cookie' => env('STEAM_COOKIES'),
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
                        'Accept' => 'application/json, text/javascript, */*; q=0.01',
                        'Referer' => "https://steamcommunity.com/market/search?appid=730",
                    ])->get($marketLink);
            }
        });

        $pricesToStore = [];
        foreach ($items as $item) {
            if ($responses[$item->classid]->json() === null) continue;

            $itemPrices = $responses[$item->classid]->json()['prices'];

            foreach ($itemPrices as $price) {
                $date = DateHelpers::convertSteamDate($price[0]);

                if (!isset($existingDates[$item->classid . '|' . $date])) {
                    $pricesToStore[] = [
                        'classid' => $item->classid,
                        'date' => $date,
                        'price' => $price[1],
                        'sales_amount' => $price[2],
                    ];
                }
            }
            $item->update([
                'price_last_checked' => Carbon::today(),
            ]);
        }
        foreach (array_chunk($pricesToStore, 10000) as $chunk) {
            Price::query()->insert($chunk);
        }
    }
}
