@extends('layouts.admin')

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="card stat-card">
            <h3>Total Projects</h3>
            <div class="value">{{ $stats['projects_count'] }}</div>
            <p style="color: var(--text-gray); font-size: 0.8rem; margin-top: 0.5rem;">
                <span style="color: var(--secondary);">{{ $stats['active_projects'] }}</span> Active
            </p>
        </div>
        
        <div class="card stat-card">
            <h3>Today's Requests</h3>
            <div class="value">{{ $stats['requests_today'] }}</div>
        </div>
        
        <div class="card stat-card">
            <h3>Lifetime Requests</h3>
            <div class="value">{{ $stats['total_requests'] }}</div>
        </div>

        <div class="card stat-card" style="background: linear-gradient(135deg, #4F46E5 0%, #312E81 100%); border: none;">
            <h3>Target IP (Whitelist)</h3>
            <div class="value" style="font-size: 1.5rem; margin-top: 0.5rem; font-family: monospace;">{{ $serverIp }}</div>
            <p style="color: rgba(255,255,255,0.6); font-size: 0.75rem; margin-top: 0.5rem; margin-bottom: 1rem;">
                Add this IP to Al-Waseet account
            </p>
            <button id="testApiBtn" class="btn" style="width: 100%; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                <i class="fas fa-satellite-dish"></i> Test Waseet API Connection
            </button>
        </div>
    </div>

    <!-- Recent Activity Table -->
    <div style="margin-top: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 700;">Recent Gateway Traffic</h2>
            <a href="{{ route('logs.index') }}" style="color: var(--primary-glow); font-size: 0.875rem; text-decoration: none;">View all logs <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="card" style="padding: 0;">
            <div class="data-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Endpoint</th>
                            <th>Status No.</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                            <tr>
                                <td>
                                    <span style="font-weight: 700; color: #fff;">{{ $log->project->name }}</span>
                                </td>
                                <td><code>{{ $log->endpoint }}</code></td>
                                <td>{{ $log->http_status_code }}</td>
                                <td>
                                    <span class="badge {{ $log->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                        {{ strtoupper($log->status) }}
                                    </span>
                                </td>
                                <td style="font-family: monospace; color: var(--text-gray);">{{ $log->ip_address }}</td>
                                <td style="color: var(--text-gray);">{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-gray); padding: 3rem;">
                                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>
                                    No requests yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.getElementById('testApiBtn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Pinging...';
        btn.disabled = true;

        fetch('{{ route('dashboard.test') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                btn.style.background = 'rgba(16, 185, 129, 0.2)';
                btn.style.borderColor = '#10b981';
            } else {
                alert('❌ ' + data.message);
                btn.style.background = 'rgba(239, 68, 68, 0.2)';
                btn.style.borderColor = '#ef4444';
            }
        })
        .catch(error => {
            alert('❌ Network Error: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    });
</script>
@endsection
