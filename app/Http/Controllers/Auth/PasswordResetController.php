<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * "Forgot password", handled by hand. No email is sent. The customer gives the email
 * and phone number of the account plus the new password they want. When both match
 * the same account, a request appears in the admin panel; the admin calls the customer
 * to confirm and then approves it. Only then does the new password start to work.
 */
class PasswordResetController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = User::where('email', $data['email'])->where('role', 'customer')->first();

        // The email and the phone number must belong to the same account.
        $phone = PasswordRequest::normalizePhone($data['phone']);
        if (! $user || $user->is_blocked || $phone === '' || PasswordRequest::normalizePhone($user->phone) !== $phone) {
            throw ValidationException::withMessages([
                'phone' => 'This email and phone number do not belong to the same account. Use the phone number you gave when you made the account.',
            ]);
        }

        // One open request per customer: a new one replaces the old one.
        PasswordRequest::where('user_id', $user->id)->where('status', PasswordRequest::PENDING)->delete();

        PasswordRequest::create([
            'user_id' => $user->id,
            'phone' => $user->phone,
            'password_hash' => Hash::make($data['password']),
            'status' => PasswordRequest::PENDING,
        ]);

        return redirect()->route('login')->with('success', 'Your request was sent. We will call '.$user->phone.' to confirm it is you, then your new password will work.');
    }
}
