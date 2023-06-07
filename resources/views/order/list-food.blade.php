@extends('layouts.master')
@section('content')
    <div class="container layout">

        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 mt-3">
                <div class="card">
                    <input type="hidden" id="board-id" value="{{ $board->id }}">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                Danh sách món ăn - Order cho {{ $board->name }}
                            </div>
                            <div class="col-6 text-right">
                                <button class="btn btn-info" id="submitData">Xong</button>
                                <button class="btn btn-secondary" id="resetData">Reset</button>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <div class="row mt-2 mb-2">
                            <div class="col-12">
                                <nav class="nav nav-pills nav-justified category-foods">
                                    <a class="nav-link active" data-category_id="0" href="#" data-toogle="chose_category">Tất cả</a>
                                    @if($categories)
                                        @foreach($categories as $category)
                                            <a class="nav-link" href="#" data-category_id="{{ $category->id }}" data-toogle="chose_category">{{ $category->name }}</a>
                                        @endforeach
                                    @endif
                                </nav>
                            </div>
                        </div>
                        <ul class="list-group" id="list_foods">
                            @foreach($foods as $food)
                                <li class="list-group-item  align-items-right" data-category_id="{{ $food->category_id }}">
                                    <div class="row">
                                        <div class="col-3">
                                            <div class="food-name-item pl-2">
                                                {{ $food->name }}
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <textarea class="form-control txt-note" name="note" rows="2"></textarea>
                                        </div>
                                        <div class="col-3 text-right">
                                                <div class="input-group group-input-kitchen" data-food_id="{{ $food->id }}">
                                                    <input type="number" name="quantity" min="0" max="100" style="width: 50%;"
                                                           class="form-control food-quantity input-number input-kitchen-number"
                                                           placeholder="1" aria-label="" aria-describedby="basic-addon2" value="0">
                                                    <div class="input-group-append" style="width: 50%;">
                                                        <button class="btn btn-outline-secondary  btn-number" data-type="minus" data-field="quantity" type="button">-</button>
                                                        <button class="btn btn-outline-secondary  btn-number" data-type="plus" data-field="quantity" type="button">+</button>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="foodModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <input type="hidden" id="food-id" value="">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Chi tiết món</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Huỷ</button>
                    <button type="button" id="close-modal-order" class="btn btn-primary">Lưu</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
		var _token = $('meta[name="csrf-token"]').attr('content');
		$(document).ready(function () {
			$('[data-toogle="food-item"]').on('click', function (e) {
				e.preventDefault();
				var foodID = $(this).data('food_id')
				var quantity = $(this).find('span').html();
				$.ajax({
					type: "POST",
					url: '/ajax/getFoodDetailModal',
					data: {
						_token,
						food_id : foodID,
					},
					success: function (res) {
						$('#foodModal #food-id').val(foodID)
						$('#foodModal .modal-body').empty().append(res.html);
						$('#foodModal').find('.food-quantity').val(quantity);
						$('#foodModal').modal();
					}
				});
			})

			$('#close-modal-order').on('click', function () {
				var foodID = $('#foodModal #food-id').val();
				var quantity = $('#foodModal').find('.food-quantity').val();
				if (foodID > 0) {
					$('#food-item-' + foodID).find('span').html(quantity)
				}
				$('#foodModal').modal('toggle');

			})

			$('#submitData').on('click', function (e) {
			    e.preventDefault();
			    var btn = $(this);
				var data = {}
				var foods = []
				$('.group-input-kitchen').each(function (index) {
					var quantityItem = $(this).find('input.food-quantity').val();
					if (quantityItem > 0) {
						foods.push(
							{
								id: $(this).data('food_id'),
								qty: parseInt(quantityItem),
                                note: $(this).parents('.list-group-item').find('textarea').val(),
							}
						)
					}
				})

                console.log(foods)
                if (foods.length <= 0 ) {
                    return
                }
				data.foods = foods;
				data.board_id = parseInt($('#board-id').val());
				data._token = _token,
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: '/ajax/create-order',
                    beforeSend: function() {
                        btn.attr('disabled', 'disabled')
                    },
                    data: data,
                    success: function (resp) {
                        btn.removeAttr('disabled');
                        if (resp.success) {
							socket.emit('broadcast',  resp.data); // gửi event 'private'
                            window.location.href = '{{ route('order.tables.detail', ['tableId' => $board->id]) }}'
                        } else {
                            swal("Có lỗi xảy ra", "", "error");
                        }
                    }
                });
			});

			$('#resetData').on('click', function (e) {
                e.preventDefault();

                $('.group-input-kitchen').each(function (index) {
                   $(this).find('input.food-quantity').val(0);
                })
            })

			$('[data-toogle="chose_category"]').on('click', function (e) {
				$('.category-foods a').removeClass('active');
				$(this).addClass('active');
				e.preventDefault();
				var category_id = $(this).data('category_id');
				if(parseInt(category_id) === 0) {
					$('#list_foods li').css('display', 'block');
                } else {
					$('#list_foods li').each(function (val, idx) {
						var cate_id = $(this).data('category_id');
						if (parseInt(cate_id) === parseInt(category_id)) {
							$(this).css('display', 'block');
						} else {
							$(this).css('display', 'none');
						}
					});
				}
			})
		})


    </script>
@endsection
