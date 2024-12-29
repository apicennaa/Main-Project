<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Order;
use App\Models\Service;

class CleanerController extends Controller
{
    public function dashboard()
    {
        $services = Service::where('cleaner_id', auth()->id())->get();

        // Data testimonial (tetap menggunakan data statis)
        $testimonials = [
            [
                'image' => 'images/say1.png',
                'name' => 'Jefri Nichol',
                'text' => 'Excellent service! My house has never been cleaner.'
            ],
            [
                'image' => 'images/say2.png',
                'name' => 'Natasha Rizky',
                'text' => 'Very professional and thorough. Highly recommended!'
            ],
            [
                'image' => 'images/say3.png',
                'name' => 'Jackson Kamela',
                'text' => 'Great attention to detail and friendly staff.'
            ]
        ];

        // Return view dengan data
        return view('user.dashboard', compact('services', 'testimonials'));
    }
    
    public function index()
    {
        $cleaner_id = auth()->id();
        
        $stats = [
            'total_orders' => Order::where('cleaner_id', $cleaner_id)->count(),
            'pending_tasks' => Order::where('cleaner_id', $cleaner_id)
                                  ->whereIn('status', ['pending', 'in_progress'])
                                  ->count(),
            'completed_tasks' => Order::where('cleaner_id', $cleaner_id)
                                    ->where('status', 'completed')
                                    ->count(),
        ];
    
        $orders = Order::where('cleaner_id', $cleaner_id)
                      ->with(['user', 'service'])
                      ->orderBy('schedule_time', 'desc')
                      ->paginate(10);
    
        // Pass the $services variable
        $services = Service::where('cleaner_id', auth()->id())->get();

        return view('cleaner.service.index', compact('stats', 'orders', 'services'));
    }
    
    public function createService()
    {
        return view('cleaner.service.create');
    }

    // Menyimpan layanan baru ke database
    public function storeService(Request $request)
    {
        // Validasi input
        $request->validate([
            'service_name' => 'required|string|max:255',
            'service_description' => 'required|string',
            'service_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'service_price' => 'required|numeric|min:0',
        ]);

        // Menyimpan gambar dan mendapatkan path-nya
        $imagePath = $request->file('service_image')->store('services', 'public');

        // Menyimpan layanan baru
        $service = new Service();
        $service->service_name = $request->service_name;
        $service->service_description = $request->service_description;
        $service->service_image = $imagePath;
        $service->service_price = $request->service_price;
        $service->cleaner_id = auth()->id(); // Mengasumsikan cleaner adalah user yang sedang login
        $service->save();

        // Redirect ke halaman tertentu setelah berhasil
        return redirect()->route('cleaner.service.index')->with('success', 'Service added successfully!');
    }

    public function editService($service_name)
    {
        $service = Service::where('service_name', $service_name)->first();
    
        if (!$service) {
            abort(404, 'Service not found');
        }
    
        return view('cleaner.service.edit', compact('service'));
    }
    

    // Memperbarui layanan
    public function updateService(Request $request, Service $service)
    {
        $request->validate([
            'service_name' => 'required|string|max:255',
            'service_description' => 'required|string',
            'service_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'service_price' => 'required|numeric|min:0',
        ]);

        if ($request->hasFile('service_image')) {
            $imagePath = $request->file('service_image')->store('services', 'public');
            $service->service_image = $imagePath;
        }

        $service->service_name = $request->service_name;
        $service->service_description = $request->service_description;
        $service->service_price = $request->service_price;
        $service->save();

        return redirect()->route('cleaner.service.index')->with('success', 'Service updated successfully!');
    }

    // Menghapus layanan
    public function destroyService($service_name)
    {
        $service = Service::where('service_name', $service_name)->firstOrFail();
        // Hapus data
        $service->delete();
            return redirect()->route('cleaner.service.index')->with('success', 'Service deleted successfully!');
    }
    

    // public function updateStatus(Request $request, Order $order)
    // {
    //     $request->validate([
    //         'status' => 'required|in:pending,in_progress,completed'
    //     ]);

    //     $order->update([
    //         'status' => $request->status
    //     ]);

    //     return back()->with('success', 'Order status updated successfully');
    // }
}