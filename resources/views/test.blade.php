@extends('layouts.app')

@section('title')Inventory @endsection

@section('body')
    <p>
        {{ auth()->user()->name }}
    </p>
    <p>
        Items: {{ $assets->count() }}
    </p>
    <div class="row gap-2">
        @foreach($assets as $asset)
            <div class="item" @style("border: 2px solid #{$asset->item->name_color}")>
                <img src="https://community.fastly.steamstatic.com/economy/image/{{ $asset->item->icon_url }}">
            </div>
        @endforeach
    </div>
@endsection
