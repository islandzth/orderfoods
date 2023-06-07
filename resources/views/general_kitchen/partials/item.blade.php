@if (isset($orderFood) && $orderFood->food != null)
<li id="food_order_{{ $orderFood->id }}" class="list-group-item  align-items-right" kitchen-food-id="{{ isset($orderFood) ? $orderFood->food->id : '' }}">
    <div class="row">
        <div class="col-4 ">
          <div class="food-name-item pl-2">{{ isset($orderFood) ? $orderFood->food->name : '' }}</div>
            @if(isset($path) && isset($orderFood) && $path == "kitchen.by-order")
                <div class="food-name-item pl-2" style="margin-top: 5px; color: #3333ff; font-weight: bold">{{ $orderFood->board->name }}</div>
            @endif
        </div>
        <div class="col-3">
            <span id="span_food_order_{{ $orderFood->id }}" class="badge badge-primary badge-pill">{{ isset($orderFood) ? $orderFood->order_quantity - $orderFood->kitchen_quantity : 0 }}</span>
            <span id="time-calculate-{{ $orderFood->id }}" data-id="{{ $orderFood->id }}" data-origin="{{ $orderFood->created_at->format('H:i:s') }}" data-created_at="{{ strtotime($orderFood->created_at) }}" class="badge badge-danger badge-pill time-calculate">00:00</span>
        </div>
        <div class="col-5 text-right">
{{--            <div class="input-group group-input-kitchen">--}}
{{--                <input type="number" name="kitchen_quantity" min="0" max="100" class="form-control input-kitchen-number" placeholder=""--}}
{{--                       value="{{ isset($orderFood) ? $orderFood->order_quantity - $orderFood->kitchen_quantity : 0 }}">--}}
{{--                <div class="input-group-append ml-2">--}}
{{--                    <button data-toogle="choose_number_food"  data-kitchen_quantity="{{ isset($orderFood) ? $orderFood->order_quantity - $orderFood->kitchen_quantity : 0 }}"--}}
{{--                             data-food_order_id="{{ isset($orderFood) ? $orderFood->id : 0 }}" class="btn btn-info choose_number_food" type="button"> > </button>--}}
{{--                    <button data-toogle="choose_all_food"  data-kitchen_quantity="{{ isset($orderFood) ? $orderFood->order_quantity : 0 }}" data-food_order_id="{{ isset($orderFood) ? $orderFood->id : 0 }}" class="btn btn-success choose_all_food" type="button"> >> </button>--}}
{{--                </div>--}}
{{--            </div>--}}
        </div>
    </div>
    @if ($orderFood->note != "")
    <div class="row">
        <div class="col-12">
            <p class="pl-2" style="color: red">Note: {{ $orderFood->note }}</p>
        </div>
    </div>
    @endif
</li>
@endif
