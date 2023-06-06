@if(!empty($orderFoods))
    @foreach($orderFoods as $stt=>$orderFood)
        @include('general_kitchen.partials.item', ['orderFood' => $orderFood, 'stt' => $stt])
    @endforeach
@endif
