<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Item;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Http;

class ItemsController extends Controller
{
    public function index(InventoryService $service): View
    {
        $service->updateUserInventory(auth()->user());
        $service->updateItemsPriceHistory(Item::all()->pluck('classid')->toArray());

        $assets = Asset::query()
            ->with('item')
            ->where('user_id', auth()->user()->id)
            ->orderByDesc('id')
            ->get();

        return view('index', compact('assets'));
    }
}
