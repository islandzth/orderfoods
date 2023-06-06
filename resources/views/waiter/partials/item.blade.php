<li id="waiter-order-id-{{ $item->id }}" class="list-group-item  align-items-right">
    <div class="row">
        <div class="col-4">
            <div class="food-name-item pl-2"> {{ $item->food->name }} - <span style="color: #3333ff; font-weight: bold">{{ $item->board->name }}</span></div>
        </div>
        <div class="col-3">
            <span id="kitchen-quantity-{{ $item->id }}" class="badge badge-primary badge-pill">{{ $item->kitchen_quantity - $item->waiter_quantity }}</span>
            <span id="time-calculate-{{ $item->id }}" data-id="{{ $item->id }}" data-created_at="{{ strtotime($item->created_at) }}" class="badge badge-danger badge-pill time-calculate">00:00</span>
        </div>
        <div class="col-5 text-right">
            <div class="input-group group-input-kitchen">
                <input type="text" id="quantity-item-{{ $item->id }}" class="form-control input-kitchen-number" placeholder="" value="{{ $item->kitchen_quantity - $item->waiter_quantity }}">
                <div class="input-group-append ml-2">
                    <button data-toogle="choose_number_food" data-order_food_id="{{ $item->id }}" class="btn btn-info choose_number_food " type="button"> > </button>
                    <button data-toogle="choose_all_food"  data-order_food_id="{{ $item->id }}" class="btn btn-success choose_all_food" type="button"> >> </button>
                </div>
            </div>
        </div>
    </div>
    @if ($item->note != "")
        <div class="row">
            <div class="col-12">
                <p class="pl-2" style="color: red">Note: {{ $item->note }}</p>
            </div>
        </div>
    @endif
</li>
