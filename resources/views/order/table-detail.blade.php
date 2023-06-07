@extends('layouts.master')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 mt-3">
                <div class="card">
                    <input type="hidden" id="board-id" value="{{ $board->id }}">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-6">
                                Món ăn đã order chờ phục vụ - {{ $board->name }}
                            </div>
                            <div class="col-6 text-right" style="line-height: 3">
                                <a href="{{ route('order.tables.foods', ['tableId' => $board->id]) }}" class="btn btn-info" id="submitData">Thêm</a>
                                <a data-board_id="{{ $board->id }}" class="btn btn-success" id="checkout">Thanh toán</a>
                            </div>
                        </div>

                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($orderFoods as $item)
                                <a href="javascript:void(0)"
                                   class="list-group-item d-flex justify-content-between align-items-center food-item">
                                    {{ $item->name }}
                                    <span class="badge badge-primary badge-pill food-quantity">{{ $item->total_order_quantity }}</span>
                                </a>
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
		var _token = $('meta[name="csrf-token"]').attr('content');
        $(document).ready(function () {
            $('#checkout').on('click', function (e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Bạn chắc chắn thanh toán bàn này?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Thanh toán',
                    cancelButtonText: 'Không thực hiện'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var data = {}
                        data.board_id = parseInt($(this).data('board_id'));
                        data._token = _token,

                            $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: '/ajax/order/checkout',
                                data: data,
                                success: function (resp) {
                                    if (resp.success) {
                                        socket.emit('broadcast',  resp.data);
                                        window.location.href = '{{ route('order.tables') }}'
                                    } else {
                                        swal("Thành công!", "", "error");
                                    }
                                }
                            });
                    }
                })
			})
		})
    </script>
    @endsection
