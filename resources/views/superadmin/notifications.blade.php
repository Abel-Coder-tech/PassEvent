@extends('superadmin.layouts.master')

@section('title', 'Notifications - Super Admin')
@section('page-title', 'Notifications')

@section('content')
<div class="sa-card">
    <div class="sa-card-header">
        <span><i class="bi bi-bell-fill me-2" style="color: var(--sa-primary);"></i>Notifications</span>
    </div>
    <div class="sa-card-body p-0">
        <table class="sa-table">
            <thead>
                <tr><th>Type</th><th>Expéditeur</th><th>Objet</th><th>Message</th><th>Lu</th><th>Date</th></tr>
            </thead>
            <tbody>
                @foreach($messages as $msg)
                <tr>
                    <td>
                        @if(str_starts_with($msg->objet, '[Remboursement]'))
                            <span class="sa-badge" style="background:#f59e0b;color:#fff;">Remboursement</span>
                        @else
                            <span class="sa-badge sa-badge-primary">Contact</span>
                        @endif
                    </td>
                    <td><strong>{{ $msg->nom_complet }}</strong><br><small style="font-size:0.7rem;">{{ $msg->email }}</small></td>
                    <td>{{ $msg->objet }}</td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $msg->message }}</td>
                    <td>
                        @if($msg->lu) <span class="sa-badge sa-badge-success">Lu</span>
                        @else <span class="sa-badge sa-badge-danger">Non lu</span>
                        @endif
                    </td>
                    <td style="font-size:0.75rem;">{{ $msg->created_at->isoFormat('D MMM YYYY HH:mm') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3 d-flex justify-content-center">{{ $messages->links() }}</div>
@endsection
