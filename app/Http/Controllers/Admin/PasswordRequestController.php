<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PasswordRequestController extends Controller
{
    public function index(): Response
    {
        $map = fn (PasswordRequest $r) => [
            'id' => $r->id,
            'status' => $r->status,
            'phone' => $r->phone,
            'created_at' => $r->created_at,
            'handled_at' => $r->handled_at,
            'handled_by' => $r->handler?->name,
            'customer' => ['id' => $r->user->id, 'name' => $r->user->name, 'email' => $r->user->email],
        ];

        return Inertia::render('Admin/PasswordRequests/Index', [
            'pending' => PasswordRequest::with('user')->where('status', PasswordRequest::PENDING)->oldest()->get()->map($map)->values(),
            'recent' => PasswordRequest::with(['user', 'handler'])->where('status', '!=', PasswordRequest::PENDING)->latest('handled_at')->take(15)->get()->map($map)->values(),
        ]);
    }

    /** The admin has phoned the customer and it was them: the chosen password becomes the account password. */
    public function approve(Request $request, PasswordRequest $passwordRequest): RedirectResponse
    {
        abort_unless($passwordRequest->status === PasswordRequest::PENDING, 404);

        // Written straight to the column: the hash is already made.
        User::whereKey($passwordRequest->user_id)->update([
            'password' => $passwordRequest->password_hash,
            'remember_token' => Str::random(60),
        ]);

        $this->close($passwordRequest, PasswordRequest::APPROVED, $request);

        return back()->with('success', 'Done. '.$passwordRequest->user->name.' can sign in with the new password.');
    }

    public function reject(Request $request, PasswordRequest $passwordRequest): RedirectResponse
    {
        abort_unless($passwordRequest->status === PasswordRequest::PENDING, 404);

        $this->close($passwordRequest, PasswordRequest::REJECTED, $request);

        return back()->with('success', 'Request rejected. The password was not changed.');
    }

    private function close(PasswordRequest $passwordRequest, string $status, Request $request): void
    {
        $passwordRequest->forceFill([
            'status' => $status,
            'password_hash' => '',
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ])->save();
    }
}
