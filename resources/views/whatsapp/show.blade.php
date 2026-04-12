@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('whatsapp.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-800">{{ $project->name }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Connection Status Card -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full {{ $status === 'connected' ? 'bg-green-500 animate-pulse' : 'bg-yellow-500' }}"></span>
                Link Status: {{ ucfirst($status) }}
            </h2>

            @if($status === 'connected')
                <div class="text-center py-8">
                    <div class="bg-green-50 px-4 py-3 rounded-lg text-green-700 mb-4 inline-block">
                        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Successfully Connected!
                    </div>
                </div>
            @elseif($qr)
                <div class="text-center bg-gray-50 p-6 rounded-xl border border-dashed border-gray-300">
                    <p class="text-sm text-gray-600 mb-4">Scan the QR code with your WhatsApp app (Settings > Linked Devices)</p>
                    <div class="bg-white p-4 inline-block rounded-lg shadow-sm border">
                        <img src="{{ $qr }}" alt="WhatsApp QR Code" class="w-64 h-64">
                    </div>
                    <p class="mt-4 text-xs text-gray-500">Auto-refreshes every few seconds...</p>
                    <script>
                        // Simple polling to refresh the page when connected
                        setInterval(() => {
                            fetch(window.location.href)
                                .then(res => res.text())
                                .then(html => {
                                    if(html.includes('Successfully Connected!')) {
                                        window.location.reload();
                                    }
                                });
                        }, 5000);
                    </script>
                </div>
            @else
                <div class="py-12 text-center text-gray-400">
                    Initializing engine... please wait.
                    <script>setTimeout(() => window.location.reload(), 3000);</script>
                </div>
            @endif
        </div>

        <!-- API Integration Card -->
        <div class="bg-white rounded-xl shadow-sm border p-6">
            <h2 class="text-lg font-semibold mb-4">API Integration</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Your API Key</label>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ $project->api_key }}" class="bg-gray-50 border-gray-300 rounded font-mono text-xs w-full p-2" id="apiKeyInput">
                        <button onclick="copyToClipboard()" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Copy</button>
                    </div>
                </div>

                <div class="pt-4 border-t">
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Usage Example (cURL)</label>
                    <pre class="bg-gray-900 text-gray-300 p-3 rounded text-[10px] overflow-x-auto">
curl -X POST {{ url('/api/v1/whatsapp/send') }} \
  -H "X-WA-API-KEY: {{ $project->api_key }}" \
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

<script>
function copyToClipboard() {
    const input = document.getElementById('apiKeyInput');
    input.select();
    document.execCommand('copy');
    alert('API Key copied to clipboard!');
}
</script>
@endsection
