<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FuelSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FuelSettingController extends Controller
{
    // ==========================================
    // LIST SETTING (HRD/IT ONLY)
    // ==========================================

    public function index()
    {
        // Ambil general setting
        $generalSetting = FuelSetting::whereNull('user_id')
            ->where('is_active', true)
            ->first();

        // Ambil semua individual settings dengan user
        $individualSettings = FuelSetting::whereNotNull('user_id')
            ->with('user')
            ->where('is_active', true)
            ->get();

        // Ambil semua sales untuk dropdown
        $sales = User::where('role', 'sales')->get();

        return view('fuel_settings.index', compact('generalSetting', 'individualSettings', 'sales'));
    }

    // ==========================================
    // SETTING GENERAL (Untuk Semua Karyawan)
    // ==========================================

    public function storeGeneral(Request $request)
    {
        $request->validate([
            'km_per_liter' => 'required|numeric|min:0.001',
            'fuel_price' => 'required|numeric|min:0',
        ]);

        // Cek apakah sudah ada general setting aktif
        $existing = FuelSetting::whereNull('user_id')
            ->where('is_active', true)
            ->first();

        if ($existing) {
            // Update yang sudah ada
            $existing->update([
                'km_per_liter' => $request->km_per_liter,
                'fuel_price' => $request->fuel_price,
            ]);
        } else {
            // Nonaktifkan general setting yang lama (jika ada)
            FuelSetting::whereNull('user_id')
                ->update(['is_active' => false]);

            // Buat general setting baru
            FuelSetting::create([
                'user_id' => null, // null = general
                'km_per_liter' => $request->km_per_liter,
                'fuel_price' => $request->fuel_price,
                'is_active' => true,
            ]);
        }

        $routeName = Auth::user()->isIt() ? 'it.fuel_settings.index' : 'fuel_settings.index';
        return redirect()->route($routeName)
            ->with('success', 'Setting general bahan bakar berhasil disimpan!');
    }

    // ==========================================
    // SETTING INDIVIDUAL (Per Karyawan)
    // ==========================================

    public function storeIndividual(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'km_per_liter' => 'required|numeric|min:0.001',
            'fuel_price' => 'required|numeric|min:0',
        ]);

        // Cek apakah user adalah sales
        $user = User::findOrFail($request->user_id);
        if ($user->role !== 'sales') {
            return back()->withErrors(['user_id' => 'Hanya bisa setting untuk sales.'])->withInput();
        }

        // Nonaktifkan setting individual yang lama untuk user ini
        FuelSetting::where('user_id', $request->user_id)
            ->update(['is_active' => false]);

        // Buat individual setting baru
        FuelSetting::create([
            'user_id' => $request->user_id,
            'km_per_liter' => $request->km_per_liter,
            'fuel_price' => $request->fuel_price,
            'is_active' => true,
        ]);

        $routeName = Auth::user()->isIt() ? 'it.fuel_settings.index' : 'fuel_settings.index';
        return redirect()->route($routeName)
            ->with('success', 'Setting individual bahan bakar berhasil disimpan!');
    }

    // ==========================================
    // UPDATE SETTING
    // ==========================================

    public function update(Request $request, $id)
    {
        $setting = FuelSetting::findOrFail($id);

        $request->validate([
            'km_per_liter' => 'required|numeric|min:0.001',
            'fuel_price' => 'required|numeric|min:0',
        ]);

        $setting->update([
            'km_per_liter' => $request->km_per_liter,
            'fuel_price' => $request->fuel_price,
        ]);

        $routeName = Auth::user()->isIt() ? 'it.fuel_settings.index' : 'fuel_settings.index';
        return redirect()->route($routeName)
            ->with('success', 'Setting berhasil diupdate!');
    }

    // ==========================================
    // NONAKTIFKAN SETTING
    // ==========================================

    public function deactivate($id)
    {
        $setting = FuelSetting::findOrFail($id);
        $setting->update(['is_active' => false]);

        $routeName = Auth::user()->isIt() ? 'it.fuel_settings.index' : 'fuel_settings.index';
        return redirect()->route($routeName)
            ->with('success', 'Setting berhasil dinonaktifkan!');
    }
}
