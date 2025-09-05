<?php

namespace App\Http\Controllers;

use App\Events\PrivateEvent;
use App\Events\PublicEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TestController extends Controller
{
    public function index()
    {
        $data['users'] = User::all();
        return Inertia::render('Dashboard', $data);
    }
    public function sendPublicMessage(Request $request)
    {
        // 1. Validate the incoming request data (optional but recommended)
        $validatedData = $request->validate([
            'message' => 'required|string|max:255', // Example validation rules
        ]);

        // 2. Extract the message
        $message = $validatedData['message'];

        // 3. Dispatch the PublicEvent
        // This will queue the event for broadcasting.
        PublicEvent::dispatch($message);

        // 4. Return a response (e.g., JSON for an API call, or redirect)
        return response()->json(['status' => 'Message sent!', 'message' => $message]);
        // Or, if not an API call:
        // return redirect()->back()->with('success', 'Public message queued for broadcast.');
    }

    /**
     * Send a private notification to the currently authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPrivateMessageToMe(Request $request)
    {
        // 1. Validate the incoming request data (optional but recommended)
        $validatedData = $request->validate([
            'message' => 'required|string|max:255', // Example validation rules
        ]);

        // 2. Get the currently authenticated user
        /** @var User $user */
        $user = Auth::user(); // Assumes the route is protected by 'auth' middleware

        if (!$user) {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }

        // 3. Extract the message content
        $messageContent = $validatedData['message'];

        // 4. Dispatch the PrivateEvent
        // Pass the User object and the message content
        PrivateEvent::dispatch($user, $messageContent);

        // 5. Return a response
        return response()->json([
            'status' => 'Private message sent!',
            'message' => $messageContent,
            'to_user_id' => $user->id
        ]);
    }

    /**
     * Send a private notification to a specific user by ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPrivateMessage(Request $request)
    {
        // 1. Validate the incoming request data
        $validatedData = $request->validate([
            'target_user_id' => 'required|exists:users,id', // Validate user exists
            'message' => 'required|string|max:255',
        ]);

        $userMe = Auth::user();

        // 2. Find the target user
        $userTo = User::findOrFail($validatedData['target_user_id']);

        // 3. Extract the message content
        $messageContent = $validatedData['message'];

        // 4. Dispatch the PrivateEvent to the target user
        PrivateEvent::dispatch($userTo, $userMe, $messageContent);

        // 5. Return a response
        return response()->json([
            'status' => 'Private message sent!',
            'message' => $messageContent,
            'to_user_id' => $userTo->id
        ]);
    }
}
