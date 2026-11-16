<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Déclaration sur l'honneur — Autorisation de gestion de compte</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11.5px; color: #211c31; line-height: 1.55; margin: 0; }
    .bandeau { background: #542680; color: #fff; padding: 14px 22px; }
    .bandeau h1 { font-size: 16px; margin: 0; }
    .bandeau p { margin: 2px 0 0; font-size: 10.5px; opacity: .9; }
    .corps { padding: 18px 26px; }
    h2 { font-size: 12.5px; color: #542680; margin: 16px 0 5px; }
    ul { margin: 4px 0 8px 16px; padding: 0; }
    li { margin-bottom: 3px; }
    .cadre { border: 1px solid #d9c9e6; background: #f6f1fa; padding: 10px 14px; margin-top: 16px; }
    .signatures { margin-top: 26px; width: 100%; }
    .signatures td { width: 50%; vertical-align: top; padding-right: 20px; }
    .ligne { border-bottom: 1px solid #211c31; height: 46px; margin-top: 4px; }
    .pied { margin-top: 20px; font-size: 9.5px; color: #6b6474; }
</style>
</head>
<body>
<div class="bandeau">
    <h1>Déclaration sur l'honneur — Autorisation de gestion de compte</h1>
    <p>PaxEvent, billetterie en ligne</p>
</div>
<div class="corps">
    <p>
        Je soussigné(e) <strong>{{ $user->nom }}</strong>@if($user->organisation), représentant(e) de <strong>{{ $user->organisation }}</strong>@endif,
        titulaire du compte organisateur PaxEvent associé à l'adresse <strong>{{ $user->email }}</strong>, déclare sur l'honneur
        <strong>autoriser PaxEvent (le mandataire) à agir en mon nom et pour mon compte</strong> sur mon espace organisateur.
    </p>

    <h2>Ce que PaxEvent peut faire en mon nom</h2>
    <ul>
        <li>compléter mon profil, mes documents et ma signature ;</li>
        <li>créer, modifier et publier mes événements, ouvrir ou fermer la billetterie ;</li>
        <li>supprimer, à ma demande, un événement pour lequel aucun ticket n'a été émis ;</li>
        <li>gérer mes tarifs et mes codes promos ;</li>
        <li>créer et gérer mes agents de scan et de vente.</li>
    </ul>

    <h2>Ce que PaxEvent ne peut pas faire en mon nom</h2>
    <ul>
        <li>demander un retrait d'argent ou modifier mes numéros de paiement ;</li>
        <li>modifier mon adresse email ou mon mot de passe ;</li>
        <li>supprimer mon compte ;</li>
        <li>vendre des billets ni commander des tickets physiques depuis mon compte.</li>
    </ul>

    <h2>Transparence et retrait</h2>
    <p>
        Chaque action réalisée par PaxEvent est enregistrée dans un journal que je peux consulter depuis mes paramètres.
        Je peux retirer cette autorisation à tout moment depuis mon tableau de bord ; le retrait prend effet immédiatement.
    </p>

    <div class="cadre">
        @if($mode === 'organisateur')
            Autorisation accordée en ligne par <strong>{{ $user->nom }}</strong> le <strong>{{ $date->isoFormat('D MMMM YYYY [à] HH[h]mm') }}</strong>@if($ip) (adresse IP {{ $ip }})@endif.
        @elseif($mode === 'creation_paxevent')
            Compte créé par PaxEvent à la demande de l'organisateur. L'autorisation est accordée dès la création du compte et enregistrée par
            <strong>{{ $par }}</strong> le <strong>{{ $date->isoFormat('D MMMM YYYY [à] HH[h]mm') }}</strong>.
        @elseif($mode === 'paxevent_recueilli')
            Autorisation recueillie par PaxEvent auprès de l'organisateur et enregistrée par <strong>{{ $par }}</strong>
            le <strong>{{ $date->isoFormat('D MMMM YYYY [à] HH[h]mm') }}</strong>. Le justificatif signé est conservé.
        @else
            Fait à ______________________, le ____ / ____ / ________
        @endif
    </div>

    @if($mode === 'modele')
    <table class="signatures">
        <tr>
            <td>Signature de l'organisateur<br>(précédée de « Lu et approuvé »)<div class="ligne"></div></td>
            <td>Pour PaxEvent, le mandataire<div class="ligne"></div></td>
        </tr>
    </table>
    @endif

    <div class="pied">Document généré par PaxEvent le {{ now()->isoFormat('D MMMM YYYY [à] HH[h]mm') }}.</div>
</div>
</body>
</html>
