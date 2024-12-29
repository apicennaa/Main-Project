<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\CleanerController;


class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'cleaner', 'service'])
                      ->latest()
                      ->paginate(10);
    
        if ($orders->isEmpty()) {
            return view('user.order.index')->with('orders', collect());
        }
    
        return view('user.order.index', compact('orders'));
    }
    

    public function create(Request $request)
    {
        $serviceId = $request->query('service_id'); // Ambil service_id dari query string
        $service = Service::find($serviceId);
    
        if (!$service) {
            abort(404, 'Service not found'); // Tampilkan error jika service tidak ditemukan
        }
    
        return view('user.order.create', compact('service'));
    }
    
    
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'service_id' => 'required|exists:services,id',
    //         'name' => 'required|string|max:255',
    //         'phone' => 'required|string|max:20',
    //         'address' => 'required|string',
    //         'service_date' => 'required|date',
    //         'service_time' => 'required',
    //         'notes' => 'nullable|string'
    //     ]);

    //     $order = Order::create([
    //         'user_id' => auth()->id(),
    //         'service_id' => $request->service_id,
    //         'customer_name' => $request->name,
    //         'customer_phone' => $request->phone,
    //         'service_address' => $request->address,
    //         'service_date' => $request->service_date,
    //         'service_time' => $request->service_time,
    //         'notes' => $request->notes,
    //         'status' => 'pending'
    //     ]);

    //     return redirect()->route('payment.create', $order->id);
    // }
    
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'service_date' => 'required|date',
            'service_time' => 'required',
            'notes' => 'nullable|string'
        ]);

        $order = Order::create([
            'user_id' => auth()->id(),
            'service_id' => $request->service_id,
            'customer_name' => $request->name,
            'customer_phone' => $request->phone,
            'service_address' => $request->address,
            'service_date' => $request->service_date,
            'service_time' => $request->service_time,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        return redirect()->route('payment.create', ['order_id' => $order->id]);
    }
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'service_id' => 'required|exists:services,id',
    //         'schedule_time' => 'required|date',
    //         'name' => 'required|string|max:255',
    //         'phone' => 'required|string|max:20',
    //         'address' => 'required|string',
    //         'service_date' => 'required|date',
    //         'service_time' => 'required',
    //         'notes' => 'nullable|string',
    //     ]);

    //     $order = Order::create([
    //         'user_id' => auth()->id(),
    //         'service_id' => $request->service_id,
    //         'customer_name' => $request->name,
    //         'customer_phone' => $request->phone,
    //         'service_address' => $request->address,
    //         'service_date' => $request->service_date,
    //         'service_time' => $request->service_time,
    //         'schedule_time' => $request->schedule_time, // Tambahkan nilai ini
    //         'notes' => $request->notes,
    //         'status' => 'pending',
    //     ]);
    //     // Redirect ke payment.create dengan order_id
    //     return redirect()->route('payment.create', ['order_id' => $order->id])
    //         ->with('success', 'Order created successfully. Proceed with payment.');
    // }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'service_id' => 'required|exists:services,id',
    //         'schedule_time' => 'required|date',
    //     ]);

    //     // Ambil layanan
    //     $service = Service::findOrFail($request->service_id);

    //     // Cari cleaner dengan tugas paling sedikit
    //     $cleaner = Cleaner::withCount(['orders' => function ($query) {
    //         $query->whereIn('status', ['pending', 'in_progress']);
    //     }])->orderBy('orders_count', 'asc')->first();

    //     if (!$cleaner) {
    //         return back()->withErrors(['message' => 'No available cleaners at the moment.']);
    //     }

    //     // Buat pesanan
    //     $order = Order::create([
    //         'user_id' => auth()->id(),
    //         'service_id' => $request->service_id,
    //         'cleaner_id' => $cleaner->id, // Tetapkan cleaner
    //         'schedule_time' => $request->schedule_time,
    //         'status' => 'pending',
    //         'total_price' => $service->service_price,
    //     ]);

    //     return redirect()->route('user.order.index')->with('success', 'Order created successfully!');
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return redirect()->route('user.order.index')->with('success', 'Order status updated successfully!');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('user.order.index')->with('success', 'Order deleted successfully!');
    }
}