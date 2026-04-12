@extends('layouts.admin')

@section('content')
<div class="p-6 md:p-12 bg-slate-50 min-h-screen">
    <!-- Breadcrumbs & Header -->
    <div class="max-w-6xl mx-auto mb-10">
        <a href="{{ route('whatsapp.index') }}" class="inline-flex items-center text-slate-400 hover:text-indigo-600 font-bold transition-colors mb-4 group">
            <i class="fas fa-arrow-left mr-2 group-hover:-translate-x-1 transition-transform"></i>
            Back to Overview
        </a>
        <div class="flex items-center gap-6">
            <div class="w-16 h-16 bg-white rounded-3xl shadow-sm flex items-center justify-center text-indigo-600 text-3xl border border-slate-100">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">{{ $project->name }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-slate-400 font-medium">Owner: {{ $project->owner_name }}</span>
                    <span class="text-slate-200">|</span>
                    <span class="inline-flex items-center gap-2 {{ $status === 'connected' ? 'text-emerald-500' : 'text-amber-500' }} font-bold text-sm uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full {{ $status === 'connected' ? 'bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)] pulse-fast' : 'bg-amber-500' }}"></span>
                        {{ $status }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <!-- Connection Card (Left) -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 h-full flex flex-col items-center justify-center text-center">
                
                @if($status === 'connected')
                    <div class="relative">
                        <div class="w-48 h-48 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 text-7xl animate-in zoom-in duration-500">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="absolute -bottom-4 left-1/2 -translate-x-1/2 bg-white px-6 py-2 rounded-full shadow-lg border border-emerald-100 whitespace-nowrap">
                            <span class="text-emerald-600 font-black tracking-tight tracking-wide uppercase text-xs">Device Ready</span>
                        </div>
                    </div>
                    <h3 class="mt-12 text-2xl font-black text-slate-800">Connection Active</h3>
                    <p class="text-slate-400 mt-2 font-medium max-w-[250px]">Your account is linked and ready to process messages.</p>
                @elseif($qr)
                    <div class="bg-slate-50 p-8 rounded-[2.5rem] border border-slate-100 relative group transition-all">
                        <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-200">
                            <img src="{{ $qr }}" alt="QR" class="w-56 h-56 group-hover:scale-105 transition-transform duration-500">
                        </div>
                    </div>
                    <div class="mt-8">
                        <h3 class="text-xl font-black text-slate-800 mb-2">Scan with WhatsApp</h3>
                        <p class="text-slate-400 text-sm font-medium leading-relaxed">Go to Settings > Linked Devices<br>on your phone to scan this code.</p>
                    </div>
                    
                    <script>
                        setInterval(() => {
                            fetch(window.location.href)
                                .then(res => res.text())
                                .then(html => {
                                    if(html.includes('Connection Active')) { window.location.reload(); }
                                });
                        }, 4000);
                    </script>
                @else
                    <div class="flex flex-col items-center">
                        <div class="w-20 h-20 border-4 border-slate-100 border-t-indigo-600 rounded-full animate-spin"></div>
                        <p class="mt-6 font-bold text-slate-400 animate-pulse">Initializing Engine...</p>
                    </div>
                    <script>setTimeout(() => window.location.reload(), 3000);</script>
                @endif
            </div>
        </div>

        <!-- API Details (Right) -->
        <div class="lg:col-span-7 space-y-8">
            <!-- API Key Card -->
            <div class="bg-slate-900 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-indigo-900/20 relative overflow-hidden">
                <i class="fas fa-key absolute -right-4 -bottom-4 text-white/5 text-8xl transform -rotate-12"></i>
                
                <h3 class="text-xl font-bold mb-8 flex items-center gap-2">
                    <span class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center text-xs">
                        <i class="fas fa-code"></i>
                    </span>
                    API Authentication Key
                </h3>

                <div class="bg-white/5 border border-white/10 p-5 rounded-2xl flex items-center justify-between group">
                    <code class="font-mono text-indigo-300 text-sm md:text-base tracking-tighter" id="apiKeyText">{{ $project->api_key }}</code>
                    <button onclick="copyKey()" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2 rounded-xl text-xs font-bold transition-all active:scale-95">
                        <i class="fas fa-copy mr-2"></i> Copy Key
                    </button>
                </div>
                <p class="mt-6 text-slate-400 text-sm font-medium italic">Keep this key secret and secure.</p>
            </div>

            <!-- cURL Bento Box -->
            <div class="bg-white rounded-[2.5rem] p-10 border border-slate-100 shadow-sm">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-3">
                    <i class="fas fa-terminal text-indigo-600"></i>
                    Documentation Snippet
                </h3>
                
                <div class="relative group">
                    <pre class="bg-slate-50 border border-slate-100 p-6 rounded-2xl text-[11px] md:text-[13px] text-slate-600 font-mono leading-relaxed overflow-x-auto">
curl -X POST {{ url('/api/v1/whatsapp/send') }} \
-H "X-WA-API-KEY: YOUR_KEY" \
-H "Content-Type: application/json" \
-d '{
  "to": "9665XXXXXXXX",
  "message": "Hello from API!"
}'</pre>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    @keyframes pulse-fast {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.5); opacity: 0.5; }
    }
    .pulse-fast { animation: pulse-fast 1.5s infinite; }
</style>

<script>
function copyKey() {
    const text = document.getElementById('apiKeyText').innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('API Key Copied to Dashboard!');
    });
}
</script>
@endsection
