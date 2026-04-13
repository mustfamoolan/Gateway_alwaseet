@extends('layouts.admin')

@section('content')
    <div style="margin-bottom: 2rem;">
        <a href="{{ route('whatsapp.index') }}" style="color: var(--text-gray); text-decoration: none; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Channels
        </a>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.5rem; font-weight: 800;">{{ $project->name }} <span style="font-weight: 300; font-size: 1rem; color: var(--text-gray);">/ Connection Manager</span></h2>
            <div class="badge" style="background: {{ $status === 'connected' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; color: {{ $status === 'connected' ? '#10b981' : '#f59e0b' }}; padding: 0.5rem 1rem; font-weight: 700;">
                <i class="fas {{ $status === 'connected' ? 'fa-check-circle' : 'fa-clock' }}" style="margin-right: 0.4rem;"></i> {{ strtoupper($status) }}
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 450px 1fr; gap: 2rem;">
        
        <!-- Connection Status (QR) -->
        <div class="card" style="text-align: center; padding: 3rem 2rem;">
            @if($status === 'connected')
                <div style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                    <i class="fas fa-shield-check"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">Securely Connected</h3>
                <p style="color: var(--text-gray); font-size: 0.875rem; margin-bottom: 2rem;">Your device is linked and monitoring incoming API requests.</p>
                <div style="padding: 1rem; background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.1); border-radius: 12px; color: #10b981; font-size: 0.75rem; font-weight: 700;">
                    LIVE: MULTI-DEVICE MODE ENABLED
                </div>
            @elseif($qr)
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem;">Link WhatsApp Account</h3>
                <p style="color: var(--text-gray); font-size: 0.8rem; margin-bottom: 2rem;">Scan this code using WhatsApp on your phone</p>
                
                <div style="background: #fff; padding: 1.5rem; border-radius: 20px; display: inline-block; margin-bottom: 2rem;">
                    <img src="{{ $qr }}" alt="WhatsApp QR" style="width: 250px; height: 250px; display: block;">
                </div>

                <div style="color: var(--secondary); font-size: 0.75rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fas fa-sync fa-spin"></i> Auto-refreshing session...
                </div>

                <script>
                    setInterval(() => {
                        fetch(window.location.href)
                            .then(res => res.text())
                            .then(html => {
                                if(html.includes('Securely Connected')) { window.location.reload(); }
                            });
                    }, 4000);
                </script>
            @elseif($error)
                <div style="padding: 3rem 0; color: #ef4444;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 2.5rem; margin-bottom: 1.5rem;"></i>
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">Connection Error</h3>
                    <p style="font-size: 0.875rem; color: var(--text-gray);">{{ $error }}</p>
                    <div style="margin-top: 1.5rem;">
                        <a href="{{ route('whatsapp.show', $project->id) }}" class="btn btn-primary" style="font-size: 0.75rem;">Retry Connection</a>
                    </div>
                </div>
            @else
                <div style="padding: 4rem 0;">
                    <i class="fas fa-circle-notch fa-spin" style="font-size: 2.5rem; color: var(--primary-glow); margin-bottom: 1.5rem;"></i>
                    <p style="color: var(--text-gray); font-weight: 600;">Initializing Engine...</p>
                    <p style="font-size: 0.75rem; color: var(--text-gray); margin-top: 0.5rem;">If this takes more than 30 seconds, please check the logs.</p>
                </div>
                <script>setTimeout(() => window.location.reload(), 5000);</script>
            @endif
        </div>

        <!-- API Integration Details -->
        <div class="space-y-6">
            <div class="card shadow-lg" style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border: 1px solid rgba(79, 70, 229, 0.3);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #fff;">API Credentials</h3>
                    <i class="fas fa-key" style="color: var(--primary-glow);"></i>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="color: rgba(255,255,255,0.6);">Authentication Key</label>
                    <div style="display: flex; gap: 1rem; margin-top: 0.5rem;">
                        <input type="text" readonly value="{{ $project->api_key }}" class="form-control" style="background: rgba(0,0,0,0.3); border-color: rgba(255,255,255,0.1); font-family: monospace; font-size: 0.875rem;" id="apiKeyInp">
                        <button onclick="copyApi()" class="btn btn-primary" style="flex-shrink: 0; padding: 0 1.5rem;">Copy</button>
                    </div>
                </div>

                <div style="background: rgba(0,0,0,0.2); border-radius: 12px; padding: 1.5rem;">
                    <p style="font-size: 0.75rem; color: rgba(255,255,255,0.4); margin-bottom: 1rem; text-transform: uppercase; font-weight: 800;">Implementation Example (cURL)</p>
                    <pre style="margin: 0; white-space: pre-wrap; word-break: break-all; color: var(--primary-glow); font-size: 0.75rem; font-family: monospace; line-height: 1.6;">
curl -X POST {{ url('/api/v1/whatsapp/send') }} \
-H "X-WA-API-KEY: {{ $project->api_key }}" \
-H "Content-Type: application/json" \
-d '{
  "to": "9665XXXXXXXX",
  "message": "Hello from API!"
}'</pre>
                </div>
            </div>

            <!-- Integration Notes -->
            <div class="card" style="padding: 1.5rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 1rem;"><i class="fas fa-info-circle" style="color: var(--primary-glow);"></i> Integration Guidelines</h4>
                <ul style="color: var(--text-gray); font-size: 0.8rem; line-height: 1.8; padding-left: 1.2rem;">
                    <li>Ensure the destination number includes the country code with no prefix (e.g. 966...).</li>
                    <li>Do not use the API for spamming to avoid WhatsApp account banning.</li>
                    <li>The API supports plain text, links, and basic emojis by default.</li>
                </ul>
            </div>
        </div>

    </div>

    <script>
        function copyApi() {
            const inp = document.getElementById('apiKeyInp');
            inp.select();
            document.execCommand('copy');
            alert('API Key copied to clipboard!');
        }
    </script>
@endsection
