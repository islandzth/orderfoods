@extends('layouts.master')
@section('content')
    <div class="container layout">
        <div class="row mt-5">
            <div class="card w-100">
                <div class="card-header">
                    @include('kitchen.partials.menu', ['current' => 'boards'])
                </div>
                <div class="card-body">
                    <div id="accordion">
                        <div class="row" id="list_food_by_board">
                            @if($boards)
                                @foreach($boards as $board)
                                        @include('kitchen.partials.board', ['board' => $board])
                                    @endforeach
                                @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
		$(document).ready(function () {
			@if(isset($listBoardId))
			@foreach($listBoardId as $boardId)
			loadFoodByBoard({{ $boardId }});
            @endforeach
            @endif

			$(document).on('click','[data-toogle="choose_number_food"]', function (e) {
				var food_order_id = $(this).data('food_order_id');
				var kitchen_quantity = $(this).closest('.input-group').find('input[name="kitchen_quantity"]').val();
				var _token = $('meta[name="csrf-token"]').attr('content');
                var totalQuantity = $('#span_food_order_' + food_order_id).html();
                if (kitchen_quantity == "" || isNaN(kitchen_quantity) || parseInt(kitchen_quantity) > parseInt(totalQuantity) || parseInt(kitchen_quantity) <= 0) {
                    swal('Số lượng nhập vào không đúng', '', 'error');
                    return;
                }
				$.ajax({
					type: "POST",
					url: "/ajax/kitchen/update-food-order",
					data: {
						_token,
						food_order_id,
						kitchen_quantity,
					},
					success: function (data) {
						if(data.err) {
							alert('error');
                        }else {
							socket.emit('broadcast',  data.data);
							loadFoodByBoard(data.board_id);
                        }
					}
				});
			});
			$(document).on('click','[data-toogle="choose_all_food"]', function (e) {
				var food_order_id = $(this).data('food_order_id');
				var kitchen_quantity = $(this).data('kitchen_quantity');
				var _token = $('meta[name="csrf-token"]').attr('content');
				// var confirmCheck = confirm('Bạn chắc không ? ');
				// if(!confirmCheck) return false;
				$.ajax({
					type: "POST",
					url: "/ajax/kitchen/update-food-order",
					data: {
						_token,
						food_order_id,
						kitchen_quantity,
					},
					success: function (data) {
						if(data.err) {
							alert('error');
						}else {
							socket.emit('broadcast',  data.data);
							loadFoodByBoard(data.board_id);
						}
					}
				});
			})
		});
    </script>
@endsection
