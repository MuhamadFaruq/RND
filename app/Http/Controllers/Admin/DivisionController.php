<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class DivisionController extends Controller
{
    /**
     * Menampilkan daftar divisi di halaman Manajemen Divisi
     */
    public function index()
    {
        return Inertia::render('Admin/DivisionManagement', [
            'divisions' => Division::orderBy('name', 'asc')->get(),
        ]);
    }

    /**
     * Menyimpan divisi baru dari form
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name',
            'slug' => 'required|string|unique:divisions,slug',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // Pastikan slug dalam format yang benar (kecil dan tanpa spasi)
        $validated['slug'] = Str::slug($request->slug);

        Division::create($validated);

        return redirect()->back()->with('success', 'Divisi berhasil ditambahkan ke sistem Duniatex!');
    }

    /**
     * Mengupdate data divisi yang sudah ada
     */
    public function update(Request $request, $id)
    {
        $division = Division::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:divisions,name,' . $id,
            'slug' => 'required|string|unique:divisions,slug,' . $id,
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($request->slug);
        
        $division->update($validated);

        return redirect()->back()->with('success', 'Data divisi berhasil diperbarui.');
    }

    /**
     * Menghapus divisi secara permanen
     */
    public function destroy($id)
    {
        $division = Division::findOrFail($id);
        $division->delete();

        return redirect()->back()->with('success', 'Divisi telah dihapus dari database.');
    }
}