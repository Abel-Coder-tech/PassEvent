@extends('layouts.public')

@section('title', 'Connexion Agent de vente')
@section('description', 'Connectez-vous à votre espace de vente PassEvent.')

@section('content')
<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 50%, #f5f3ff 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-purple-100" style="width: 64px; height: 64px;">
                        <i class="bi bi-cart-check text-purple-700" style="font-size: 1.75rem;"></i>
                    </div>
                    <h4 class="mt-3 fw-bold">Espace vente</h4>
                    <p class="text-muted small">Connectez-vous pour vendre des tickets</p>
                </div>

                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-4">
                        @if ($errors->any())
                        <div class="alert alert-danger py-2 small">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('agent-vente.login.post') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Email</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-medium">Mot de passe</label>
                                <div class="position-relative">
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                        required style="padding-right: 2.6rem;">
                                    <button type="button" class="btn position-absolute border-0 bg-transparent toggle-password" style="right: 8px; top: 50%; transform: translateY(-50%); padding: 4px; z-index: 5;">
                                        <i class="bi bi-eye" style="color: #9a9a9a;"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn w-100 text-white py-2 fw-medium" style="background: #7c3aed;">
                                Se connecter
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-center mt-3 small text-muted">
                    <i class="bi bi-arrow-left"></i>
                    <a href="{{ url('/') }}" class="text-decoration-none">Retour à l'accueil</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (!input) return;
        const icon = this.querySelector('i');
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });
});
</script>
@endpush
