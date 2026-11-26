/*
 * Listes déroulantes avec recherche (espace super admin).
 *
 * Tout <select> simple de plus de SEUIL options devient cherchable : au clic,
 * un champ de recherche apparaît en tête de la liste. Le <select> d'origine
 * reste dans la page (caché) et garde sa valeur : formulaires, filtres
 * « onchange=submit » et scripts existants continuent de fonctionner.
 *
 *   data-recherche="1"  force la recherche même sur une liste courte
 *   data-recherche="0"  la désactive
 *
 * Les options ajoutées ou retirées plus tard (listes chargées en AJAX) et
 * l'état disabled sont suivis automatiquement.
 */
(function () {
    'use strict';

    var SEUIL = 8;

    function normaliser(texte) {
        return String(texte || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '');
    }

    function doitEtreAmeliore(select) {
        if (select.multiple || select.dataset.rechercheActive) return false;
        if (select.dataset.recherche === '0') return false;
        if (select.dataset.recherche === '1') return true;
        return select.options.length > SEUIL;
    }

    function ameliorer(select) {
        select.dataset.rechercheActive = '1';

        var conteneur = document.createElement('div');
        conteneur.className = 'sr-conteneur';
        if (select.style.width) conteneur.style.width = select.style.width;
        if (select.style.maxWidth) conteneur.style.maxWidth = select.style.maxWidth;
        if (select.style.minWidth) conteneur.style.minWidth = select.style.minWidth;

        var bouton = document.createElement('button');
        bouton.type = 'button';
        bouton.className = select.className + ' sr-bouton';
        bouton.setAttribute('aria-haspopup', 'listbox');
        bouton.setAttribute('aria-expanded', 'false');
        if (select.id) {
            var label = document.querySelector('label[for="' + select.id + '"]');
            if (label) {
                label.addEventListener('click', function (ev) { ev.preventDefault(); bouton.focus(); });
            }
        }

        var panneau = document.createElement('div');
        panneau.className = 'sr-panneau';
        panneau.hidden = true;

        var champ = document.createElement('input');
        champ.type = 'search';
        champ.className = 'sr-champ';
        champ.placeholder = 'Rechercher…';
        champ.autocomplete = 'off';
        champ.setAttribute('aria-label', 'Rechercher dans la liste');

        var liste = document.createElement('ul');
        liste.className = 'sr-liste';
        liste.setAttribute('role', 'listbox');

        var vide = document.createElement('div');
        vide.className = 'sr-vide';
        vide.textContent = 'Aucun résultat';
        vide.hidden = true;

        panneau.appendChild(champ);
        panneau.appendChild(liste);
        panneau.appendChild(vide);

        select.parentNode.insertBefore(conteneur, select);
        conteneur.appendChild(select);
        conteneur.appendChild(bouton);
        conteneur.appendChild(panneau);
        select.classList.add('sr-natif');
        select.tabIndex = -1;

        var actif = -1;

        function majBouton() {
            var opt = select.options[select.selectedIndex];
            bouton.textContent = opt ? opt.text : '';
            bouton.disabled = select.disabled;
        }

        function visibles() {
            return Array.prototype.filter.call(liste.children, function (li) { return !li.hidden; });
        }

        function surligner(index) {
            var items = visibles();
            items.forEach(function (li) { li.classList.remove('sr-actif'); });
            if (!items.length) { actif = -1; return; }
            actif = Math.max(0, Math.min(index, items.length - 1));
            items[actif].classList.add('sr-actif');
            items[actif].scrollIntoView({ block: 'nearest' });
        }

        function construireListe() {
            liste.innerHTML = '';
            Array.prototype.forEach.call(select.options, function (opt, i) {
                if (opt.hidden) return;
                var li = document.createElement('li');
                li.className = 'sr-option';
                li.setAttribute('role', 'option');
                li.textContent = opt.text;
                li.dataset.index = i;
                li.dataset.cle = normaliser(opt.text);
                if (opt.disabled) li.classList.add('sr-desactive');
                if (i === select.selectedIndex) {
                    li.classList.add('sr-choisi');
                    li.setAttribute('aria-selected', 'true');
                }
                liste.appendChild(li);
            });
        }

        function filtrer() {
            var q = normaliser(champ.value.trim());
            var n = 0;
            Array.prototype.forEach.call(liste.children, function (li) {
                // Pendant une recherche, l'option vide « -- Choisir … -- » est masquée
                var vide = select.options[li.dataset.index].value === '';
                var ok = !q || (!vide && li.dataset.cle.indexOf(q) !== -1);
                li.hidden = !ok;
                if (ok) n++;
            });
            vide.hidden = n > 0;
            surligner(0);
        }

        function ouvrir() {
            if (select.disabled) return;
            fermerTous(conteneur);
            construireListe();
            champ.value = '';
            panneau.hidden = false;
            bouton.setAttribute('aria-expanded', 'true');
            conteneur.classList.add('sr-ouvert');
            filtrer();
            var items = visibles();
            var choisi = items.findIndex(function (li) { return li.classList.contains('sr-choisi'); });
            surligner(choisi >= 0 ? choisi : 0);
            champ.focus();
        }

        function fermer(rendreFocus) {
            if (panneau.hidden) return;
            panneau.hidden = true;
            bouton.setAttribute('aria-expanded', 'false');
            conteneur.classList.remove('sr-ouvert');
            if (rendreFocus) bouton.focus();
        }
        conteneur._srFermer = fermer;

        function choisir(li) {
            if (!li || li.classList.contains('sr-desactive')) return;
            var index = Number(li.dataset.index);
            var change = select.selectedIndex !== index;
            select.selectedIndex = index;
            majBouton();
            fermer(true);
            if (change) select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        bouton.addEventListener('click', function () {
            if (panneau.hidden) ouvrir(); else fermer(false);
        });
        bouton.addEventListener('keydown', function (ev) {
            if (['ArrowDown', 'ArrowUp', 'Enter', ' '].indexOf(ev.key) !== -1) {
                ev.preventDefault();
                ouvrir();
            } else if (ev.key.length === 1 && !ev.ctrlKey && !ev.metaKey && !ev.altKey) {
                // Taper directement une lettre ouvre la recherche avec cette lettre
                ev.preventDefault();
                ouvrir();
                champ.value = ev.key;
                filtrer();
            }
        });
        champ.addEventListener('input', filtrer);
        champ.addEventListener('keydown', function (ev) {
            if (ev.key === 'ArrowDown') { ev.preventDefault(); surligner(actif + 1); }
            else if (ev.key === 'ArrowUp') { ev.preventDefault(); surligner(actif - 1); }
            else if (ev.key === 'Enter') { ev.preventDefault(); choisir(visibles()[actif]); }
            else if (ev.key === 'Escape') { ev.preventDefault(); fermer(true); }
            else if (ev.key === 'Tab') { fermer(false); }
        });
        liste.addEventListener('mousedown', function (ev) { ev.preventDefault(); });
        liste.addEventListener('click', function (ev) {
            choisir(ev.target.closest('.sr-option'));
        });

        // Valeur changée par un script, options rechargées, disabled basculé
        select.addEventListener('change', majBouton);
        new MutationObserver(function () {
            majBouton();
            if (!panneau.hidden) { construireListe(); filtrer(); }
        }).observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled'] });
        // select.value = … ne déclenche aucun évènement : on resynchronise au focus/survol
        conteneur.addEventListener('pointerenter', majBouton);
        if (select.form) select.form.addEventListener('reset', function () { setTimeout(majBouton, 0); });

        majBouton();
    }

    function fermerTous(sauf) {
        document.querySelectorAll('.sr-conteneur.sr-ouvert').forEach(function (c) {
            if (c !== sauf && c._srFermer) c._srFermer(false);
        });
    }

    document.addEventListener('mousedown', function (ev) {
        if (!ev.target.closest('.sr-conteneur')) fermerTous(null);
    });

    function scanner(racine) {
        (racine.querySelectorAll ? racine : document).querySelectorAll('select').forEach(function (s) {
            if (doitEtreAmeliore(s)) ameliorer(s);
        });
    }

    function init() {
        scanner(document);
        // Listes courtes qui grossissent après un chargement AJAX
        new MutationObserver(function () {
            document.querySelectorAll('select:not([data-recherche-active])').forEach(function (s) {
                if (doitEtreAmeliore(s)) ameliorer(s);
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.SelectRecherche = { appliquer: function (select) { if (!select.dataset.rechercheActive) ameliorer(select); } };
})();
