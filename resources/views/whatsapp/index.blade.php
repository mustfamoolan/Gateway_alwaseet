@extends('layouts.admin')

@section('content')
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start;">
        
        <!-- WhatsApp Projects Table -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; mb-1.5rem; margin-bottom: 20px;">
                <h2 style="font-size: 1.25rem; font-weight: 700;">WhatsApp API Channels</h2>
                <p style="color: var(--text-gray); font-size: 0.875rem;">Manage your messaging connections</p>
            </div>

            <div class="card" style="padding: 0;">
                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Owner</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div style="width: 32px; height: 32px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                                <i class="fab fa-whatsapp"></i>
                                            </div>
                                            <span style="font-weight: 700; color: #fff;">{{ $project->name }}</span>
                                        </div>
                                    </td>
                                    <td style="color: var(--text-gray);">{{ $project->owner_name }}</td>
                                    <td>
                                        <span class="badge {{ $project->status === 'connected' ? 'badge-success' : 'badge-danger' }}" style="background: {{ $project->status === 'connected' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; color: {{ $project->status === 'connected' ? '#10b981' : '#f59e0b' }};">
                                            {{ strtoupper($project->status) }}
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                            <a href="{{ route('whatsapp.show', $project->id) }}" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: rgba(255,255,255,0.05); border: 1px solid var(--border);">
                                                <i class="fas fa-plug" style="margin-right: 0.3rem;"></i> Manage
                                            </a>
                                            <form action="{{ route('whatsapp.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 3rem;">No WhatsApp channels created yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add New Connection Form -->
        <div class="card">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; font-weight: 700; color: #fff;">New WhatsApp Connection</h3>
            
            <form action="{{ route('whatsapp.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Channel Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Sales Alerts" required>
                </div>
                
                <div class="form-group">
                    <label>Responsible Person</label>
                    <input type="text" name="owner_name" class="form-control" placeholder="e.g. Ahmad" required>
                </div>

                <div style="background: rgba(79, 70, 229, 0.05); border: 1px dashed rgba(79, 70, 229, 0.2); border-radius: 12px; padding: 1rem; margin: 1.5rem 0;">
                    <p style="font-size: 0.75rem; color: var(--primary-glow); font-weight: 600; line-height: 1.4;">
                        <i class="fas fa-info-circle"></i> Once created, you will need to scan the QR code to link your account.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Initialize Channel</button>
            </form>
        </div>

    </div>
@endsection
