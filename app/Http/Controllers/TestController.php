<?php

namespace App\Http\Controllers;

use App\Events\TestEvent;
use Illuminate\Http\Request;

class TestController extends Controller
{
     public function sendEvent(Request $request)
    {
        // Kirim event ke semua client yang listening
        broadcast(new TestEvent(
            message: 'Hello from server!',
            user: $request->user ?? 'Anonymous'
        ));
        
        return response()->json(['status' => 'Event sent!']);
    }
}
