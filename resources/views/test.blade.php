@extends('layouts.app')

@section('title')Inventory @endsection

@section('body')
    {{ auth()->user()->name }}
    <br>
    <div class="row row-cols-8 gap-2">
        @foreach($items as $item)
            <div class="col">
                <img width="100" height="100" src="https://community.fastly.steamstatic.com/economy/image/{{ $item->type->icon_url }}" style="border: 2px solid {{ '#' . $item->type->name_color }}">
            </div>
        @endforeach
    </div>
@endsection
