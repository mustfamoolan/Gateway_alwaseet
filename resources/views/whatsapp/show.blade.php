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

    @if($status === 'connected')
    <!-- Test Chat Interface -->
    <div class="card" style="margin-top: 2rem; padding: 0; overflow: hidden; display: flex; flex-direction: column; min-height: 500px; border: 1px solid rgba(255,255,255,0.05);">
        <div style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.02);">
            <h3 style="font-size: 1.1rem; font-weight: 700;"><i class="fab fa-whatsapp" style="color: #25d366; margin-right: 0.5rem;"></i> Live Test Chat</h3>
            <div id="connectionPulse" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: #10b981; font-weight: 700;">
                <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px #10b981; animation: pulse 2s infinite;"></span>
                SYNCED WITH ENGINE
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 350px 1fr; flex-grow: 1;">
            <!-- Chat Config -->
            <div style="padding: 1.5rem; border-right: 1px solid rgba(0,0,0,0.05); background: rgba(0,0,0,0.01);">
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="font-size: 0.8rem; font-weight: 700; color: var(--text-gray); display: block; margin-bottom: 0.5rem;">Destination Number (Test)</label>
                    <input type="text" id="testTo" placeholder="e.g. 966500000000" class="form-control" style="font-size: 0.875rem;">
                    <small style="color: var(--text-gray); font-size: 0.7rem; margin-top: 0.4rem; display: block;">Include country code, no + prefix</small>
                </div>
                <div style="padding: 1rem; background: rgba(79, 70, 229, 0.05); border-radius: 12px; border: 1px dashed rgba(79, 70, 229, 0.2);">
                    <p style="font-size: 0.75rem; color: var(--text-gray); margin-bottom: 0;">Try sending a message to yourself or any number to test the connection. Incoming messages will appear on the right.</p>
                </div>
            </div>

            <!-- Chat Window -->
            <div style="display: flex; flex-direction: column; background: #fff;">
                <!-- Messages Area -->
                <div id="chatMessages" style="flex-grow: 1; padding: 1.5rem; overflow-y: auto; max-height: 400px; display: flex; flex-direction: column; gap: 1rem; background: #f8fafc;">
                    <div style="text-align: center; color: var(--text-gray); padding: 2rem;">
                        <i class="fas fa-comments" style="font-size: 2rem; opacity: 0.2; margin-bottom: 1rem; display: block;"></i>
                        <p style="font-size: 0.875rem;">Waiting for activity...</p>
                    </div>
                </div>

                <!-- Input Area -->
                <div style="padding: 1.5rem; background: #fff; border-top: 1px solid rgba(0,0,0,0.05);">
                    <div style="display: flex; gap: 1rem;">
                        <input type="text" id="testMsg" placeholder="Type a test message..." class="form-control" style="border-radius: 30px; padding-left: 1.5rem;">
                        <button onclick="sendTest()" id="sendBtn" class="btn btn-primary" style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; padding: 0;">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes pulse {
            0% { opacity: 0.4; }
            50% { opacity: 1; }
            100% { opacity: 0.4; }
        }
        .msg-bubble {
            max-width: 80%;
            padding: 0.75rem 1rem;
            border-radius: 18px;
            font-size: 0.875rem;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .msg-inbound {
            align-self: flex-start;
            background: #fff;
            color: #1e293b;
            border-bottom-left-radius: 4px;
        }
        .msg-outbound {
            align-self: flex-end;
            background: var(--primary-glow);
            color: #fff;
            border-bottom-right-radius: 4px;
        }
        .msg-time {
            font-size: 0.65rem;
            opacity: 0.6;
            margin-top: 0.4rem;
            display: block;
        }
    </style>

    <script>
        function sendTest() {
            const to = document.getElementById('testTo').value;
            const msg = document.getElementById('testMsg').value;
            const btn = document.getElementById('sendBtn');

            if(!to || !msg) {
                alert('Please provide number and message');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            fetch("{{ route('whatsapp.test-send', $project->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ to, message: msg })
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('testMsg').value = '';
                    loadMessages();
                } else {
                    alert('Error: ' + (data.error || 'Failed to send'));
                }
            })
            .catch(err => alert('Network Error'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            });
        }

        let lastMsgCount = 0;
        function loadMessages() {
            fetch("{{ route('whatsapp.messages', $project->id) }}")
            .then(res => res.json())
            .then(messages => {
                const container = document.getElementById('chatMessages');
                if(messages.length === 0) return;

                if(messages.length !== lastMsgCount) {
                    container.innerHTML = '';
                    messages.forEach(m => {
                        const isOut = m.direction === 'outbound';
                        const time = new Date(m.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        const div = document.createElement('div');
                        div.className = `msg-bubble ${isOut ? 'msg-outbound' : 'msg-inbound'}`;
                        div.innerHTML = `
                            <div>${m.message_body}</div>
                            <span class="msg-time">${isOut ? '<i class="fas fa-check-double"></i> ' : ''}${time} - ${isOut ? m.to_number : m.from_number}</span>
                        `;
                        container.appendChild(div);
                    });
                    container.scrollTop = container.scrollHeight;
                    lastMsgCount = messages.length;
                }
            });
        }

        // Poll for messages every 3 seconds
        setInterval(loadMessages, 3000);
        loadMessages();
    </script>
    @endif

    <script>
        function copyApi() {
            const inp = document.getElementById('apiKeyInp');
            inp.select();
            document.execCommand('copy');
            alert('API Key copied to clipboard!');
        }
    </script>
@endsection
