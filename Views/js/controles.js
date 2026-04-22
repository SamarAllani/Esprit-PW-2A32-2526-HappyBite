/**
 * Contrôles formulaire commande (FrontOffice — commande.php uniquement).
 * Mode de paiement : affichage des blocs, formatage et validation (comme list-com-liv / BackOffice).
 */
(function () {
    'use strict';

    var MODE_KEYS = ['carte', 'cash', 'paypal'];

    var PANELS = {
        carte: 'carte-paiement-details',
        cash: 'cash-paiement-details',
        paypal: 'paypal-paiement-details'
    };

    function ids() {
        return {
            reduction: 'reduction',
            carteTitulaire: 'carte-titulaire',
            carteNumero: 'carte-numero',
            carteExpiration: 'carte-expiration',
            carteCvv: 'carte-cvv',
            cashMontant: 'cash-montant',
            cashContact: 'cash-contact',
            cashNote: 'cash-note',
            paypalEmail: 'paypal-email',
            paypalNom: 'paypal-nom'
        };
    }

    function initModePanels() {
        var sel = document.getElementById('mode-paiement');
        if (!sel) return;
        function sync() {
            var v = sel.value;
            MODE_KEYS.forEach(function (key) {
                var el = document.getElementById(PANELS[key]);
                if (!el) return;
                var show = v === key;
                el.hidden = !show;
                el.setAttribute('aria-hidden', show ? 'false' : 'true');
            });
        }
        sel.addEventListener('change', sync);
        sync();
    }

    function bindModePaiementChange(form) {
        var sel = document.getElementById('mode-paiement');
        if (!sel || !form) return;
        sel.addEventListener('change', function () {
            clearPanelErrors();
            hideFormMessage(form);
        });
    }

    function onlyDigits(str) {
        return str.replace(/\D/g, '');
    }

    function formatCardNumber(el) {
        var raw = onlyDigits(el.value).slice(0, 19);
        var parts = raw.match(/.{1,4}/g);
        el.value = parts ? parts.join(' ') : '';
    }

    function formatExpiration(el) {
        var d = onlyDigits(el.value).slice(0, 4);
        el.value = d.length > 2 ? d.slice(0, 2) + '/' + d.slice(2) : d;
    }

    function formatCvv(el) {
        el.value = onlyDigits(el.value).slice(0, 4);
    }

    function isValidCardNumber(value) {
        var n = onlyDigits(value);
        return n.length >= 13 && n.length <= 19;
    }

    function isValidExpiration(value) {
        var m = /^(\d{2})\/(\d{2})$/.exec(value.trim());
        if (!m) return false;
        var mo = parseInt(m[1], 10);
        return mo >= 1 && mo <= 12;
    }

    function isValidCvv(value) {
        return /^\d{3,4}$/.test(value.trim());
    }

    function isValidPhoneLoose(value) {
        var t = value.trim();
        if (t === '') return false;
        return /^[\d\s+().-]{8,}$/.test(t);
    }

    function isValidEmailLoose(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
    }

    function anyFilled(inputs) {
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i] && inputs[i].value.trim() !== '') return true;
        }
        return false;
    }

    function clearClassErr(el) {
        if (el) el.classList.remove('controle-erreur');
    }

    function setClassErr(el) {
        if (el) el.classList.add('controle-erreur');
    }

    function clearPanelErrors() {
        var I = ids();
        [
            I.carteTitulaire, I.carteNumero, I.carteExpiration, I.carteCvv,
            I.cashMontant, I.cashContact, I.cashNote, I.paypalEmail, I.paypalNom
        ].forEach(function (id) {
            clearClassErr(document.getElementById(id));
        });
    }

    function showFormMessage(form, text) {
        var box = form.querySelector('.controle-js-message');
        if (!box) {
            box = document.createElement('p');
            box.className = 'controle-js-message commande-flash-erreur';
            box.setAttribute('role', 'alert');
            form.insertBefore(box, form.firstChild);
        }
        box.textContent = text;
        box.hidden = false;
        box.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    function hideFormMessage(form) {
        var box = form.querySelector('.controle-js-message');
        if (box) {
            box.hidden = true;
            box.textContent = '';
        }
    }

    function validateCommandeForm(form) {
        hideFormMessage(form);
        clearPanelErrors();
        var I = ids();
        var sel = document.getElementById('mode-paiement');
        if (!sel || sel.value === '') {
            return 'Veuillez choisir un mode de paiement.';
        }

        var mode = sel.value;
        if (mode === 'carte') {
            var tit = document.getElementById(I.carteTitulaire);
            var num = document.getElementById(I.carteNumero);
            var exp = document.getElementById(I.carteExpiration);
            var cvv = document.getElementById(I.carteCvv);
            var ok = true;
            if (!tit || tit.value.trim().length < 2) {
                setClassErr(tit);
                ok = false;
            }
            if (!num || !isValidCardNumber(num.value)) {
                setClassErr(num);
                ok = false;
            }
            if (!exp || !isValidExpiration(exp.value)) {
                setClassErr(exp);
                ok = false;
            }
            if (!cvv || !isValidCvv(cvv.value)) {
                setClassErr(cvv);
                ok = false;
            }
            if (!ok) {
                return 'Complétez tous les champs carte correctement (titulaire, numéro 13–19 chiffres, expiration MM/AA, CVV 3–4 chiffres).';
            }
        } else if (mode === 'cash') {
            var m = document.getElementById(I.cashMontant);
            var tel = document.getElementById(I.cashContact);
            var note = document.getElementById(I.cashNote);
            var okC = true;
            if (!m || m.value.trim() === '') {
                setClassErr(m);
                okC = false;
            }
            if (!tel || !isValidPhoneLoose(tel.value)) {
                setClassErr(tel);
                okC = false;
            }
            if (!note || note.value.trim().length < 2) {
                setClassErr(note);
                okC = false;
            }
            if (!okC) {
                return 'Pour le paiement cash : remplissez tous les champs (montant, téléphone valide 8+ caractères et note min. 2 caractères).';
            }
        } else if (mode === 'paypal') {
            var em = document.getElementById(I.paypalEmail);
            var nom = document.getElementById(I.paypalNom);
            var okP = true;
            if (!em || !isValidEmailLoose(em.value)) {
                setClassErr(em);
                okP = false;
            }
            if (!nom || nom.value.trim().length < 2) {
                setClassErr(nom);
                okP = false;
            }
            if (!okP) {
                return 'Pour PayPal : remplissez tous les champs avec un e-mail valide et un nom d’au moins 2 caractères.';
            }
        }

        return null;
    }

    function bindFormCommandeDelegated(form) {
        if (!form || form.id !== 'form-commande') return;

        form.addEventListener('input', function (e) {
            var t = e.target;
            if (!t || t.tagName !== 'INPUT') return;
            var id = t.id;
            if (id === 'carte-numero') {
                formatCardNumber(t);
                clearClassErr(t);
                return;
            }
            if (id === 'carte-expiration') {
                formatExpiration(t);
                clearClassErr(t);
                return;
            }
            if (id === 'carte-cvv') {
                formatCvv(t);
                clearClassErr(t);
                return;
            }
            if (
                id === 'carte-titulaire' ||
                id === 'cash-montant' ||
                id === 'cash-contact' ||
                id === 'cash-note' ||
                id === 'paypal-email' ||
                id === 'paypal-nom'
            ) {
                clearClassErr(t);
            }
        });

        form.addEventListener(
            'blur',
            function (e) {
                var t = e.target;
                if (t && t.id === 'carte-numero') {
                    formatCardNumber(t);
                }
            },
            true
        );
    }

    function bindCommandeSubmit(form) {
        if (!form) return;
        form.addEventListener('submit', function (e) {
            var err = validateCommandeForm(form);
            if (err) {
                e.preventDefault();
                showFormMessage(form, err);
            }
        });
    }

    function run() {
        var form = document.getElementById('form-commande');
        if (!form) return;

        initModePanels();
        bindModePaiementChange(form);
        bindFormCommandeDelegated(form);
        bindCommandeSubmit(form);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
