{{-- Graphe de ventes partagé : tableau de bord et statistiques --}}
<div class="panel-card">
    <div class="panel-card-header">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-nowrap w-100">
            <h5 class="mb-0 text-truncate" style="min-width: 0;"><i class="bi bi-graph-up me-2" style="color: var(--vert);"></i>Ventes de tickets — {{ $periodeGraphique }}</h5>
            <select class="form-select form-select-sm w-auto flex-shrink-0" aria-label="Choisir une période" onchange="if(this.value){window.location.href=this.value;}">
                <option value="{{ route($routePeriode, ['periode' => '7']) }}" @selected($periode === '7')>7 jours</option>
                <option value="{{ route($routePeriode, ['periode' => '30']) }}" @selected($periode === '30')>30 jours</option>
                <option value="{{ route($routePeriode, ['periode' => '90']) }}" @selected($periode === '90')>3 mois</option>
                <option value="{{ route($routePeriode, ['periode' => 'annee']) }}" @selected($periode === 'annee')>Cette année</option>
                <option value="{{ route($routePeriode, ['periode' => 'tout']) }}" @selected($periode === 'tout')>Tout</option>
            </select>
        </div>
    </div>
    <div class="panel-card-body">
        <div style="height: 300px;">
            <canvas id="ventesChart"></canvas>
        </div>
    </div>
</div>

@section('chart-scripts')
<script>
const ventesData = @json($ventesParJour);

const ventesLabels = ventesData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' });
});

new Chart(document.getElementById('ventesChart'), {
    type: 'line',
    data: {
        labels: ventesLabels,
        datasets: [
            @foreach($ventesCategories as $categorie)
            {
                label: @json($categorie['label']),
                data: ventesData.map(d => d[@json($categorie['key'])] ?? 0),
                borderColor: @json($categorie['couleur']),
                backgroundColor: @json($categorie['fond']),
                tension: 0.3,
                fill: true,
                pointRadius: 3,
            }{{ $loop->last ? '' : ',' }}
            @endforeach
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    usePointStyle: true,
                    pointStyle: 'circle',
                    font: { size: 11 }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: { font: { size: 11 } }
            },
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 }, maxRotation: 45 }
            }
        }
    }
});
</script>
@endsection
