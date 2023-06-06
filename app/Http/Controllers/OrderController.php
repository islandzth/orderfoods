<?php

namespace App\Http\Controllers;

use App\Area;
use App\Board;
use App\Food;
use App\FoodCategory;
use App\Model\User;
use App\OrderFood;
use Illuminate\Http\Request;
use App\Jobs\PushNotification;
use Illuminate\Support\Facades\DB;
use Input;
use Validator;
use Response;

class OrderController extends Controller
{
    public function __construct()
    {
    }

    public function tables() {
        $tables = Board::all();
        $areas = Area::all();
        return view('order.tables', compact('tables', 'areas'));
    }

    public function loadTableByAreaAjax(Request $request){
        $areaId = $request->get('area_id');
        if ($areaId != 0) {
            $tables = Board::where('area_id', $areaId)->get();
        } else {
            $tables = Board::all();
        }
        return response()->json(['tables' => $tables], 200);
    }

    public function tableDetail($tableId) {
        $table = Board::find($tableId);
        if (!$table) {
            abort(404);
        }

        // Check if have at least 1 order in this table status = 1
        $orderFood = OrderFood::where('board_id', $tableId)
            ->where('status', OrderFood::NOT_CHECKOUT)->first();

        if ($orderFood == null) {
            return redirect()->route('order.tables.foods',[
                'tableId' => $tableId,
            ]);
        }

        $oderFoods = DB::select('select orderFood.food_id, f.name, sum(orderFood.order_quantity - orderFood.waiter_quantity) as total_order_quantity
from order_foods as orderFood
join food f on orderFood.food_id = f.id
where orderFood.board_id = :board_id and status = :status
group by orderFood.food_id', [
            'board_id' => $tableId,
            'status' => OrderFood::NOT_CHECKOUT,
        ]);

        return view('order.table-detail', [
            'board' => $table,
            'orderFoods' => $oderFoods,
        ]);
    }

    public function checkout()
    {
        $input = Input::all();
        $rules = [
            'board_id' => 'required',
        ];

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return Response::json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }

        $board = Board::find($input['board_id']);
        if (!$board) {
            return Response::json([
                'success' => false,
                'message' => ['The board is not found'],
            ]);
        }
        // update status board
        $board->status = Board::STATUS['CHECKOUT'];
        $board->save();

        $oderFoods = OrderFood::where('status', '=', OrderFood::NOT_CHECKOUT)
            ->where('board_id', '=', $input['board_id'])->get();

        $listIdsFoodNotRelease = [];
        $kitChenIDs = [];
        $foodIDs = [];
        foreach ($oderFoods as $oderFood) {
            if ($oderFood->order_quantity > $oderFood->kitchen_quantity) {
                $oderFood->origin_order_quantity = $oderFood->order_quantity;
                $oderFood->order_quantity = $oderFood->kitchen_quantity;
                $listIdsFoodNotRelease[] = $oderFood->id;
                if (!in_array($oderFood->kitchen_id, $kitChenIDs)) {
                    $kitChenIDs[] = $oderFood->kitchen_id;
                }

                if (!in_array($oderFood->food_id, $foodIDs)) {
                    $foodIDs[] = $oderFood->food_id;
                }

            }
            $oderFood->status = OrderFood::DID_CHECKOUT;
            $oderFood->save();
        }

        return Response::json([
            'success' => true,
            'data' => [
                'title' => 'Checkout',
                'body' => 'Checkout '. $board->name,
                'target_type' => 'checkout_order',
                'target_value' => $input['board_id'],
                'order_food_ids' => $listIdsFoodNotRelease,
                'kitchenIDs' => $kitChenIDs,
                'foodIDs' => $foodIDs,
            ]
        ]);
    }
    public function listFoods($tableId) {
        $table = Board::find($tableId);
        if (!$table) {
            abort(404);
        }
        $foods = Food::all();
        $categories = FoodCategory::all();
        return view('order.list-food', [
            'board' => $table,
            'foods' => $foods,
            'categories' => $categories,
        ]);
    }

    public function getFoodDetail(Request $request) {
        $foodId = $request->food_id;
        $food = Food::find($foodId);
        $foodModal = view('partials.modal_food_detail', compact('food'))->render();
        return  ['html' => $foodModal];
    }

    public function sendNotification(Request $request) {
        $user = User::find(1);
        if ($user) {
            dispatch(new PushNotification($user->firebase_web_key, [
                'title' => 'test',
                'body' => 'Bạn có một đơn order mới',
                'content' => 'Content',
                'target_type' => 'order',
                'target_value' => 'order_id'
            ]));
            return response()->json(['status' => 'OK', 'user' => $user], 200);
        }
        return response()->json(['status' => 'Fail', 'user' => $user], 200);
    }

    // Create Order
    public function createOrder()
    {
        $data = \Input::all();
        $rules = [
            'board_id' => 'required',
            'foods' => 'required',
        ];
        $validator = \Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success'=>false,
                'message'=>$validator->messages(),
            ]);
        }

        $user = \Auth::user();
        $foods = $data['foods'];
        $boardID = $data['board_id'];

        $kitchenIDs = [];
        $listOrderId = [];

        foreach ($foods as $food) {
            $foodObj = Food::where('id', $food['id'])->first();
            if ($foodObj == null) {
                continue;
            }
            $orderFood = new OrderFood();
            $orderFood->user_order_id = $user->id;
            $orderFood->food_id = $foodObj->id;
            $orderFood->board_id = $boardID;
            $orderFood->kitchen_id = $foodObj->kitchen_id;
            $orderFood->order_quantity = $food['qty'];
            $orderFood->note = isset($food['note']) ? trim($food['note']) : '';

            if ($orderFood->save()) {
                $listOrderId[] = $orderFood->id;
                if (!in_array($foodObj->kitchen_id, $kitchenIDs)) {
                    $kitchenIDs[] = intval($foodObj->kitchen_id);
                }
            }
        }

        $board = Board::find($data['board_id']);
        if ($board->status !== Board::STATUS['BUSY']) {
            $board->status = Board::STATUS['BUSY'];
            $board->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                    'title' => 'Order #'. implode(',', $listOrderId),
                    'body' => 'Bạn có một đơn order mới '. $board->name,
                    'target_type' => 'new_order',
                    'target_value' => $data['board_id'],
                    'content' => $listOrderId,
                    'kitchenIDs' => $kitchenIDs,
                ]
        ]);
    }
}
