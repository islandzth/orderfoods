@extends('layouts.master')
@section('content')
<div class="container">
    <div class="row mt-2">
        <div class="col-12">
            <nav class="nav nav-pills nav-justified menu-tables-areas">
                <a class="nav-link active" data-area_id="0" href="#" data-toogle="chose_area">Tất cả</a>
                @if($areas)
                    @foreach($areas as $area)
                        <a class="nav-link" href="#" data-area_id="{{ $area->id }}" data-toogle="chose_area">{{ $area->name }}</a>
                    @endforeach
                @endif
            </nav>
        </div>
    </div>
    <div class="row mt-2" id="list_tables">
        @foreach($tables as $tb)
        <div class="col-6 col-sm-3 col-md-3 mt-3">
            <div class="card {{ $tb->status == 1 ? 'board-busy' : '' }}">
                <div class="card-body">
                    <a href="{{ route('order.tables.detail', ['tableId' => $tb->id]) }}">
                        <h5 class="card-title text-center" >{{ $tb->name }}</h5>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
@section('scripts')
    <script>
		var _token = $('meta[name="csrf-token"]').attr('content');
        $(document).ready(function () {
            $('[data-toogle="chose_area"]').on('click', function (e) {
            	$('.menu-tables-areas a').removeClass('active');
            	$(this).addClass('active');
                e.preventDefault();
                $('#list_tables').empty().append('<div class="loading mt-5 text-center"><div class="spinner-border"></div></div>');

                var area_id = $(this).data('area_id');
				$.ajax({
					type: "POST",
					url: "/ajax/order/tables-by-areas",
					data: {
						_token,
						area_id,
					},
					success: function (data) {
                        var tables = data.tables;
                        var html = '';
                        if (tables.length > 0) {
							tables.forEach(function (val, idx) {
								var busy = parseInt(val.status) === 1 ? 'board-busy' : '';
								html += ' <div class="col-6 col-sm-3 col-md-3 mt-3">\n' +
									'            <div class="card">\n' +
									'                <div class="card-body '+busy+' ">\n' +
									'                    <a href="/order/tables/' + val.id + '">\n' +
									'                        <h5 class="card-title text-center" >' + val.name + '</h5>\n' +
									'                    </a>\n' +
									'                </div>\n' +
									'            </div>\n' +
									'        </div>'
							});
							$('#list_tables').empty().append(html);
						} else {
							$('#list_tables').empty().append('<div class="col-sm-12 mt-5 text-center">Không có dữ liệu</div>');
                        }
					}
				});
			})
		})
    </script>
    @endsection
