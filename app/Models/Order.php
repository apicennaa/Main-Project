<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'cleaner_id', 
        'service_id', 
        'schedule_time', 
        'status', 
        'total_price',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Cleaner (jika ada)
    public function cleaner()
    {
        return $this->belongsTo(Cleaner::class);
    }    

    // Relasi ke Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}