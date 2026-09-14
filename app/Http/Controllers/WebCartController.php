<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Food;
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
            'total' => $cart->items->sum(fn ($item) => $item->quantity * $item->food->price),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'food_id' => ['required', 'integer', 'exists:foods,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);
        $food = Food::findOrFail($data['food_id']);

        if (! $food->is_available || $food->stock < $data['quantity']) {
            throw ValidationException::withMessages(['quantity' => 'Món ăn không còn đủ số lượng.']);
        }

        $cart = $request->user()->cart()->firstOrCreate();
        $item = $cart->items()->firstOrNew(['food_id' => $food->id]);
        $item->quantity = ($item->exists ? $item->quantity : 0) + $data['quantity'];

        if ($item->quantity > $food->stock) {
            throw ValidationException::withMessages(['quantity' => 'Tổng số lượng trong giỏ vượt quá tồn kho.']);
        }

        $item->save();

        return redirect()->route('cart.show')->with('status', "Đã thêm {$food->name} vào giỏ hàng.");
    }

    public function update(Request $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($request, $cartItem);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        if ($data['quantity'] > $cartItem->food->stock) {
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
