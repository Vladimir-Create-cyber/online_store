<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Список отзывов с фильтрацией по статусу.
     */
    public function index(Request $request)
    {
        $status = $request->input('status'); // approved | pending | null

        $reviews = Review::with(['product', 'user'])
            ->when($status === 'approved', fn($q) => $q->where('is_approved', true))
            ->when($status === 'pending', fn($q) => $q->where('is_approved', false))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.reviews.index', compact('reviews', 'status'));
    }

    /**
     * Одобрение отзыва.
     */
    public function approve(Request $request, Review $review)
    {
        $review->update(['is_approved' => true]);

        // Возврат JSON, если AJAX-запрос
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Отзыв одобрен.');
    }

    /**
     * Удаление отзыва (soft delete).
     */
    public function destroy(Request $request, Review $review)
    {
        $review->delete();

        // Возврат JSON, если AJAX-запрос
        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Отзыв удалён.');
    }
}
