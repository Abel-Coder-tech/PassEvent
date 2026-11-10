<h2>Alerte de suivi des paiements PaxEvent</h2>

<p>Un événement de paiement nécessite une vérification.</p>

<p><strong>Action :</strong> {{ $action }}</p>
<p><strong>Date :</strong> {{ now()->format('d/m/Y H:i:s') }}</p>

<h3>Détails techniques</h3>
<ul>
    @foreach ($details as $key => $value)
        <li>
            <strong>{{ str_replace('_', ' ', $key) }} :</strong>
            {{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value }}
        </li>
    @endforeach
</ul>

<p>Les journaux détaillés sont disponibles dans l’administration PaxEvent.</p>
