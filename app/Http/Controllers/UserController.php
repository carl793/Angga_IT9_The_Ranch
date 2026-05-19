<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        // Security check: Only admins can view this page
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access.');
        }

        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,staff,manager'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Encrypt the password
            'role' => $request->role,
        ]);

        return back()->with('success', 'New user account created successfully!');
    }

    public function destroy(User $user)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Prevent the admin from accidentally deleting themselves
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own active session.');
        }

        $user->delete();
        return back()->with('success', 'User account removed.');
    }
}