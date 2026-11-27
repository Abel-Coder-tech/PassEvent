{{--
    Parcours d'un nouvel agent, en étapes numérotées (styles en ligne : emails).
    Variables : $type ('scan'|'vente'), $email, $motDePasse, $codeAcces (scan),
                $lienApp (null si aucune version publiée), $lienExpireLe, $lienWeb.
--}}
@php
    $etape = 0;
    $pastille = 'display:inline-block;width:26px;height:26px;line-height:26px;border-radius:50%;background:#542680;color:#fff;font-weight:700;font-size:0.85rem;text-align:center;';
    $titre = 'margin:0;font-size:0.95rem;color:#211C31;font-weight:700;';
    $texte = 'margin:0.35rem 0 0;font-size:0.83rem;color:#555;line-height:1.5;';
    $code = 'background:#fff;border:1px solid #d9c9e8;padding:0.15rem 0.5rem;border-radius:4px;color:#211C31;font-family:monospace;';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:1.25rem 0;border-collapse:collapse;">
    {{-- 1. Installer l'application (ou ouvrir l'espace web si aucune version n'est publiée) --}}
    <tr>
        <td width="38" valign="top" style="padding:0.75rem 0;"><span style="{{ $pastille }}">{{ ++$etape }}</span></td>
        <td style="padding:0.75rem 0;border-bottom:1px solid #eee;">
            @if($lienApp)
                <p style="{{ $titre }}">Installez l'application PaxAgent</p>
                <p style="{{ $texte }}">Sur votre téléphone Android, touchez le bouton puis ouvrez le fichier téléchargé. Si Android le demande, autorisez l'installation depuis cette source.</p>
                <p style="margin:0.75rem 0 0;">
                    <a href="{{ $lienApp }}" style="display:inline-block;background:#542680;color:#fff;text-decoration:none;padding:0.7rem 1.6rem;border-radius:10px;font-weight:700;font-size:0.9rem;">Télécharger PaxAgent</a>
                </p>
                <p style="margin:0.4rem 0 0;font-size:0.72rem;color:#888;">Lien personnel, valable jusqu'au {{ $lienExpireLe->isoFormat('D MMMM YYYY') }}. Ne le transférez pas.</p>
            @else
                <p style="{{ $titre }}">Ouvrez votre espace agent</p>
                <p style="{{ $texte }}">Depuis le navigateur de votre téléphone.</p>
                <p style="margin:0.75rem 0 0;">
                    <a href="{{ $lienWeb }}" style="display:inline-block;background:#542680;color:#fff;text-decoration:none;padding:0.7rem 1.6rem;border-radius:10px;font-weight:700;font-size:0.9rem;">Ouvrir mon espace</a>
                </p>
            @endif
        </td>
    </tr>

    {{-- 2. Se connecter --}}
    <tr>
        <td width="38" valign="top" style="padding:0.75rem 0;"><span style="{{ $pastille }}">{{ ++$etape }}</span></td>
        <td style="padding:0.75rem 0;border-bottom:1px solid #eee;">
            <p style="{{ $titre }}">Connectez-vous avec ces identifiants</p>
            <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:0.5rem;background:#f6f2f9;border-radius:10px;width:100%;">
                <tr><td style="padding:0.75rem 1rem;font-size:0.85rem;color:#542680;line-height:1.9;">
                    <strong>Email :</strong> <span style="{{ $code }}">{{ $email }}</span><br>
                    <strong>Mot de passe :</strong> <span style="{{ $code }}">{{ $motDePasse }}</span>
                </td></tr>
            </table>
        </td>
    </tr>

    @if($type === 'scan')
    {{-- 3. Débloquer le scan --}}
    <tr>
        <td width="38" valign="top" style="padding:0.75rem 0;"><span style="{{ $pastille }}">{{ ++$etape }}</span></td>
        <td style="padding:0.75rem 0;border-bottom:1px solid #eee;">
            <p style="{{ $titre }}">Débloquez le scan avec votre code</p>
            <p style="{{ $texte }}">Touchez « Accéder au scan », puis saisissez ce code à 4 chiffres :</p>
            <p style="margin:0.5rem 0 0;"><span style="{{ $code }}font-size:1.3rem;letter-spacing:6px;font-weight:700;padding:0.3rem 0.8rem;">{{ $codeAcces }}</span></p>
        </td>
    </tr>

    {{-- 4. Scanner --}}
    <tr>
        <td width="38" valign="top" style="padding:0.75rem 0;"><span style="{{ $pastille }}">{{ ++$etape }}</span></td>
        <td style="padding:0.75rem 0;">
            <p style="{{ $titre }}">Scannez les billets à l'entrée</p>
            <p style="{{ $texte }}">Visez le QR code du billet (téléphone ou papier). Le scan est ouvert le jour de l'événement.</p>
        </td>
    </tr>
    @else
    {{-- 3. Vendre --}}
    <tr>
        <td width="38" valign="top" style="padding:0.75rem 0;"><span style="{{ $pastille }}">{{ ++$etape }}</span></td>
        <td style="padding:0.75rem 0;">
            <p style="{{ $titre }}">Vendez les billets</p>
            <p style="{{ $texte }}">Choisissez le billet, saisissez l'acheteur et encaissez (espèces ou Mobile Money). L'acheteur reçoit son billet par email, et vous pouvez aussi lui remettre le PDF.</p>
        </td>
    </tr>
    @endif
</table>

<p style="margin:0.5rem 0 0;font-size:0.75rem;color:#888;">
    Gardez ces identifiants pour vous. Pas de téléphone Android ?
    <a href="{{ $lienWeb }}" style="color:#542680;">Utilisez la version web</a>.
</p>
