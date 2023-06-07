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


class KitchenController extends Controller
{
    public function kitchenHomePageByOrder()
    {
        $view = [];
        $kitchenID = User::getCurrentUser()->kitchen_id;
        $orderFoods = OrderFood::with('food')
            ->with('board')
            ->where('kitchen_id', '=', $kitchenID)
            ->whereRaw('order_quantity > kitchen_quantity')->orderBy('created_at', 'ASC')->get();

        $view['foods'] = [];
        $view['orderFoods'] = $orderFoods;
        return view('kitchen.orders', $view);
    }

    public function kitchenHomePageByFood()
    {
        $oderFoods = DB::select('select orderFood.food_id, f.name, sum(orderFood.order_quantity - orderFood.kitchen_quantity) as total_order_quantity
from order_foods as orderFood
join food f on orderFood.food_id = f.id
where orderFood.kitchen_id = :kitchen_id and orderFood.order_quantity > orderFood.kitchen_quantity
group by orderFood.food_id', [
    'kitchen_id' => Auth::user()->kitchen_id,
        ]);


        $view = [];
        $view['orderFoods'] = $oderFoods;
        return view('kitchen.foods', $view);
    }

    public function kitchenHomePageByBoard()
    {
        $user = \Auth::user();
        $queryListBoardIds = DB::select('SELECT board_id FROM order_foods WHERE order_foods.kitchen_id = :kitchenID and order_foods.order_quantity > order_foods.kitchen_quantity GROUP BY board_id',
        [
            'kitchenID' => $user->kitchen_id,
        ]);

        if (isset($queryListBoardIds) && count($queryListBoardIds) > 0) {
            $listBoardId = array_pluck($queryListBoardIds, 'board_id');
            $boards = Board::whereIn('id', $listBoardId)->get();
            return view('kitchen.boards', compact('boards', 'listBoardId'));
        } else {
            return view('kitchen.boards', [
                'boards' => [],
                'listBoardId' => [],
            ]);
        }

    }

    public function loadBoardAjax(Request $request) {
        $board = Board::where('id', $request->board_id)->first();
        $html = View('kitchen.partials.board', compact('board'))->render();
        return ['html' => $html, 'board_id' => $request->board_id ];
    }

    public function loadFoodByBoardAjax(Request $request) {
        $user = \Auth::user();
        $orderFoods = OrderFood::where('board_id', $request->board_id)
            ->where('kitchen_id', '=', $user->kitchen_id)
            ->whereRaw('order_quantity > kitchen_quantity')->get();
        $html = view('kitchen.partials.list_food', compact('orderFoods'))->render();
        return ['html' => $html, 'count_food' => count($orderFoods)];
    }

    public function loadFoodByAjax(Request $request) {
        $input = Input::all();
        $orderFood = OrderFood::with('food')
            ->with('board')
            ->where('id', $request->order_food_id)
            ->where('kitchen_id', '=', \Auth::user()->kitchen_id)
            ->first();
        if (!$orderFood) {
            return ['html' => ''];
        }
        $path = isset($input['path']) ? $input['path'] : '';
        $html = view('kitchen.partials.item', [
            'orderFood' => $orderFood,
            'path' => $path,
        ])->render();
        return ['html' => $html];
    }

    public function updateFoodOrder(Request $request) {
        try {
            $orderFood = OrderFood::find($request->food_order_id);
            if ($orderFood) {
                $kitchen_quantity = $request->kitchen_quantity;
                $orderFood->kitchen_quantity += $kitchen_quantity;
                if ($orderFood->kitchen_quantity > $orderFood->order_quantity) {
                    $kitchen_quantity = $orderFood->order_quantity - $orderFood->kitchen_quantity;
                    $orderFood->kitchen_quantity = $orderFood->order_quantity;
                }
                if ($orderFood->kitchen_release_at == null) {
                    $orderFood->kitchen_release_at = date('Y-m-d H:i:s', time());
                }
                $orderFood->save();

                $waiterFood = new WaiterFood();
                $waiterFood->order_food_id = $orderFood->id;
                $waiterFood->food_id = $orderFood->food_id;
                $waiterFood->kitchen_id = $orderFood->kitchen_id;
                $waiterFood->board_id = $orderFood->board_id;
                $waiterFood->kitchen_quantity = $kitchen_quantity;
                $waiterFood->note = $orderFood->note;
                $waiterFood->save();

                $data = $this->pushNotificationToWaiter($orderFood->board->name,
                    $orderFood->food->name, $kitchen_quantity,
                    $waiterFood->id);
                return response()->json(['error' => false, 'board_id' => $orderFood->board_id, 'data' => $data ], 200);
            }
            return response()->json(['error' => true ], 200);
        }catch (\Exception $exception) {
            return response()->json(['error' => false, 'message' => $exception->getMessage() ], 200);
        }

    }

    public function releaseByOrder()
    {
        $input = Input::all();
        $rules = [
            'order_food_id' => 'required',
            'quantity' => 'required',
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages(),
            ]);
        }

        $orderFood = OrderFood::find($input['order_food_id']);
        if (!$orderFood) {
            return response()->json([
                'success' => false,
                'message' => ['The order is not found']
            ]);
        }

        if (isset($input['is_all']) && boolval($input['is_all'])) {
            $orderFood->kitchen_quantity = $orderFood->order_quantity;
        } else {
            if ($orderFood->kitchen_quantity + intval($input['quantity']) > $orderFood->order_quantity) {
                return response()->json([
                    'success' => false,
                    'message' => ['The quantity is invalid']
                ]);
            }
            $orderFood->kitchen_quantity += intval($input['quantity']);
        }

        if ($orderFood->kitchen_release_at == null) {
            $orderFood->kitchen_release_at = date('Y-m-d H:i:s', time());
        }

        if ($orderFood->save()) {

            $waiterFood = new WaiterFood();
            $waiterFood->order_food_id = $orderFood->id;
            $waiterFood->food_id = $orderFood->food_id;
            $waiterFood->kitchen_id = $orderFood->kitchen_id;
            $waiterFood->board_id = $orderFood->board_id;

            if (isset($input['is_all']) && boolval($input['is_all'])) {
                $waiterFood->kitchen_quantity = $orderFood->order_quantity;
            } else {
                $waiterFood->kitchen_quantity = intval($input['quantity']);
            }
            $waiterFood->note = $orderFood->note;
            $waiterFood->save();


           $data = $this->pushNotificationToWaiter($orderFood->board->name,
               $orderFood->food->name,
               $input['quantity'],
               $waiterFood->id);
            return Response::json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return Response::json([
            'success' => false,
            'message' => ['Have error when save order food kitchen_quantity']
        ]);
    }

    public function releaseAllByFood()
    {
        $input = Input::all();
        $rules = [
            'food_id' => 'required',
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()
            ]);
        }

        $kitchenID = Auth::user()->kitchen_id;
        $orderFoods = OrderFood::with('board')->with('food')
            ->where('kitchen_id', $kitchenID)
            ->where('food_id', $input['food_id'])
            ->whereRaw('order_quantity > kitchen_quantity')->get();


        $dataPushNotification = [];
        foreach ($orderFoods as $orderFood) {
            if ($orderFood->food == null || $orderFood->board == null) {
                continue;
            }
            $kitchenQuantity = $orderFood->order_quantity - $orderFood->kitchen_quantity;
            $orderFood->kitchen_quantity = $orderFood->order_quantity;

            if ($orderFood->kitchen_release_at == null) {
                $orderFood->kitchen_release_at = date('Y-m-d H:i:s', time());
            }

            $orderFood->save();

            $waiterFood = new WaiterFood();
            $waiterFood->order_food_id = $orderFood->id;
            $waiterFood->food_id = $orderFood->food_id;
            $waiterFood->kitchen_id = $orderFood->kitchen_id;
            $waiterFood->board_id = $orderFood->board_id;
            $waiterFood->kitchen_quantity = $kitchenQuantity;
            $waiterFood->note = $orderFood->note;
            $waiterFood->save();

            $dataPushNotification[] = $this->pushNotificationToWaiter(
                $orderFood->board->name,
                $orderFood->food->name,
                $kitchenQuantity,
                $waiterFood->id);
        }

        return Response::json([
            'success' => true,
            'data' => $dataPushNotification,
        ]);
    }


    public function releasePartialByFood()
    {
        $dataPushNotification = [];
        $input = Input::all();
        $rules = [
            'food_id' => 'required',
            'quantity' => 'required',
        ];

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()
            ]);
        }

        $releaseQuantity = intval($input['quantity']);
        $kitchenID = Auth::user()->kitchen_id;
        $orderFoods = OrderFood::where('kitchen_id', $kitchenID)
            ->where('food_id', $input['food_id'])->whereRaw('order_quantity > kitchen_quantity')
            ->orderBy('created_at', 'ASC')
            ->get();


        foreach ($orderFoods as $orderFood) {
            if ($releaseQuantity <= 0) {
                break;
            }

            $needQuantity = $orderFood->order_quantity - $orderFood->kitchen_quantity;
            $kitchenQuantity = $needQuantity < $releaseQuantity ? $needQuantity :
                $releaseQuantity;

            $orderFood->kitchen_quantity = $orderFood->kitchen_quantity + $kitchenQuantity;
            if ($orderFood->kitchen_release_at == null) {
                $orderFood->kitchen_release_at = date('Y-m-d H:i:s', time());
            }

            if ($orderFood->save()) {

                $waiterFood = new WaiterFood();
                $waiterFood->order_food_id = $orderFood->id;
                $waiterFood->food_id = $orderFood->food_id;
                $waiterFood->kitchen_id = $orderFood->kitchen_id;
                $waiterFood->board_id = $orderFood->board_id;
                $waiterFood->kitchen_quantity = $kitchenQuantity;
                $waiterFood->note = $orderFood->note;
                $waiterFood->save();

                $dataPushNotification[] = $this->pushNotificationToWaiter(
                    $orderFood->board->name,
                    $orderFood->food->name,
                    $kitchenQuantity,
                    $waiterFood->id);

                $releaseQuantity -= $kitchenQuantity;
            }
        }

        return Response::json([
            'success' => true,
            'data' => $dataPushNotification,
        ]);
    }

    public function orderFoodDetail($id)
    {
        $orderFood = OrderFood::with('food')->where('id', '=', $id)
        ->where('kitchen_id', \Auth::user()->kitchen_id)->first();
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
