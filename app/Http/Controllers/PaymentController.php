<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderWorkflowService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Order $order, OrderWorkflowService $workflow)
    {
        $result = $workflow->pay($request->user(), $order);

        return response()->json($result);
    }
}
