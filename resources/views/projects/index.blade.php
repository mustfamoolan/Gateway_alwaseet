@extends('layouts.admin')

@section('content')
    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 2rem; align-items: start;">
        
        <!-- projects List -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700;">Managed Projects</h2>
                <span style="color: var(--text-gray); font-size: 0.875rem;">{{ $projects->count() }} Total</span>
            </div>

            <div class="card" style="padding: 0;">
                <div class="data-table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Project & API Key</th>
                                <th>Waseet Credentials</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                                <tr>
                                    <td>
                                        <div style="font-weight: 700; font-size: 1rem; color: #fff; margin-bottom: 0.25rem;">{{ $project->name }}</div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <code id="api-key-{{ $project->id }}" style="background: rgba(255,255,255,0.05); padding: 0.15rem 0.4rem; border-radius: 4px; font-size: 0.75rem; color: var(--primary-glow);">{{ $project->api_key }}</code>
                                            <button onclick="copyToClipboard('{{ $project->api_key }}')" style="background: none; border: none; color: var(--text-gray); cursor: pointer; font-size: 0.75rem;">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.8rem; color: var(--text-gray);">User: <span style="color: #fff;">{{ $project->waseet_username }}</span></div>
                                        <div style="font-size: 0.8rem; color: var(--text-gray);">Pass: <span style="color: #666;">********</span></div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $project->is_active ? 'badge-success' : 'badge-danger' }}">
                                            {{ $project->is_active ? 'ACTIVE' : 'DISABLED' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 0.75rem;">
                                            <form action="{{ route('projects.toggle', $project) }}" method="POST">
                                                @csrf
                                                <button class="btn" style="padding: 0.4rem 0.8rem; background: rgba(255,255,255,0.05); color: #fff; font-size: 0.75rem;">
                                                    <i class="fas {{ $project->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn" style="padding: 0.4rem 0.8rem; background: rgba(239, 68, 68, 0.1); color: #EF4444; font-size: 0.75rem;">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; color: var(--text-gray); padding: 3rem;">No projects added. Use the sidebar to add your first project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add project Form -->
        <div class="card">
            <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; font-weight: 700; color: #fff;">Add New Project</h3>
            
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Project Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Aljabal Admin" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                </div>

                <div style="border-top: 1px solid var(--border); margin: 1.5rem 0; padding-top: 1.5rem;">
                    <p style="font-size: 0.75rem; color: var(--text-gray); margin-bottom: 1rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800;">Waseet Merchant Account</p>
                    
                    <div class="form-group">
                        <label>Merchant Username</label>
                        <input type="text" name="waseet_username" class="form-control" placeholder="User-XXXXXX" required>
                    </div>

                    <div class="form-group">
                        <label>Merchant Password</label>
                        <input type="password" name="waseet_password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Project & Key</button>
            </form>
        </div>

    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
            alert('API Key copied to clipboard!');
        }
    </script>
@endsection
