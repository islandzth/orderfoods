@extends('layouts.master')
@section('content')
    <div class="container layout">
        <div class="row mt-5">
                <div class="card w-100">
                    <div class="card-header">
                        @include('general_kitchen.partials.menu', ['current' => 'foods'])
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($orderFoods as $item)
                                <li id="food_order_{{ $item->food_id }}" class="list-group-item  align-items-right" kitchen-food-id="{{ $item->food_id }}">
                                    <div class="row row-rm-p">
                                        <div class="col-5"><div class="food-name-item">{{ $item->name }} </div></div>
                                        <div class="col-1">
                                            <span id="food-item-{{ $item->food_id }}" class="badge badge-primary badge-pill">{{ $item->total_order_quantity }}</span>
                                        </div>
                                        <div class="col-6 text-right">
{{--                                            <div class="input-group group-input-kitchen">--}}
{{--                                                <input type="number" id="qty-food-item-{{ $item->food_id }}" name="kitchen_quantity" min="0" max="100" class="form-control input-kitchen-number" placeholder="" value="{{ $item->total_order_quantity }}">--}}
{{--                                                <div class="input-group-append ml-2">--}}
{{--                                                    <button data-toogle="choose_number_food" data-food_id="{{ $item->food_id }}" class="btn btn-info choose_number_food" type="button"> > </button>--}}
{{--                                                    <button data-toogle="choose_all_food" data-food_id="{{ $item->food_id }}" class="btn btn-success choose_all_food" type="button"> >> </button>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
                                        </div>
                                    </div>
                                </li>
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
			$(document).on('click', '[data-toogle="choose_number_food"]', function (e) {
			    e.preventDefault();
				var food_id = $(this).data('food_id');
                releasePartialByFood(food_id);
			})
			$(document).on('click', '[data-toogle="choose_all_food"]', function (e) {
                e.preventDefault();
                var food_id = $(this).data('food_id');
                releaseAllByFood(food_id);
			})
		});

        function releasePartialByFood(foodID) {
            var _token = $('meta[name="csrf-token"]').attr('content');
            var totalQuantity = $('#food-item-' + foodID).html();
            var releaseQuantity = $('#qty-food-item-' + foodID).val();
            if (releaseQuantity == "" || isNaN(releaseQuantity) || parseInt(releaseQuantity) > parseInt(totalQuantity) || parseInt(releaseQuantity) <= 0) {
                swal('Số lượng nhập vào không đúng', '', 'error');
                return;
            }
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/ajax/kitchen/release-partial-by-food',
                data: {
                    _token: _token,
                    food_id: foodID,
                    quantity: releaseQuantity,
                },
                success: function (resp) {
                    if (resp.success) {
						resp.data.forEach(function (v) {
							socket.emit('broadcast',  v);
						});

                        swal("Thành công!", "", "success");
                        setTimeout(function () {
                          location.reload();
                        }, 1000)
                    } else {
                        swal("Có lỗi xảy ra", "", "error");
                    }
                }
            });
        }

		function releaseAllByFood(foodID) {
            var _token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/ajax/kitchen/release-all-by-food',
                data: {
                    _token: _token,
                    food_id: foodID,
                },
                success: function (resp) {
                    if (resp.success) {
						resp.data.forEach(function (v) {
							socket.emit('broadcast',  v);
						});
                        swal("Thành công!", "", "success");
                        setTimeout(function () {
                           location.reload();
                        }, 1000)
                    } else {
                        swal("Có lỗi xảy ra", "", "error");
                    }
                }
            });
        }
    </script>
@endsection
