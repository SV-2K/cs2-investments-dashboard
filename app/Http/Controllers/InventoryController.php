<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(InventoryService $service): View
    {
        $service->updateUserInventory(auth()->user());

        $assets = Asset::query()
            ->with('item')
            ->where('user_id', auth()->user()->id)
            ->get();

        return view('test', compact('assets'));
    }
}
