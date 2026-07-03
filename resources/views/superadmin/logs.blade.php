@extends('superadmin.layouts.master')

@section('title', 'Logs système - Super Admin')
@section('page-title', 'Logs systeme')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-journal-text me-2" style="color: var(--sa-primary);"></i>Journal des actions</span>
        <span class="text-muted" style="font-size:0.8rem;">{{ $logs->total() }} entrees</span>
    </div>
    <div class="sa-card-body p-0" style="overflow-x:auto;">
        <table class="sa-table">
            <thead>
                <tr><th>Admin</th><th>Action</th><th>Evenement</th><th>Details</th><th>IP</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>
                        @if($log->ticket_id)
                            <span style="font-size:0.75rem;">{{ $log->ticket?->email_acheteur ?? '-' }}</span>
                        @else
                            <span class="text-muted" style="font-size:0.75rem;">Systeme</span>
                        @endif
                    </td>
                    <td><span class="sa-badge sa-badge-info">{{ $log->type_operation }}</span></td>
                    <td>{{ $log->ticket?->evenement?->titre ?? 'N/A' }}</td>
                    <td style="font-size:0.72rem; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $log->details ? (is_string($log->details) ? \Illuminate\Support\Str::limit($log->details, 60) : \Illuminate\Support\Str::limit(json_encode($log->details), 60)) : '-' }}
                    </td>
                    <td style="font-family:monospace;font-size:0.75rem;">{{ $log->ip ?? '-' }}</td>
                    <td style="font-size:0.72rem; white-space:nowrap;">{{ $log->created_at->isoFormat('D MMM YYYY HH:mm:ss') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $logs->links() }}</div>
@endsection
