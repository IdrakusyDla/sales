@extends('layout')
@section('content')
    <div class="px-5 md:px-8 py-6 md:py-8">
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('it.dashboard') }}" class="text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h1 class="text-2xl font-bold">Manajemen Role & Akses</h1>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 text-sm font-bold">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 text-sm font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-lg">Daftar Role</h2>
                <a href="{{ route('it.roles.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-xl text-sm font-bold shadow-sm hover:bg-blue-700">+ Buat Role Baru</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="p-4 rounded-tl-xl text-xs font-bold text-gray-500 uppercase">Nama Role</th>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Slug / Kode</th>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Redirect Dashboard</th>
                            <th class="p-4 text-xs font-bold text-gray-500 uppercase">Hak Akses</th>
                            <th class="p-4 rounded-tr-xl text-xs font-bold text-gray-500 uppercase text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm">
                        @foreach($roles as $r)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="p-4 font-bold text-gray-800">
                                {{ $r->name }}
                                @if($r->is_system_role)
                                    <br><span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">Sistem Bawaan</span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-600">{{ $r->slug }}</td>
                            <td class="p-4 text-gray-600">{{ $r->dashboard_url }}</td>
                            <td class="p-4 max-w-sm">
                                @if(is_array($r->permissions) && in_array('all', $r->permissions))
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded font-bold">Akses Penuh (Full Control)</span>
                                @elseif(is_array($r->permissions) && count($r->permissions) > 0)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($r->permissions as $p)
                                            <span class="text-[10px] bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded border border-blue-100">{{ $p }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Tidak ada akses statis</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex flex-col gap-2 relative">
                                    <a href="{{ route('it.roles.edit', $r->id) }}" class="text-blue-600 hover:text-blue-800 font-bold whitespace-nowrap block">Edit</a>
                                    @if(!$r->is_system_role)
                                        <form action="{{ route('it.roles.destroy', $r->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="return confirm('Hapus role ini secara permanen?\nPERINGATAN: Semua user yang menggunakan Role ini akan error.')" class="text-red-500 hover:text-red-700 font-bold whitespace-nowrap">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
