<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string', // ✅ добавлено
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated); // создаст name, slug, description

        return redirect()->route('admin.categories.index')->with('success', 'Категория добавлена!');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string', // ✅ добавлено
        ]);

        $category->name = $validated['name'];
        $category->slug = Str::slug($validated['name']);
        $category->description = $validated['description']; // ✅ добавлено
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Категория обновлена!');
    }

    public function destroy(Category $category)
    {
        // Дополнительно: перед удалением проверь, не используются ли товары с этой категорией
        if ($category->products()->exists()) {
            return back()->with('error', 'Нельзя удалить категорию, к которой привязаны товары.');
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Категория удалена!');
    }
}
