<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Food;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->user()->cart()->firstOrCreate();
        $cart->load('items.food');

        return response()->json([
            'data' => $cart,
            'total' => $cart->items->sum(fn ($item) => $item->quantity * $item->food->price),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'food_id' => ['required', 'exists:foods,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $food = Food::findOrFail($data['food_id']);
        abort_if(! $food->is_available || $food->stock === 0, 422, "{$food->name} - Hết sản phẩm.");
        abort_if($food->stock < $data['quantity'], 422, "{$food->name} chỉ còn {$food->stock} phần.");

        $cart = $request->user()->cart()->firstOrCreate();
        $item = $cart->items()->firstOrNew(['food_id' => $food->id]);
        $item->quantity = $item->exists ? $item->quantity + $data['quantity'] : $data['quantity'];
        abort_if($item->quantity > $food->stock, 422, 'Số lượng trong giỏ vượt quá tồn kho.');
        $item->save();

        return response()->json($item->load('food'), 201);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeItem($request, $cartItem);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        abort_if($data['quantity'] > $cartItem->food->stock, 422, 'Số lượng trong giỏ vượt quá tồn kho.');
        $cartItem->update($data);

        return $cartItem->load('food');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $this->authorizeItem($request, $cartItem);
        $cartItem->delete();

        return response()->noContent();
    }

    public function clear(Request $request)
    {
        $request->user()->cart?->items()->delete();

        return response()->noContent();
    }

    private function authorizeItem(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 403, 'Bạn không có quyền sửa mục này.');
    }
}
