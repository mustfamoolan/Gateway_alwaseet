@extends('layouts.admin')

@section('content')
<div class="p-4 md:p-10 bg-slate-50 min-h-screen">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto mb-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-3xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">WhatsApp API Service</h1>
                <p class="text-slate-500 mt-2 font-medium">Manage your automated messaging channels</p>
            </div>
            <button onclick="document.getElementById('createModal').classList.remove('hidden')" 
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-4 rounded-2xl shadow-lg shadow-indigo-100 transition-all active:scale-95 flex items-center justify-center gap-2 font-bold">
                <i class="fas fa-plus"></i>
                Create New Project
            </button>
        </div>
    </div>

    <!-- Stats/Bento Grid -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($projects as $project)
        <div class="group bg-white rounded-[2rem] p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 relative overflow-hidden">
            <!-- Decorative Gradient -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 opacity-50 group-hover:bg-indigo-100 transition-colors"></div>
            
            <div class="relative z-10">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-4 bg-indigo-50 text-indigo-600 rounded-2xl group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <i class="fab fa-whatsapp text-2xl"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full {{ $project->status === 'connected' ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]' : 'bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]' }}"></div>
                        <span class="text-xs font-bold uppercase tracking-wider {{ $project->status === 'connected' ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $project->status }}
                        </span>
                    </div>
                </div>

                <h2 class="text-xl font-bold text-slate-800 mb-1">{{ $project->name }}</h2>
                <div class="flex items-center gap-2 text-slate-400 text-sm mb-8">
                    <i class="fas fa-user-circle"></i>
                    <span>{{ $project->owner_name }}</span>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('whatsapp.show', $project->id) }}" 
                       class="flex-1 bg-slate-900 hover:bg-black text-white text-center py-4 rounded-xl font-bold transition-all flex items-center justify-center gap-2">
                        Manage <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                    
                    <form action="{{ route('whatsapp.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-4 text-slate-300 hover:bg-rose-50 hover:text-rose-500 rounded-xl transition-all">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Empty State in Grid -->
        @if($projects->isEmpty())
        <div class="lg:col-span-3 py-24 flex flex-col items-center justify-center bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
            <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center text-slate-300 text-4xl mb-6">
                <i class="fab fa-whatsapp"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">No Projects Found</h3>
            <p class="text-slate-400 mt-2">Start by creating your first WhatsApp automation project.</p>
        </div>
        @endif
    </div>
</div>

<!-- Premium Create Modal -->
<div id="createModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg p-10 relative animate-in fade-in zoom-in duration-300">
        <button onclick="document.getElementById('createModal').classList.add('hidden')" 
                class="absolute top-6 right-8 text-slate-300 hover:text-slate-500 transition-colors">
            <i class="fas fa-times text-xl"></i>
        </button>

        <h3 class="text-2xl font-black text-slate-900 mb-2">Build New Connection</h3>
        <p class="text-slate-500 mb-8 font-medium">Configure your messaging project details below.</p>

        <form action="{{ route('whatsapp.store') }}" method="POST">
            @csrf
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 px-1">Project Identifier</label>
                    <div class="relative">
                        <i class="fas fa-tag absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="name" required 
                               class="w-full bg-slate-50 border-0 rounded-2xl px-12 py-4 text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all font-medium" 
                               placeholder="e.g. Sales Notification">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2 px-1">Lead Developer/Owner</label>
                    <div class="relative">
                        <i class="fas fa-user-tie absolute left-5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="owner_name" required 
                               class="w-full bg-slate-50 border-0 rounded-2xl px-12 py-4 text-slate-900 focus:ring-2 focus:ring-indigo-500 transition-all font-medium" 
                               placeholder="Name of person responsible">
                    </div>
                </div>
            </div>
            <div class="mt-10 flex gap-4">
                <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-5 rounded-2xl font-bold shadow-lg shadow-indigo-100 transition-all active:scale-95">
                    Launch Project
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .sidebar { border-right: 1px solid #f1f5f9 !important; }
    .layout { background: #f8fafc !important; }
</style>
@endsection
