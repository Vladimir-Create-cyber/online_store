<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $user = Auth::user();

        // Проверка: уже оставлял отзыв?
        if ($product->reviews()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Вы уже оставили отзыв для этого товара.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'is_approved' => false, // ожидает модерации
        ]);

        return back()->with('success', 'Спасибо за отзыв! Он будет опубликован после проверки.');
    }
}
