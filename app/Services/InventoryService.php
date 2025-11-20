<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Item;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
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
}
