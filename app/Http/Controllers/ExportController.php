<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function exportToExcel(Request $request)
    {
        $stat_day = $request->input('start_date');
        $end_day = $request->input('end_date');
        $data = DB::select("SELECT food.name AS 'Mon an',
            order_foods.order_quantity AS 'So luong order',
            order_foods.kitchen_quantity AS 'So luong bep don',
            order_foods.waiter_quantity AS 'SL da ra ban',
            CONVERT_TZ(order_foods.created_at, '+00:00', '+07:00') AS 'Tgian order',
            boards.name AS 'Ban'
            FROM order_foods
            JOIN food ON order_foods.food_id = food.id
            JOIN boards ON order_foods.board_id = boards.id
            WHERE order_foods.created_at BETWEEN ? AND ?
            ORDER BY order_foods.created_at DESC",[$stat_day, $end_day]);

        //rrturn view('orders.index', compact('orders'));
        return Excel::download(new OrdersExport($data), 'orderexport '.$stat_day.' '.$end_day.'.xlsx');
    }
}