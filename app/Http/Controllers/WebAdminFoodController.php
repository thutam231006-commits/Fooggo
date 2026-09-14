<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebAdminFoodController extends Controller
{
    public function index(Request $request): View
    {
        $foods = Food::query()
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($foodQuery) => $foodQuery
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('category', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_available', $request->string('status')->value() === 'available'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.foods.index', compact('foods'));
    }

    public function create(): View
    {
        return view('admin.foods.form', ['food' => new Food]);
    }

    public function store(Request $request): RedirectResponse
    {
        $food = Food::create($this->validated($request));

        return redirect()->route('admin.foods.edit', $food)->with('status', 'Đã thêm món ăn mới.');
    }

    public function edit(Food $food): View
    {
        return view('admin.foods.form', compact('food'));
    }

    public function update(Request $request, Food $food): RedirectResponse
    {
        $food->update($this->validated($request));

        return back()->with('status', 'Đã cập nhật món ăn.');
    }

    public function destroy(Food $food): RedirectResponse
    {
        $food->update(['is_available' => false]);

        return redirect()->route('admin.foods.index')->with('status', 'Món ăn đã chuyển sang trạng thái ngừng bán.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_available' => ['required', 'boolean'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ]);
    }
}
