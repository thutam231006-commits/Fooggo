<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Food;
use App\Services\FoodCustomizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WebCartController extends Controller
{
    public function show(Request $request): View
    {
        $cart = $request->user()->cart()->firstOrCreate();
        $cart->load('items.food');

        return view('cart.show', [
            'cart' => $cart,
            'total' => $cart->items->sum(fn ($item) => $item->quantity * ($item->unit_price ?? $item->food->price)),
            'hasInvalidItems' => $cart->items->contains(fn ($item) => ! $item->food->is_available || $item->food->stock < $item->quantity),
        ]);
    }

    public function store(Request $request, FoodCustomizationService $customizations): RedirectResponse
    {
        $data = $request->validate([
            'food_id' => ['required', 'integer', 'exists:foods,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'rice_type' => ['nullable', 'in:regular,garlic,brown'],
            'extras' => ['nullable', 'array', 'max:5'],
            'extras.*' => ['in:egg,meatloaf,soup,vegetables,extra_rice'],
            'sauce' => ['nullable', 'in:default,spicy,mild'],
            'spice_level' => ['nullable', 'in:none,medium,hot'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);
        $food = Food::findOrFail($data['food_id']);

        if (! $food->is_available || $food->stock < $data['quantity']) {
            throw ValidationException::withMessages(['quantity' => 'Món ăn không còn đủ số lượng.']);
        }

        $cart = $request->user()->cart()->firstOrCreate();
        $options = $customizations->normalize($data);
        $signature = $customizations->signature($options);
        $cartFoodQuantity = (int) $cart->items()->where('food_id', $food->id)->sum('quantity') + $data['quantity'];
        if ($cartFoodQuantity > $food->stock) {
            throw ValidationException::withMessages(['quantity' => 'Tổng số lượng các tùy chọn của món này vượt quá tồn kho.']);
        }
        $item = $cart->items()->firstOrNew(['food_id' => $food->id, 'option_signature' => $signature]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $data['quantity'];
        $item->options = $options;
        $item->unit_price = $customizations->unitPrice($food, $options);

        if ($item->quantity > $food->stock) {
            throw ValidationException::withMessages(['quantity' => 'Tổng số lượng trong giỏ vượt quá tồn kho.']);
        }

        $item->save();

        $route = $request->input('redirect_to') === 'menu' ? 'home' : 'cart.show';

        return redirect()->route($route)->with('status', "Đã thêm {$food->name} vào giỏ hàng.");
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        $otherQuantity = (int) $cartItem->cart->items()->where('food_id', $cartItem->food_id)->whereKeyNot($cartItem->id)->sum('quantity');
        if ($otherQuantity + $data['quantity'] > $cartItem->food->stock) {
            throw ValidationException::withMessages(['quantity' => 'Số lượng vượt quá tồn kho hiện tại.']);
        }

        $cartItem->update($data);

        return back()->with('status', 'Đã cập nhật giỏ hàng.');
    }

    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);
        $cartItem->delete();

        return back()->with('status', 'Đã xóa món khỏi giỏ hàng.');
    }

    private function authorizeItem(Request $request, CartItem $cartItem): void
    {
        abort_unless($cartItem->cart->user_id === $request->user()->id, 403);
    }
}
