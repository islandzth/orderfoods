<?php

namespace App\Http\Controllers;

use App\Board;
use App\Food;
use App\Jobs\PushNotification;
use App\OrderFood;
use App\User;
use App\WaiterFood;
use Response;
use Validator;
use Input;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralKitchenController extends Controller
{
    public function kitchenHomePageByOrder()
    {
        $view = [];
        $orderFoods = OrderFood::with('food')
            ->with('board')
            ->whereRaw('order_quantity > kitchen_quantity')->orderBy('created_at', 'ASC')->get();

        $view['foods'] = [];
        $view['orderFoods'] = $orderFoods;
        return view('general_kitchen.orders', $view);
    }

    public function kitchenHomePageByFood()
    {
        $oderFoods = DB::select('select orderFood.food_id, f.name, sum(orderFood.order_quantity - orderFood.kitchen_quantity) as total_order_quantity
from order_foods as orderFood
join food f on orderFood.food_id = f.id
where orderFood.order_quantity > orderFood.kitchen_quantity
group by orderFood.food_id');

        $view = [];
        $view['orderFoods'] = $oderFoods;
        return view('general_kitchen.foods', $view);
    }

    public function kitchenHomePageByBoard()
    {
        $queryListBoardIds = DB::select('SELECT board_id FROM order_foods WHERE order_foods.order_quantity > order_foods.kitchen_quantity GROUP BY board_id');

        if (isset($queryListBoardIds) && count($queryListBoardIds) > 0) {
            $listBoardId = array_pluck($queryListBoardIds, 'board_id');
            $boards = Board::whereIn('id', $listBoardId)->get();
            return view('general_kitchen.boards', compact('boards', 'listBoardId'));
        } else {
            return view('general_kitchen.boards', [
                'boards' => [],
                'listBoardId' => [],
            ]);
        }
    }

    public function loadBoardAjax(Request $request) {
        $board = Board::where('id', $request->board_id)->first();
        $html = View('general_kitchen.partials.board', compact('board'))->render();
        return ['html' => $html, 'board_id' => $request->board_id ];
    }

    public function loadFoodByBoardAjax(Request $request) {
        $orderFoods = OrderFood::where('board_id', $request->board_id)
            ->whereRaw('order_quantity > kitchen_quantity')->get();
        $html = view('general_kitchen.partials.list_food', compact('orderFoods'))->render();
        return ['html' => $html, 'count_food' => count($orderFoods)];
    }

    public function loadFoodByAjax(Request $request) {
        $input = Input::all();
        $orderFood = OrderFood::with('food')
            ->with('board')
            ->where('id', $request->order_food_id)
            ->first();
        if (!$orderFood) {
            return ['html' => ''];
        }
        $path = isset($input['path']) ? $input['path'] : '';
        $html = view('general_kitchen.partials.item', [
            'orderFood' => $orderFood,
            'path' => $path,
        ])->render();
        return ['html' => $html];
    }

    public function orderFoodDetail($id)
    {
        $orderFood = OrderFood::with('food')->where('id', '=', $id)
        ->first();
        if (!$orderFood) {
            return Response::json([
                'success' => false,
            ]);
        }

        $query = DB::select('select sum(orderFood.order_quantity - orderFood.kitchen_quantity) as total_order_quantity
                from order_foods as orderFood
                where orderFood.food_id = :foodID and orderFood.order_quantity > orderFood.kitchen_quantity', [
            'foodID' =>$orderFood->food_id,
        ]);

        return Response::json([
            'success' => true,
            'data' => [
                'name' => $orderFood->food->name,
                'food_id' => $orderFood->food_id,
                'total_order_quantity' => intval($query[0]->total_order_quantity),
            ],
        ]);
    }

    protected function pushNotificationToWaiter($boardName, $foodName, $number, $orderFoodId) {
        return [
            'title' => 'Kitchen #'. $foodName,
            'body' => $boardName .' - '.$number.' - '. $foodName,
            'target_type' => 'done_food',
            'target_value' => $orderFoodId,
            'content' => $number,
        ];
    }
}
