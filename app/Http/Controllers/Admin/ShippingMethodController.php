<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShippingMethod;

class ShippingMethodController extends Controller
{
    /**
     * Показать список всех способов доставки.
     */
    public function index()
    {
        $shippingMethods = \App\Models\ShippingMethod::all();
        return view('admin.shipping_methods.index', compact('shippingMethods'));
    }

    /**
     * Показать форму создания нового способа доставки.
     */
    public function create()
    {
        return view('admin.shipping_methods.create');
    }

    /**
     * Сохранить новый способ доставки в базе данных.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ShippingMethod::create([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.shipping_methods.index')
            ->with('success', 'Способ доставки добавлен.');
    }


    /**
     * Показать форму редактирования способа доставки.
     */
    public function edit(ShippingMethod $shippingMethod)
    {
        return view('admin.shipping_methods.edit', compact('shippingMethod'));
    }

    /**
     * Обновить способ доставки в базе данных.
     */
    public function update(Request $request, ShippingMethod $shippingMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $shippingMethod->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.shipping_methods.index')
            ->with('success', 'Способ доставки обновлён.');
    }


    /**
     * Удалить способ доставки из базы данных.
     */
    public function destroy(ShippingMethod $shippingMethod)
    {
        $shippingMethod->delete();
        return back()->with('success', 'Способ доставки удалён.');
    }
}
