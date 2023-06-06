<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">

    <title>Order System</title>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('/alertifyjs/css/alertify.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('/alertifyjs/css/themes/default.css') }}" type="text/css">
    @yield('css')
</head>
<body>
<header>
    <audio id="order-sound" controls style="display:none;">
        <source src="{{ asset("ping-sound.mp3") }}" type="audio/mpeg" >
    </audio>
    <nav class="navbar navbar-expand-md navbar-light navbar-laravel">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
</header>
@yield('content')

<!-- Optional JavaScript -->
<script type="text/javascript" src="{{ asset('js/jquery.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}" ></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="{{ asset('/alertifyjs/alertify.js') }}"></script>
<script src="{{ asset('js/common.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/2.0.4/socket.io.js"></script>

<script>
    @auth()
	var currentName = "{{ Route::currentRouteName() }}";
	var socket = io.connect('{{ env('SOCKET_IO', 'http://localhost:6768') }}');// clent khởi tạo kết nối socket đến server
	socket.on('news', function (data) { // lắng nghe event 'news' được server gửi đến

		// alertify.warning('OK ');
		playSound(data);
		showNotification(data);
	});
    const showNotification= function (notifData) {
		var notification = notifData;
		if (currentName === 'waiter.home') {
			if(notification.target_type === 'reload_waiter') {
				location.reload();
			}
        }

        // Reload data
        if (notification.target_type === 'checkout_order') {
            // Checkout
            if (currentName === 'kitchen.by-board') {
                var boardElem = $(document).find('#group_board_' + notification.target_value);
                if (boardElem.length > 0) {
                    boardElem.remove();
                }
            } else if (currentName === 'kitchen.by-order') {
                var listOrderFoodId = notification.order_food_ids;
                if (listOrderFoodId && listOrderFoodId.length > 0) {
                    listOrderFoodId.forEach(function (val) {
                        removeFoodRow(val);
                    });
                }
            } else if (currentName === 'kitchen.by-food') {
                var listOrderFoodId = notification.order_food_ids;
                if (listOrderFoodId && listOrderFoodId.length > 0) {
                    listOrderFoodId.forEach(function (val) {
                        reloadForKitchenByFoodPage(val);
                    });
                }
            } else if (currentName === 'general-kitchen.by-board') {
                var boardElem = $(document).find('#group_board_' + notification.target_value);
                if (boardElem.length > 0) {
                    boardElem.remove();
                }
            } else if (currentName === 'general-kitchen.by-order') {
                var listOrderFoodId = notification.order_food_ids;
                if (listOrderFoodId && listOrderFoodId.length > 0) {
                    listOrderFoodId.forEach(function (val) {
                        removeFoodRow(val);
                    });
                }
            } else if (currentName === 'general-kitchen.by-food') {
                var listOrderFoodId = notification.order_food_ids;
                if (listOrderFoodId && listOrderFoodId.length > 0) {
                    listOrderFoodId.forEach(function (val) {
                        reloadForKitchenByFoodPageGeneral(val);
                    });
                }
            }
        } else if (notification.target_type == "new_order") {
            // New order
            if (currentName === 'kitchen.by-board') {
                if ($('#group_board_' + notification.target_value).length === 0) {
                    loadKitchenBoard(notification.target_value);
                } else {
                    var orderFoodIds = notification.content;
                    if (orderFoodIds) {
                        var elem = $('#heading_' + notification.target_value + ' ul.list-group');
                        orderFoodIds.forEach((val, inx)=> {
                            loadFoodOnOrder(val, elem, currentName);
                        })
                    }
                    var distance = document.getElementById('group_board_' + notification.target_value).offsetTop;
                    $('html, body').animate({scrollTop: '' + distance + 'px'});
                }
            } else if (currentName === 'kitchen.by-order') {
                var orderFoodIds = notification.content;
                if (orderFoodIds) {
                    var elem = $('#list-order-by-order');
                    orderFoodIds.forEach((val, inx)=> {
                        loadFoodOnOrder(val, elem, currentName);
                    })
                }
            } else if (currentName === 'kitchen.by-food') {
                var orderFoodIds = notification.content;
                if (orderFoodIds) {
                    orderFoodIds.forEach((val, inx)=> {
                        reloadForKitchenByFoodPage(val);
                    });
                }
            } else if (currentName === 'general-kitchen.by-board') {
                if ($('#group_board_' + notification.target_value).length === 0) {
                    loadFoodOnOrderGeneral(notification.target_value);
                } else {
                    var orderFoodIds = notification.content;
                    if (orderFoodIds) {
                        var elem = $('#heading_' + notification.target_value + ' ul.list-group');
                        orderFoodIds.forEach((val, inx)=> {
                            loadFoodOnOrderGeneral(val, elem, currentName);
                        })
                    }
                    var distance = document.getElementById('group_board_' + notification.target_value).offsetTop;
                    $('html, body').animate({scrollTop: '' + distance + 'px'});
                }
            } else if (currentName === 'general-kitchen.by-order') {
                var orderFoodIds = notification.content;
                if (orderFoodIds) {
                    var elem = $('#list-order-by-order');
                    orderFoodIds.forEach((val, inx)=> {
                        loadFoodOnOrderGeneral(val, elem, currentName);
                    })
                }
            } else if (currentName === 'general-kitchen.by-food') {
                var orderFoodIds = notification.content;
                if (orderFoodIds) {
                    orderFoodIds.forEach((val, inx)=> {
                        reloadForKitchenByFoodPageGeneral(val);
                    });
                }
            }
        } else if (notification.target_type == "done_food") {
            if (currentName === 'waiter.home') {
                loadFoodForWaiter(notification.target_value);
            } else if (
                currentName === 'general-kitchen.by-board' ||
                currentName === 'general-kitchen.by-order' ||
                currentName === 'general-kitchen.by-food'
            ) {
                var x = document.getElementById("order-sound");
                if (x) {
                    x.play();
                }
                setTimeout(function () {
                    location.reload();
                }, 1000)
            }
        }
	};

    function reloadForKitchenByFoodPage(val) {
        var containerElem = $('ul.list-group');
        $.ajax({
            type: "GET",
            url: "/ajax/order-detail-by-food/" + val,
            success: function (resp) {
                if (resp.success) {
                    var elem = $(document).find('#food_order_' + resp.data.food_id)
                    if (elem.length <= 0) {
                        if (resp.data.total_order_quantity > 0) {
                            var html = '<li id="food_order_'+resp.data.food_id+'" class="list-group-item  align-items-right" kitchen-food-id="'+ resp.data.food_id + '">\n' +
                                '                                    <div class="row row-rm-p">\n' +
                                '                                        <div class="col-5"><div class="food-name-item">' + resp.data.name + ' </div></div>\n' +
                                '                                        <div class="col-1">\n' +
                                '                                            <span id="food-item-'+ resp.data.food_id + '" class="badge badge-primary badge-pill">'+ resp.data.total_order_quantity + '</span>\n' +
                                '                                        </div>\n' +
                                '                                        <div class="col-6 text-right">\n' +
                                '                                            <div class="input-group group-input-kitchen">\n' +
                                '                                                <input type="number" id="qty-food-item-'+ resp.data.food_id + '" name="kitchen_quantity" min="0" max="100" class="form-control input-kitchen-number" placeholder="" value="'+ resp.data.total_order_quantity + '">\n' +
                                '                                                <div class="input-group-append ml-2">\n' +
                                '                                                    <button data-toogle="choose_number_food" data-food_id="'+ resp.data.food_id + '" class="btn btn-info choose_number_food" type="button"> > </button>\n' +

                                '                                                </div>\n' +
                                '                                            </div>\n' +
                                '                                        </div>\n' +
                                '                                    </div>\n' +
                                '                                </li>';


                            containerElem.append(html)
                        }
                    } else {
                        if (resp.data.total_order_quantity > 0) {
                            elem.find('span#food-item-' + resp.data.food_id).html(resp.data.total_order_quantity);
                            elem.find('input#qty-food-item-' + resp.data.food_id).val(resp.data.total_order_quantity);
                        } else {
                            elem.remove();
                        }
                    }
                }
            }
        });
    }

    function reloadForKitchenByFoodPageGeneral(val) {
        var containerElem = $('ul.list-group');
        $.ajax({
            type: "GET",
            url: "/ajax/order-detail-by-food-general/" + val,
            success: function (resp) {
                if (resp.success) {
                    var elem = $(document).find('#food_order_' + resp.data.food_id)
                    if (elem.length <= 0) {
                        if (resp.data.total_order_quantity > 0) {
                            var html = '<li id="food_order_'+resp.data.food_id+'" class="list-group-item  align-items-right" kitchen-food-id="'+ resp.data.food_id + '">\n' +
                                '                                    <div class="row row-rm-p">\n' +
                                '                                        <div class="col-5"><div class="food-name-item">' + resp.data.name + ' </div></div>\n' +
                                '                                        <div class="col-1">\n' +
                                '                                            <span id="food-item-'+ resp.data.food_id + '" class="badge badge-primary badge-pill">'+ resp.data.total_order_quantity + '</span>\n' +
                                '                                        </div>\n' +
                                '                                        <div class="col-6 text-right">\n' +
                                '                                        </div>\n' +
                                '                                    </div>\n' +
                                '                                </li>';


                            containerElem.append(html)
                        }
                    } else {
                        if (resp.data.total_order_quantity > 0) {
                            elem.find('span#food-item-' + resp.data.food_id).html(resp.data.total_order_quantity);
                            elem.find('input#qty-food-item-' + resp.data.food_id).val(resp.data.total_order_quantity);
                        } else {
                            elem.remove();
                        }
                    }
                }
            }
        });
    }

	function playSound(data) {
        if(
            (data.target_type === 'done_food' && currentName === 'waiter.home') ||
            (data.target_type === 'new_order' && currentName === 'kitchen.by-board') ||
			(data.target_type === 'new_order' && currentName === 'kitchen.by-food') ||
			(data.target_type === 'new_order' && currentName === 'kitchen.by-order') ||
            (data.target_type === 'new_order' && currentName === 'general-kitchen.by-order') ||
            (data.target_type === 'new_order' && currentName === 'general-kitchen.by-order') ||
            (data.target_type === 'new_order' && currentName === 'general-kitchen.by-order')
        ) {
            var isPlaySound = false;
            if (data.target_type === 'done_food' && currentName === 'waiter.home') {
                isPlaySound = true;
            } else {
                if (parseInt('{{ \Auth::user()->role }}') == 100) {
                    isPlaySound = true
                } else if (data.kitchenIDs && data.kitchenIDs.length > 0 && data.kitchenIDs.includes(parseInt('{{ \Auth::user()->kitchen_id }}'))) {
                    isPlaySound = true;
                }
            }

            if (isPlaySound) {
                var x = document.getElementById("order-sound");
                if (x) {
                    x.play();
                }
            }
		}
    }

    @endauth
</script>
@yield('scripts')

</body>
</html>
