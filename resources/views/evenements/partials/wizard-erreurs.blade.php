{{-- Erreurs de validation serveur propres à une étape de l'assistant de création --}}
@if($messages->isNotEmpty())
    <div class="wz-alert" role="alert">
        <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>À corriger :</strong>
        <ul>
            @foreach($messages as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
