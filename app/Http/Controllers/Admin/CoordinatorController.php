<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class CoordinatorController extends Controller
{
    /**
     * Display list of coordinators for Superadmin.
     */
    public function index()
    {
        $units = Unit::all();

        // Ensure role koordinator exists
        Role::findOrCreate('koordinator');

        $coordinators = User::role('koordinator')
            ->with('unit')
            ->orderBy('unit_id', 'asc')
            ->get();

        $availableUsers = User::doesntHave('roles')
            ->orWhereHas('roles', function ($q) {
                $q->whereNotIn('name', ['superadmin', 'admin']);
            })
            ->get();

        return view('admin.coordinators.index', compact('coordinators', 'units', 'availableUsers'));
    }

    /**
     * Store/Assign a user as coordinator for a unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'unit_id' => 'required|exists:units,id',
        ], [
            'user_id.required' => 'Pengguna wajib dipilih.',
            'unit_id.required' => 'Unit/Paket wajib dipilih.',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $role = Role::findOrCreate('koordinator');

        $user->unit_id = $validated['unit_id'];
        $user->save();
        $user->syncRoles(['koordinator']);

        return back()->with('success', "Pengguna {$user->name} berhasil ditugaskan sebagai Koordinator Paket.");
    }

    /**
     * Remove coordinator role from user.
     */
    public function destroy(User $user)
    {
        $user->removeRole('koordinator');
        return back()->with('success', "Peran koordinator berhasil dihapus dari pengguna {$user->name}.");
    }
}
