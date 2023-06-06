@extends('layouts.master')
@section('content')
    <div class="container layout">
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-4">
                                Đã xong / chờ cung ứng
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="list_food_for_waiter">
                            @foreach($orderFoods as $item)
                                @if ($item->food != null && $item->board != null)
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
                                @endif
                            @endforeach
                        </ul>
                    </div>
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
                var orderFoodID = $(this).data('order_food_id');
                releasePartialByFood(orderFoodID);
            })
            $(document).on('click', '[data-toogle="choose_all_food"]', function (e) {
                e.preventDefault();
                var orderFoodID = $(this).data('order_food_id');
                releaseAllByFood(orderFoodID);
            })
        });

        function releasePartialByFood(id) {
            var _token = $('meta[name="csrf-token"]').attr('content');
            var kitchenQuantity = $('#kitchen-quantity-' + id).html();
            var releaseQuantity = $('#quantity-item-' + id).val();
            if (releaseQuantity == "" || isNaN(releaseQuantity) || parseInt(releaseQuantity) > parseInt(kitchenQuantity)) {
                swal('Số lượng nhập vào không đúng', '', 'error');
                return;
            }
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/ajax/waiter/release-partial-by-food',
                data: {
                    _token: _token,
                    id: id,
                    quantity: releaseQuantity,
                },
                success: function (resp) {
                    if (resp.success) {
                        swal("Thành công!", "", "success");
						socket.emit('broadcast', {'target_type': 'reload_waiter'}); // gửi event 'private'
                        setTimeout(function () {
                        	location.reload();
                        }, 1000)
                    } else {
                        swal("Có lỗi xảy ra", "", "error");
                    }
                }
            });
        }

        function releaseAllByFood(id) {
            var _token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '/ajax/waiter/release-all-by-food',
                data: {
                    _token: _token,
                    id: id,
                },
                success: function (resp) {
                    if (resp.success) {
                        swal("Thành công!", "", "success");
						socket.emit('broadcast', {'target_type': 'reload_waiter'}); // gửi event 'private'
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
