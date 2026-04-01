<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddress;
use App\Models\ShippingMethod;

class CartController extends Controller
{
    /**
     * Отображает корзину пользователя.
     */
    public function viewCart()
    {
        $cart = session()->get('cart', []);
        $unreadCount = $this->getUnreadCount();

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return view('cart.index', [
            'cart' => $cart,
            'unreadCount' => $unreadCount,
            'total' => $total,
        ]);
    }

    /**
     * Добавляет товар в корзину.
     */
    public function addToCart(Request $request, $productId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $quantity = $validated['quantity'];

        $product = Product::findOrFail($productId);

        if ($quantity > $product->stock) {
            return redirect()->back()->with('error', __('ui.not_enough_stock'));
        }

        $cart = session()->get('cart', []);

        $cart[$productId] = [
            'product_id' => $productId,
            'name' => $product->name,
            'price' => $product->final_price,
            'image' => $product->image,
            'quantity' => $quantity,
        ];

        session()->put('cart', $cart);

        return redirect()->back()->with('success', __('ui.product_added_to_cart'));
    }



    /**
     * Удаляет товар из корзины.
     */
    public function removeFromCart($productId)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
        }

        return redirect()->back()->with('success', __('ui.product_removed_from_cart'));
    }

    /**
     * Отображает форму оформления заказа.
     */
    public function showCheckoutForm()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', __('ui.cart_empty'));
        }

        $shippingMethods = ShippingMethod::where('is_active', true)->get();
        $unreadCount = $this->getUnreadCount();

        return view('cart.checkout', [
            'cart' => $cart,
            'shippingMethods' => $shippingMethods,
            'unreadCount' => $unreadCount,
        ]);
    }


    /**
     * Создаёт заказ из содержимого корзины.
     */
    public function completeOrder(Request $request)
    {
        $validated = $request->validate([
            'full_name'          => 'required|string|max:255',
            'phone'              => 'required|string|max:20',
            'address'            => 'required|string|max:255',
            'city'               => 'required|string|max:100',
            'country'            => 'nullable|string|max:100',
            'postal_code'        => 'required|string|max:20',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'payment_method'     => 'required|string',
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', __('ui.cart_empty'));
        }

        $shippingMethod = ShippingMethod::find($validated['shipping_method_id']);

        if (!$shippingMethod || !$shippingMethod->is_active) {
            return redirect()->back()->withErrors(['shipping_method_id' => __('ui.invalid_shipping_method')]);
        }

        $order = null;
        $total = 0;
        $messages = [];

        DB::transaction(function () use (&$order, &$total, &$messages, $validated, $shippingMethod, $cart) {
            $order = Order::create([
                'user_id'         => Auth::id(),
                'full_name'       => $validated['full_name'],
                'phone'           => $validated['phone'],
                'address'         => $validated['address'],
                'city'            => $validated['city'],
                'country'         => $validated['country'],
                'postal_code'     => $validated['postal_code'],
                'shipping_method' => $shippingMethod->name,
                'payment_method'  => $validated['payment_method'],
                'total'           => 0,
                'status'          => 'pending',
            ]);

            OrderAddress::create([
                'order_id'    => $order->id,
                'type'        => 'shipping',
                'full_name'   => $validated['full_name'],
                'phone'       => $validated['phone'],
                'address'     => $validated['address'],
                'city'        => $validated['city'],
                'country'     => $validated['country'],
                'postal_code' => $validated['postal_code'],
            ]);

            $productIds = array_keys($cart);
            $products = Product::whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cart as $productId => $item) {
                $product = $products->get((int) $productId);

                if (!$product) {
                    $messages[] = __('ui.product_not_found_skipped', ['id' => $productId]);
                    continue;
                }

                $orderedQty = (int) $item['quantity'];
                $availableQty = (int) $product->stock;

                if ($availableQty <= 0) {
                    $messages[] = __('ui.product_out_of_stock_skipped', ['name' => $product->name]);
                    continue;
                }

                $finalQty = min($orderedQty, $availableQty);
                if ($finalQty < $orderedQty) {
                    $messages[] = __('ui.product_qty_adjusted', ['name' => $product->name, 'ordered' => $orderedQty, 'added' => $finalQty]);
                }

                $unitPrice = (float) $product->final_price;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $finalQty,
                    'price'      => $unitPrice,
                ]);

                $product->decrement('stock', $finalQty);
                $total += $unitPrice * $finalQty;
            }

            $order->update(['total' => $total]);
        });

        session()->forget('cart');

        $successMessage = __('ui.order_successfully_created');
        if ($messages) {
            $successMessage .= ' ' . __('ui.please_note') . ': ' . implode(' ', $messages);
        }

        return redirect()->route('orders.show', $order->id)->with('success', $successMessage);
    }


}
