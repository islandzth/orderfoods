$(document).ready(function () {
    //plugin bootstrap minus and plus
    $(document).on('click', '.btn-number', function (e) {
        e.preventDefault();
        var fieldName = $(this).attr('data-field');
        var type      = $(this).attr('data-type');
        var input = $(this).closest('.input-group').find("input[name='"+fieldName+"']");
        var currentVal = parseInt(input.val());
        if (!isNaN(currentVal)) {
            if(type == 'minus') {
                if(currentVal > input.attr('min')) {
                    input.val(currentVal - 1).change();
                }
                if(parseInt(input.val()) == input.attr('min')) {
                    $(this).attr('disabled', true);
                }

            } else if(type == 'plus') {
                if(currentVal < input.attr('max')) {
                    input.val(currentVal + 1).change();
                }
                if(parseInt(input.val()) == input.attr('max')) {
                    $(this).attr('disabled', true);
                }

            }
        } else {
            input.val(0);
        }
    });

    $(this).closest('.input-group').find('.input-number').focusin(function(){
        $(this).data('oldValue', $(this).val());
    });
    $(this).closest('.input-group').find('.input-number').change(function() {

       var  minValue =  parseInt($(this).attr('min'));
       var maxValue =  parseInt($(this).attr('max'));
       var valueCurrent = parseInt($(this).val());

        var name = $(this).attr('name');
        if(valueCurrent >= minValue) {
            $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
        } else {
            alert('Sorry, the minimum value was reached');
            $(this).val($(this).data('oldValue'));
        }
        if(valueCurrent <= maxValue) {
            $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
        } else {
            alert('Sorry, the maximum value was reached');
            $(this).val($(this).data('oldValue'));
        }


    });
    $(this).closest('.input-group').find(".input-number").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
            // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
            // let it happen, don't do anything
            return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
});


/*******/
function loadKitchenBoard(board_id) {
    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "POST",
        url: "/ajax/kitchen/board-ajax/load-board",
        data: {
            _token,
            board_id,
        },
        success: function (data) {
            $('#list_food_by_board').append(data.html);
            loadFoodByBoard(data.board_id);
        }
    });
}
function loadFoodByBoard(board_id) {
        $('#heading_' + board_id + ' ul.list-group').empty().append('<div class="loading mt-5 text-center"><div class="spinner-border"></div></div>');
        var _token = $('meta[name="csrf-token"]').attr('content');
        $.ajax({
            type: "POST",
            url: "/ajax/kitchen/board-ajax/load-food-order",
            data: {
                _token,
                board_id,
            },
            success: function (data) {
                $('#heading_' + board_id + ' ul.list-group').empty().append(data.html);
                calculateTimeOrder();
                if (data.count_food === 0) {
                    $('#group_board_' + board_id).remove();
                }
            }
        });
}

function loadFoodByBoardGeneralKitchen(board_id) {
    $('#heading_' + board_id + ' ul.list-group').empty().append('<div class="loading mt-5 text-center"><div class="spinner-border"></div></div>');
    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "POST",
        url: "/ajax/kitchen/board-ajax/load-food-order-general",
        data: {
            _token,
            board_id,
        },
        success: function (data) {
            $('#heading_' + board_id + ' ul.list-group').empty().append(data.html);
            calculateTimeOrder();
            if (data.count_food === 0) {
                $('#group_board_' + board_id).remove();
            }
        }
    });
}

function loadFoodOnOrder(order_food_id, elem, path) {
    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "POST",
        url: "/ajax/kitchen/board-ajax/load-food",
        data: {
            _token,
            order_food_id,
            path: path,
        },
        success: function (data) {
            if (data.html !== "") {
                elem.append(data.html);
                calculateTimeOrder();
            }
        }
    });
}

function loadFoodOnOrderGeneral(order_food_id, elem, path) {
    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "POST",
        url: "/ajax/kitchen/board-ajax/load-food-general",
        data: {
            _token,
            order_food_id,
            path: path,
        },
        success: function (data) {
            if (data.html !== "") {
                elem.append(data.html);
                calculateTimeOrder();
            }
        }
    });
}

function moneyFormat(number, decimalSeparator, thousandsSeparator, nDecimalDigits){
    //default values
    decimalSeparator = decimalSeparator || '.';
    thousandsSeparator = thousandsSeparator || ',';
    nDecimalDigits = nDecimalDigits == null? 2 : nDecimalDigits;

    var fixed = number.toFixed(nDecimalDigits), //limit/add decimal digits
        parts = new RegExp('^(-?\\d{1,3})((?:\\d{3})+)(\\.(\\d{'+ nDecimalDigits +'}))?$').exec( fixed ); //separate begin [$1], middle [$2] and decimal digits [$4]

    if(parts){ //number >= 1000 || number <= -1000
        return parts[1] + parts[2].replace(/\d{3}/g, thousandsSeparator + '$&') + (parts[4] ? decimalSeparator + parts[4] : '');
    }else{
        return fixed.replace('.', decimalSeparator);
    }
}
function loadFoodForWaiter(order_food_id) {
    var _token = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
        type: "POST",
        dataType: "json",
        url: '/ajax/waiter/load-food',
        data: {
            _token,
            order_food_id: order_food_id,
        },
        success: function (resp) {
            if (resp.html) {
                var elem = $(document).find('li#waiter-order-id-' + order_food_id)
                if (elem.length > 0) {
                    elem.find('#kitchen-quantity-'  +order_food_id).html(resp.data.kitchen_quantity);
                    elem.find('#quantity-item-'  +order_food_id).val(resp.data.kitchen_quantity);
                } else {
                    $('#list_food_for_waiter').append(resp.html);
                    calculateTimeOrder();
                }
            } else {
                swal("Có lỗi xảy ra", "", "error");
            }
        }
    });
}
function removeFoodRow(order_food_id) {
    $(document).find('#food_order_'+order_food_id).remove();
}

$(window).on('load', function () {
    calculateTimeOrder();
});

function calculateTimeOrder() {
    var listTimes = [];
    $('.time-calculate').each(function (e) {
        var date_created_at = $(this).data('created_at');
        var id = $(this).data('id');
        listTimes.push({id: id , created_at: date_created_at});
    });
    if (listTimes.length > 0) {
        setInterval(function () {
            listTimes.forEach(function (val, idx) {
                var jsTimestamp = new Date(val.created_at * 1000);
                var currentTime = new Date();
                var diffTimeCalculate = (currentTime - jsTimestamp) / 1000;
                var hours = Math.floor(diffTimeCalculate / 3600 );
                var minute = Math.floor((diffTimeCalculate  - (3600 * hours)) / 60 );

                if (minute < 10) {
                    minute = '0'+ minute;
                }
                if (hours < 10) {
                    hours = '0'+ hours;
                }
                var timeCalculateFormat = hours + ':' + minute;

                $('#time-calculate-'+ val.id).html(timeCalculateFormat);
            });
        }, 1000);
    }
}
