<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Создаёт отзыв пользователя для товара.
     */
    public function store(Request $request, Product $product)
    {
        $user = Auth::user();

        if ($product->reviews()->where('user_id', $user->id)->exists()) {
            return back()->with('error', __('ui.review_already_exists'));
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'review' => $validated['review'],
            'is_approved' => false,
        ]);

        return back()->with('success', __('ui.review_thanks_pending_approval'));
    }
}
