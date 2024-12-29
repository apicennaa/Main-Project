<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class ServiceController extends Controller
{
    // Method untuk dashboard
    public function dashboard()
    {
        $user = auth()->user();
        $services = Service::latest()->take(5)->get(); // Menampilkan 5 Service terbaru
        foreach ($services as $service) {
            $service->service_date = Carbon::parse($service->service_date);
        }
        return view('user.dashboard', compact('user', 'services'));
    }


    // Method untuk melihat semua service
    public function services()
    {
        $services = Service::all();
        return view('user.dashboard', compact('services'));
    }

    public function index()
    {
        $services = Service::where('cleaner_id', auth()->id())->get();
        return view('service.index', compact('services'));
    }
    

    public function subscribeNewsletter(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:newsletters,email'
        ]);

        // Add newsletter subscription logic here

        return back()->with('success', 'Successfully subscribed to newsletter!');
    }

    public function show($id)
    {
        $service = Service::findOrFail($id);
        return view('user.service.show', compact('service'));
    }

    public function store(Request $request)
{
    $request->validate([
        'service_name' => 'required|string|max:255',
        'service_description' => 'required|string',
        'service_price' => 'required|numeric',
        'service_image' => 'required|image|max:2048',
    ]);

    $service = new Service();
    $service->service_name = $request->service_name;
    $service->service_description = $request->service_description;
    $service->service_price = $request->service_price;
    $service->cleaner_id = auth()->id();
    if ($request->hasFile('service_image')) {
        $path = $request->file('service_image')->store('services');
        $service->service_image = $path;
    }
    $service->save();

    return redirect()->route('cleaner.dashboard')->with('success', 'Service added successfully!');
}

}