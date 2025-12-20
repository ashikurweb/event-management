<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::query()
            ->with(['user', 'event'])
            ->latest();

        // Search by order number or buyer name/email
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('order_number', 'like', "%{$request->search}%")
                  ->orWhere('buyer_first_name', 'like', "%{$request->search}%")
                  ->orWhere('buyer_last_name', 'like', "%{$request->search}%")
                  ->orWhere('buyer_email', 'like', "%{$request->search}%");
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by event
        if ($request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->paginate($request->per_page ?? 10)->withQueryString();

        return Inertia::render('Dashboard/Orders/Index', [
            'orders' => OrderResource::collection($orders),
            'filters' => $request->only(['search', 'status', 'event_id', 'date_from', 'date_to', 'per_page']),
            'events' => Event::select('id', 'title')->get(),
            'statuses' => ['pending', 'completed', 'failed', 'cancelled', 'refunded'],
        ]);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        $order->load(['user', 'event', 'items.ticketType', 'payments', 'promoCode']);

        return Inertia::render('Dashboard/Orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    /**
     * Update the specified order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,failed,cancelled,refunded',
        ]);

        $order->update($validated);

        return back()->with('success', "Order status updated to {$validated['status']}.");
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return redirect()->route('orders.index')
            ->with('success', 'Order soft deleted successfully.');
    }
}
