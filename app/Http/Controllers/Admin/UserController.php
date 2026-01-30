<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog; // Pastikan model AuditLog sudah dibuat
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/UserManagement', [
            'users' => User::all(),
            'auth_user' => auth()->user(),
            'divisions' => \App\Models\Division::all(),
            'audit_logs' => \App\Models\AuditLog::with('user')->latest()->take(50)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:marketing,operator,admin,superadmin',
            'division' => 'required_if:role,operator|nullable|exists:divisions,slug', 
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'division' => $validated['division'],
        ]);

        // AUDIT LOG: Catat penambahan user baru
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'CREATE_USER',
            'module' => 'User Management',
            'details' => "Menambahkan user baru: {$user->name} dengan role {$user->role}",
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'User ' . $user->name . ' berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $userName = $user->name; // Simpan nama untuk catatan log
        $user->delete();

        // AUDIT LOG: Catat penghapusan user
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'DELETE_USER',
            'module' => 'User Management',
            'details' => "Menghapus user: {$userName}",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        
        $user->update([
            'password' => Hash::make('duniatex123')
        ]);

        // AUDIT LOG: Catat reset password
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'RESET_PASSWORD',
            'module' => 'User Management',
            'details' => "Mereset password untuk user: {$user->name}",
            'ip_address' => request()->ip(),
        ]);

        return redirect()->back()->with('success', 'Password untuk ' . $user->name . ' telah direset menjadi: duniatex123');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldRole = $user->role; // Simpan role lama untuk perbandingan

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|in:marketing,operator,admin,superadmin',
            'division' => 'required_if:role,operator|nullable|string',
            'password' => 'nullable|min:8',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->division = $validated['division'];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // AUDIT LOG: Catat pembaruan data user
        $detailAction = "Memperbarui profil user: {$user->name}";
        if ($oldRole !== $user->role) {
            $detailAction .= " (Perubahan Role: {$oldRole} -> {$user->role})";
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'UPDATE_USER',
            'module' => 'User Management',
            'details' => $detailAction,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Data ' . $user->name . 'User berhasil diperbarui!');
    }
}