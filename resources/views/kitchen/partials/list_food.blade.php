@if(!empty($orderFoods))
    @foreach($orderFoods as $stt=>$orderFood)
        @include('kitchen.partials.item', ['orderFood' => $orderFood, 'stt' => $stt])
    @endforeach
@endif
