<?php

Route::get('/', 'HomeController@index')->name('homepage');

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    Route::get('/home', 'HomeController@index')->name('home');
    Route::group(['middleware' => 'order_auth'], function () {
        Route::get('/order/tables', 'OrderController@tables')->name('order.tables');
        Route::get('/order/tables/{tableId}', 'OrderController@tableDetail')->name('order.tables.detail');
        Route::get('/order/tables/foods/{tableId}/', 'OrderController@listFoods')->name('order.tables.foods');
        Route::post('/ajax/create-order', 'OrderController@createOrder')->name('order.create');
        Route::post('/ajax/order/checkout', 'OrderController@checkout')->name('order.checkout');
        Route::post('/ajax/order/tables-by-areas', 'OrderController@loadTableByAreaAjax')->name('order.table-by-area');
    });

    Route::post('/ajax/getFoodDetailModal', 'OrderController@getFoodDetail')->name('order.getFoodDetailModal');
    Route::post('/ajax/sendNotification', 'OrderController@sendNotification')->name('order.sendNotification');
    Route::post('/ajax/updateFireToken', 'HomeController@updateFireBaseToken');

    Route::group(['middleware' => 'kitchen_auth'], function () {
        Route::get('/kitchen/order', 'KitchenController@kitchenHomePageByOrder')->name('kitchen.by-order');
        Route::get('/kitchen/food', 'KitchenController@kitchenHomePageByFood')->name('kitchen.by-food');
        Route::get('/kitchen/board', 'KitchenController@kitchenHomePageByBoard')->name('kitchen.by-board');
    });

    Route::group(['middleware' => 'general_kitchen_auth'], function () {
        Route::get('/general-kitchen/order', 'GeneralKitchenController@kitchenHomePageByOrder')->name('general-kitchen.by-order');
        Route::get('/general-kitchen/food', 'GeneralKitchenController@kitchenHomePageByFood')->name('general-kitchen.by-food');
        Route::get('/general-kitchen/board', 'GeneralKitchenController@kitchenHomePageByBoard')->name('general-kitchen.by-board');
    });

    Route::post('/ajax/kitchen/board-ajax/load-food-order-general', 'GeneralKitchenController@loadFoodByBoardAjax')->name('general-kitchen.by-food-by-board-ajax');
    Route::post('/ajax/kitchen/board-ajax/load-food-general', 'GeneralKitchenController@loadFoodByAjax')->name('general-kitchen.by-food-by-ajax');

    Route::post('/ajax/kitchen/board-ajax/load-board', 'KitchenController@loadBoardAjax')->name('kitchen.by-load-board-ajax');
    Route::post('/ajax/kitchen/board-ajax/load-food-order', 'KitchenController@loadFoodByBoardAjax')->name('kitchen.by-food-by-board-ajax');
    Route::post('/ajax/kitchen/board-ajax/load-food', 'KitchenController@loadFoodByAjax')->name('kitchen.by-food-by-ajax');
    Route::post('/ajax/kitchen/update-food-order', 'KitchenController@updateFoodOrder')->name('kitchen.update-food-order-ajax');

    Route::post('/ajax/kitchen/release-by-order', 'KitchenController@releaseByOrder');
    Route::post('/ajax/kitchen/release-all-by-food', 'KitchenController@releaseAllByFood');
    Route::post('/ajax/kitchen/release-partial-by-food', 'KitchenController@releasePartialByFood');

    Route::get('/ajax/order-detail-by-food/{id}', 'KitchenController@orderFoodDetail');
    Route::get('/ajax/order-detail-by-food-general/{id}', 'GeneralKitchenController@orderFoodDetail');

    Route::group(['middleware' => 'waiter_auth'], function () {
        Route::get('/waiter', 'WaiterController@getWaiterPage')->name('waiter.home');
        Route::post('/ajax/waiter/release-all-by-food', 'WaiterController@releaseAllByFood');
        Route::post('/ajax/waiter/release-partial-by-food', 'WaiterController@releasePartialByFood');
        Route::post('/ajax/waiter/load-food', 'WaiterController@loadFood');
    });
});

