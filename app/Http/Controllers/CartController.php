<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Food;
use App\Services\FoodCustomizationService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->user()->cart()->firstOrCreate();
        $cart->load('items.food');

        return response()->json([
            'data' => $cart,
            'total' => $cart->items->sum(fn ($item) => $item->quantity * ($item->unit_price ?? $item->food->price)),
        ]);
    }

    public function store(Request $request, FoodCustomizationService $customizations)
    {
        $data = $request->validate([
            'food_id' => ['required', 'exists:foods,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'rice_type' => ['nullable', 'in:regular,garlic,brown'],
            'extras' => ['nullable', 'array', 'max:5'],
            'extras.*' => ['in:egg,meatloaf,soup,vegetables,extra_rice'],
            'sauce' => ['nullable', 'in:default,spicy,mild'],
            'spice_level' => ['nullable', 'in:none,medium,hot'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        $food = Food::findOrFail($data['food_id']);
        abort_if(! $food->is_available || $food->stock === 0, 422, "{$food->name} - Hết sản phẩm.");
        abort_if($food->stock < $data['quantity'], 422, "{$food->name} chỉ còn {$food->stock} phần.");

        $cart = $request->user()->cart()->firstOrCreate();
        $options = $customizations->normalize($data);
        $signature = $customizations->signature($options);
        $cartFoodQuantity = (int) $cart->items()->where('food_id', $food->id)->sum('quantity') + $data['quantity'];
        abort_if($cartFoodQuantity > $food->stock, 422, 'Tổng số lượng các tùy chọn của món này vượt quá tồn kho.');
        $item = $cart->items()->firstOrNew(['food_id' => $food->id, 'option_signature' => $signature]);
        $item->quantity = $item->exists ? $item->quantity + $data['quantity'] : $data['quantity'];
        $item->options = $options;
        $item->unit_price = $customizations->unitPrice($food, $options);
        abort_if($item->quantity > $food->stock, 422, 'Số lượng trong giỏ vượt quá tồn kho.');
        $item->save();

        return response()->json($item->load('food'), 201);
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeItem($request, $cartItem);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);
        $otherQuantity = (int) $cartItem->cart->items()->where('food_id', $cartItem->food_id)->whereKeyNot($cartItem->id)->sum('quantity');
        abort_if($otherQuantity + $data['quantity'] > $cartItem->food->stock, 422, 'Số lượng trong giỏ vượt quá tồn kho.');
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
