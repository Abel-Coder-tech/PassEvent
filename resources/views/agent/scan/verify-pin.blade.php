@extends('layouts.agent')

@section('title', 'Code d\'accès — Agent PaxEvent')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card-agent p-4 text-center">
                <div class="mb-3">
                    <div style="width:70px;height:70px;border-radius:50%;background:rgba(84,38,128,0.08);display:flex;align-items:center;justify-content:center;margin:0 auto;">
                        <i class="bi bi-shield-lock" style="font-size:1.8rem;color:var(--violet);"></i>
                    </div>
                </div>
                <h5 class="fw-bold">Code d'accès requis</h5>
                <p class="text-muted small mb-4">Saisissez le code à 6 chiffres pour débuter le scan pour <strong>{{ Auth::guard('agent')->user()->evenement->titre }}</strong></p>

                @if($errors->any())
                    <div class="alert alert-danger py-2" style="font-size:0.85rem;border-radius:10px;">
                        @foreach($errors->all() as $e) {{ $e }} @break @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('agent.scan.check-pin') }}">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="code_acces" class="form-control text-center" 
                               style="font-size:2rem;letter-spacing:8px;font-weight:700;border-radius:10px;"
                               maxlength="6" inputmode="numeric" pattern="[0-9]*" required autofocus
                               placeholder="••••••">
                    </div>
                    <button type="submit" class="btn btn-violet w-100">
                        <i class="bi bi-unlock me-1"></i>Valider le code
                    </button>
                    <a href="{{ route('agent.dashboard') }}" class="btn btn-outline-violet w-100 mt-2">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
