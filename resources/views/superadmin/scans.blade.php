@extends('superadmin.layouts.master')

@section('title', 'Scans - Super Admin')
@section('page-title', 'Scans')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-qr-code me-2" style="color: var(--sa-primary);"></i>Historique des scans</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Evenement</th><th>Action</th><th>Details</th><th>IP</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->ticket?->evenement?->titre ?? 'N/A' }}</td>
                    <td><span class="sa-badge sa-badge-info">{{ $log->type_operation }}</span></td>
                    <td style="font-size:0.75rem; max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $log->details ? (is_string($log->details) ? $log->details : json_encode($log->details)) : '-' }}
                    </td>
                    <td style="font-family:monospace;font-size:0.75rem;">{{ $log->ip ?? '-' }}</td>
                    <td style="font-size:0.75rem;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $logs->links() }}</div>
@endsection
