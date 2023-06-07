<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $user = \Auth::user();
       $role = $user->role;
       switch ($role) {
           case User::ORDER_ROLE:
               return redirect(route('order.tables'));
           case User::KITCHEN_ROLE:
               return redirect(route('kitchen.by-order'));
           case User::GENERAL_KITCHEN_ROLE:
               return redirect(route('general-kitchen.by-order'));
           case User::WAITER_ROLE:
               return redirect(route('waiter.home'));
           case User::EXPORT_ROLE:
               return redirect(route('export'));
       }

        return redirect(route('order.tables'));
    }


    public function updateFireBaseToken(Request $request) {
        $user = User::find($request->user_id);
        $user->firebase_web_key = $request->firebase_token;
        $user->save();
        return response()->json(['status' => 'OK', 'user' => $user], 200);
    }
}
