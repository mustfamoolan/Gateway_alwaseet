@extends('layouts.admin')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 800;">Request History</h2>
        <div style="display: flex; align-items: center; gap: 1rem;">
            @if(request('secret'))
                <form action="{{ route('logs.clear', ['secret' => request('secret')]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete ALL logs?')">
                    @csrf
                    <button type="submit" class="btn" style="background: #EF4444; color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-trash-alt"></i> Clear All Logs
                    </button>
                </form>
            @endif
            <div style="color: var(--text-gray); font-size: 0.875rem;">Showing last 25 requests</div>
        </div>
    </div>

    <div class="card" style="padding: 0;">
        <div class="data-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Project</th>
                        <th>Endpoint</th>
                        <th>Status</th>
                        <th>Code</th>
                        <th>Payloads</th>
                        <th>IP Address</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="color: var(--text-gray); font-size: 0.75rem;">#{{ $log->id }}</td>
                            <td>
                                <span style="font-weight: 700; color: #fff;">{{ $log->project->name }}</span>
                            </td>
                            <td><code>{{ $log->endpoint }}</code></td>
                            <td>
                                <span class="badge {{ $log->status === 'success' ? 'badge-success' : 'badge-danger' }}">
                                    {{ strtoupper($log->status) }}
                                </span>
                            </td>
                            <td>{{ $log->http_status_code }}</td>
                            <td>
                                <button onclick="toggleDetails({{ $log->id }})" class="btn" style="padding: 0.25rem 0.5rem; background: rgba(255,255,255,0.05); color: var(--primary-glow); font-size: 0.75rem;">
                                    <i class="fas fa-eye"></i> Details
                                </button>
                            </td>
                            <td style="font-family: monospace; color: var(--text-gray);">{{ $log->ip_address }}</td>
                            <td style="color: var(--text-gray); font-size: 0.85rem;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        <tr id="details-{{ $log->id }}" style="display: none; background: rgba(0,0,0,0.2);">
                            <td colspan="8" style="padding: 1.5rem;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <p style="font-size: 0.75rem; color: var(--text-gray); margin-bottom: 0.5rem; text-transform: uppercase;">Request Payload</p>
                                        <pre style="background: #000; padding: 1rem; border-radius: 8px; font-size: 0.75rem; color: #10B981; overflow-x: auto;">{{ json_encode($log->request_payload, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                    <div>
                                        <p style="font-size: 0.75rem; color: var(--text-gray); margin-bottom: 0.5rem; text-transform: uppercase;">Response Data</p>
                                        <pre style="background: #000; padding: 1rem; border-radius: 8px; font-size: 0.75rem; color: #818CF8; overflow-x: auto;">{{ json_encode($log->response_payload, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: var(--text-gray); padding: 5rem;">
                                <i class="fas fa-history" style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.3;"></i>
                                No traffic logs found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 1.5rem; display: flex; justify-content: center;">
        {{ $logs->links() }}
    </div>

    <script>
        function toggleDetails(id) {
            const row = document.getElementById('details-' + id);
            row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
        }
    </script>
@endsection
