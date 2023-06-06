@extends('layouts.master')
@section('content')
    <div class="container layout">
        <div class="row mt-5">
            <div class="card w-100">
                    <div class="card-header">
                        @include('kitchen.partials.menu', ['current' => 'orders'])
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="list-order-by-order">
                            @foreach($orderFoods as $orderFood)
                                @if($orderFood->food != null)
                                    <li id="food_order_{{ $orderFood->id }}" class="list-group-item  align-items-right">
                                        <div class="row">
                                            <div class="col-4 ">
                                                <div class="food-name-item pl-2">{{ isset($orderFood) ? $orderFood->food->name : '' }}</div>
                                                @if ($orderFood->board != null)
                                                    <div class="food-name-item pl-2" style="margin-top: 5px; color: #3333ff; font-weight: bold">{{ $orderFood->board->name }}</div>
                                                @endif
                                            </div>
                                            <div class="col-3">
                                                <span class="badge badge-primary badge-pill">{{ isset($orderFood) ? $orderFood->order_quantity - $orderFood->kitchen_quantity : 0 }}</span>
                                                <span id="time-calculate-{{ $orderFood->id }}" data-id="{{ $orderFood->id }}" data-origin="{{ $orderFood->created_at->format('H:i:s') }}" data-created_at="{{ strtotime($orderFood->created_at) }}" class="badge badge-danger badge-pill time-calculate">00:00</span>
                                            </div>
                                            <div class="col-5 text-right">
                                                <div class="input-group group-input-kitchen">
                                                    <input type="number" name="kitchen_quantity" min="0" max="100" class="form-control input-kitchen-number"
                                                           placeholder="" value="{{ isset($orderFood) ? $orderFood->order_quantity - $orderFood->kitchen_quantity : 0 }}">
                                                    <div class="input-group-append ml-2">
                                                        <button data-toogle="choose_number_food" data-kitchen_quantity="{{ isset($orderFood) ? $orderFood->order_quantity - $orderFood->kitchen_quantity : 0 }}"
                                                                data-food_order_id="{{ isset($orderFood) ? $orderFood->id : 0 }}"
                                                                class="btn btn-info choose_number_food" type="button"> > </button>
                                                        
                                                    </div>
                                                </div>
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
                            @endforeach
                        </ul>
                    </div>
                </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function () {
            var _token = $('meta[name="csrf-token"]').attr('content');
            $(document).on('click', '[data-toogle="choose_number_food"]', function (e) {
                e.preventDefault();
                var quantity = $(this).parents('.group-input-kitchen').find('input').val();
                var orderQuantity = $(this).data('kitchen_quantity');
                if (quantity == '' || parseInt(quantity) > parseInt(orderQuantity) || parseInt(quantity) <= 0) {
                    swal({
                        icon: "error",
                        text: "Số lượng nhập không hợp lệ",
                    });
                    return;
                }
                var orderFoodID = $(this).data('food_order_id');
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/ajax/kitchen/release-by-order',
                    data: {
                        _token: _token,
                        order_food_id: orderFoodID,
                        quantity: quantity
                    },
                    success: function (resp) {
                        if (resp.success) {
							socket.emit('broadcast',  resp.data);
                            swal("Thành công!", "", "success");
                            setTimeout(function () {
                                location.reload();
                            }, 1000)
                        } else {
                            swal("Có lỗi xảy ra", "", "error");
                        }
                    }
                });
			})
			$(document).on('click', '[data-toogle="choose_all_food"]', function (e) {
                e.preventDefault();
                var orderQuantity = $(this).data('kitchen_quantity');
                var orderFoodID = $(this).data('food_order_id');
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/ajax/kitchen/release-by-order',
                    data: {
                        _token: _token,
                        order_food_id: orderFoodID,
                        quantity: orderQuantity,
                        is_all: true,
                    },
                    success: function (resp) {
                        if (resp.success) {
							socket.emit('broadcast',  resp.data);
                            swal("Thành công!", "", "success");
                            setTimeout(function () {
                                location.reload();
                            }, 1000)
                        } else {
                            swal("Có lỗi xảy ra", "", "error");
                        }
                    }
                });
			})
		});
    </script>
    @endsection
