@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">WhatsApp API Projects</h1>
        <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg shadow transition">
            + New Project
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($projects as $project)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $project->name }}</h2>
                    <p class="text-sm text-gray-500">Owner: {{ $project->owner_name }}</p>
                </div>
                <span class="px-2 py-1 text-xs rounded-full {{ $project->status === 'connected' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ ucfirst($project->status) }}
                </span>
            </div>
            
            <div class="mt-4 flex gap-2">
                <a href="{{ route('whatsapp.show', $project->id) }}" class="flex-1 text-center bg-gray-50 hover:bg-gray-100 text-gray-700 px-3 py-2 rounded text-sm font-medium transition">
                    View & Connect
                </a>
                <form action="{{ route('whatsapp.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-red-500 hover:bg-red-50 rounded transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    @if($projects->isEmpty())
    <div class="text-center py-20 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
        <p class="text-gray-500">No WhatsApp projects yet. Create your first one!</p>
    </div>
    @endif
</div>

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-xl font-bold mb-4">Create New WhatsApp Project</h3>
        <form action="{{ route('whatsapp.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project Name</label>
                    <input type="text" name="name" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2 border" placeholder="e.g. My Shop Bot">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Owner Name</label>
                    <input type="text" name="owner_name" required class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-4 py-2 border" placeholder="e.g. Ahmad">
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">Create Project</button>
            </div>
        </form>
    </div>
</div>
@endsection
