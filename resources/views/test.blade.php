@extends('layouts.app')

@section('title')Inventory @endsection

@section('body')
    {{ auth()->user()->name }}
    <br>
    <div class="row row-cols-8 gap-2">
        @foreach($assets as $asset)
            <div class="col">
                <img width="100" height="100" src="https://community.fastly.steamstatic.com/economy/image/{{ $asset->item->icon_url }}" style="border: 2px solid {{ '#' . $asset->item->name_color }}">
            </div>
        @endforeach
    </div>
@endsection
