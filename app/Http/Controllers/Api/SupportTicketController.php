<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportTicketController extends Controller
{
    /**
     * Create a new support ticket.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:5000',
            'page_url' => 'nullable|string|max:500',
            'page_context' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();

        $ticket = SupportTicket::create([
            'org_id' => $user?->org_id,
            'user_id' => $user?->id,
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'page_url' => $request->page_url,
            'page_context' => $request->page_context,
            'status' => SupportTicket::STATUS_OPEN,
            'priority' => SupportTicket::PRIORITY_NORMAL,
        ]);

        // TODO: Send notification email to support team
        // TODO: Send confirmation email to user

        return response()->json([
            'success' => true,
            'message' => 'Support ticket created successfully.',
            'ticket_id' => $ticket->id,
        ]);
    }
}
