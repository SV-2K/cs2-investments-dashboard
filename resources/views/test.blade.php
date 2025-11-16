{{ auth()->user()->name }}
@dd($items)
@foreach($items as $item)
    <img width="100" height="100" src="https://community.fastly.steamstatic.com/economy/image/{{ $item['icon_url'] }}">
@endforeach
