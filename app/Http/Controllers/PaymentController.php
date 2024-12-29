<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // public function create(Order $order)
    // {
    //     // Pastikan order milik user yang sedang login
    //     if ($order->user_id !== auth()->id()) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     return view('payment.create', compact('order'));
    // }
    public function create(Request $request)
    {
        $order = Order::where('id', $request->order_id)
                    ->where('user_id', auth()->id())
                    ->firstOrFail();
    
        return view('payment.create', compact('order'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:credit_card,bank_transfer,e-wallet'
        ]);
    
        $order = Order::where('id', $request->order_id)
                      ->where('user_id', auth()->id())
                      ->firstOrFail(); // Pastikan order milik user
    
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
        ]);
    
        // Integrasi gateway pembayaran bisa ditempatkan di sini
        return redirect()->route('payment.index', $payment->id)
            ->with('success', 'Payment initiated successfully');
    }    

    public function success($payment_id)
    {
        $payment = Payment::with('order.service')->findOrFail($payment_id);
        return view('payment.success', compact('payment'));
    }
}