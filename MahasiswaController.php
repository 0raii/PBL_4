<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller; // Pastikan ini diimpor

class MahasiswaController extends Controller
{
    // Menampilkan daftar mahasiswa
    public function index()
    {
        $mahasiswas = Mahasiswa::all();
        // Pastikan Anda memiliki file resources/views/mahasiswa/index.blade.php
        return view('mahasiswa.index', compact('mahasiswas'));
    }

    // Menampilkan formulir untuk membuat mahasiswa baru
    public function create()
    {
        return view('mahasiswa.create');
    }

    // Menyimpan mahasiswa baru ke database
    public function store(Request $request)
    {
        // Validasi data input
        $request->validate([
            'username' => 'required|string|max:50|unique:mahasiswa,username',
            'password' => 'required|string|min:6',
            'namaLengkap' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:mahasiswa,email',
            'nim' => 'required|string|max:20|unique:mahasiswa,nim',
            'role' => 'required|string|in:admin,dosen,staff,mahasiswa',
            'prodi' => 'required|string|max:50',
        ]);
        
        // Buat instance model Mahasiswa
        $mahasiswa = new Mahasiswa();

        // Mengisi data dari request
        $mahasiswa->username = $request->username;
        $mahasiswa->password = Hash::make($request->password);
        $mahasiswa->namaLengkap = $request->namaLengkap;
        $mahasiswa->email = $request->email;
        $mahasiswa->nim = $request->nim;
        $mahasiswa->role = $request->role;
        $mahasiswa->prodi = $request->prodi;

        // Simpan data ke database
        $mahasiswa->save();

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    // Menampilkan detail mahasiswa
    public function show(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.show', compact('mahasiswa'));
    }

    // Menampilkan formulir untuk mengedit mahasiswa
    public function edit(Mahasiswa $mahasiswa)
    {
        return view('mahasiswa.edit', compact('mahasiswa'));
    }

    // Memperbarui mahasiswa
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'username' => 'required|max:50|unique:mahasiswa,username,' . $mahasiswa->id,
            'namaLengkap' => 'required',
            'email' => 'required|email|unique:mahasiswa,email,' . $mahasiswa->id,
            'nim' => 'required|unique:mahasiswa,nim,' . $mahasiswa->id,
            'role' => 'required',
            'prodi' => 'required',
        ]);

        $mahasiswa->update($validated);

        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil diperbarui.');
    }

    // Menghapus mahasiswa
    public function destroy(Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();
        return redirect()->route('mahasiswa.index')->with('success', 'Mahasiswa berhasil dihapus.');
    }
}