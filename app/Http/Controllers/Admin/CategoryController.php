<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Отображает список категорий.
     */
    public function index()
    {
        $categories = Category::paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Отображает форму создания категории.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Создаёт новую категорию.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Категория добавлена!');
    }

    /**
     * Отображает форму редактирования категории.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Обновляет существующую категорию.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);

        $category->name = $validated['name'];
        $category->slug = Str::slug($validated['name']);
        $category->description = $validated['description'];
        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Категория обновлена!');
    }

    /**
     * Удаляет категорию, если к ней не привязаны товары.
     */
    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Нельзя удалить категорию, к которой привязаны товары.');
        }

        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Категория удалена!');
    }
}
