<?php

namespace App\Http\Controllers;

use App\OrderFood;
use App\WaiterFood;
use Auth;
use Illuminate\Support\Facades\Request;
use Validator;
use Input;
use Response;

class WaiterController extends Controller
{
    public function getWaiterPage()
    {
        $view = [];
        $orderFoods = WaiterFood::with('food')
            ->with('board')
            ->where('kitchen_quantity', '>', 0)
            ->whereRaw('kitchen_quantity > waiter_quantity')->orderBy('created_at', 'ASC')->get();

        $view['orderFoods'] = $orderFoods;
        return view('waiter.foods', $view);
    }

    public function releaseAllByFood()
    {
        $input = Input::all();
        $rules = [
            'id' => 'required',
        ];
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->messages()
            ]);
        }

        $waiterFood = WaiterFood::find($input['id']);
        $waiterFood->waiter_quantity = $waiterFood->kitchen_quantity;
        $waiterFood->save();

        $orderFood = OrderFood::find($waiterFood->order_food_id);
        $orderFood->waiter_quantity = $orderFood->kitchen_quantity;
        $orderFood->user_waiter_id = Auth::user()->id;
        $orderFood->save();

        return Response::json([
            'success' => true,
        ]);
    }


    public function releasePartialByFood()
    {
        $input = Input::all();
        $rules = [
            'id' => 'required',
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
        $waiterFood = WaiterFood::find($input['id']);

        if (!$waiterFood) {
            return Response::json([
                'success'=>false,
                'message' => ['The order is not found']
            ]);
        }
        if ($waiterFood->waiter_quantity + $releaseQuantity > $waiterFood->kitchen_quantity) {
            return Response::json([
                'success'=>false,
                'message' => ['The quantity is invalid']
            ]);
        }

        $waiterFood->waiter_quantity += $releaseQuantity;
        $waiterFood->save();

        $orderFood = OrderFood::find($waiterFood->order_food_id);
        if (!$orderFood) {
            return Response::json([
                'success'=>false,
                'message' => ['The order is not found']
            ]);
        }
        if ($orderFood->waiter_quantity + $releaseQuantity > $orderFood->kitchen_quantity) {
            return Response::json([
                'success'=>false,
                'message' => ['The quantity is invalid']
            ]);
        }

        $orderFood->waiter_quantity += $releaseQuantity;
        $orderFood->save();

        return Response::json([
            'success' => true,
        ]);
    }

    public function loadFood() {
        $input = Input::all();
        $orderFoodId = $input['order_food_id'];
        $item = WaiterFood::find($orderFoodId);
        if (!$item) {
            abort(404);
        }
        $html = view('waiter.partials.item', compact('item'))->render();
        return [
            'html' => $html,
            'data'=> $item,
        ];
    }
}
