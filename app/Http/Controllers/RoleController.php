<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    // List available permissions
    protected $availablePermissions = [
        'manage_users' => 'Manajemen User (HRD & Finance)',
        'view_reports' => 'Melihat & Export Laporan',
        'approve_reimburse' => 'Approval Reimburse',
        'view_sales' => 'Melihat Tim Sales Bawahannya',
        'create_visits' => 'Mulai Hari & Buat Kunjungan',
        'create_reimburse' => 'Pengajuan Reimburse',
    ];

    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('it.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = $this->availablePermissions;
        return view('it.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'dashboard_url' => 'required|string',
            'hrd_can_create' => 'nullable|boolean',
        ]);

        // Generate slug from name
        $slug = Str::slug($request->name);

        // Pastikan slug unik
        if (Role::where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Role dengan nama ini sudah ada atau slug bentrok.'])->withInput();
        }

        Role::create([
            'name' => $request->name,
            'slug' => $slug,
            'permissions' => $request->permissions ?? [],
            'dashboard_url' => $request->dashboard_url,
            'hrd_can_create' => $request->has('hrd_can_create'),
            'is_system_role' => false,
        ]);

        return redirect()->route('it.roles.index')->with('success', 'Role baru berhasil dibuat.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = $this->availablePermissions;
        return view('it.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'dashboard_url' => 'required|string',
            'hrd_can_create' => 'nullable|boolean',
        ]);

        $slug = Str::slug($request->name);

        // Jika mengubah nama, cek keunikan slug baru
        if ($role->slug !== $slug && Role::where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Role dengan nama ini sudah ada atau slug bentrok.'])->withInput();
        }

        // Jangan ubah slug dari system roles agar tidak rusak
        $data = [
            'permissions' => $request->permissions ?? [],
            'dashboard_url' => $request->dashboard_url,
            'hrd_can_create' => $request->has('hrd_can_create'),
        ];

        if (!$role->is_system_role) {
            $data['name'] = $request->name;
            $data['slug'] = $slug;
            // if we changed the slug, we MUST update all users that had the old slug
            if ($role->slug !== $slug) {
                \App\Models\User::where('role', $role->slug)->update(['role' => $slug]);
            }
        } elseif ($request->name !== $role->name) {
            $data['name'] = $request->name; // System role can change display name but not slug
        }

        $role->update($data);

        return redirect()->route('it.roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        if ($role->is_system_role) {
            return back()->with('error', 'Role sistem bawaan tidak dapat dihapus.');
        }

        // Pastikan tidak ada user yang sedang menggunakan role ini
        if (\App\Models\User::where('role', $role->slug)->exists()) {
            return back()->with('error', 'Gagal menghapus. Masih ada user yang menggunakan Role ini.');
        }

        $role->delete();

        return redirect()->route('it.roles.index')->with('success', 'Role berhasil dihapus.');
    }
}
