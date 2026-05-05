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
            paypalVerified: 'paypal-verified'
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
            if (sel.value !== 'paypal') {
                var verified = document.getElementById('paypal-verified');
                var status = document.getElementById('paypal-status');
                if (verified) verified.value = '0';
                if (status) status.hidden = true;
            }
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
            I.cashMontant, I.cashContact, I.cashNote
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
            var ver = document.getElementById(I.paypalVerified);
            if (!ver || ver.value !== '1') {
                return 'Pour PayPal : connectez-vous via le bouton "Se connecter a PayPal" (ou Face ID), puis finalisez.';
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
                id === 'cash-note'
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

    function bindPaypalModal() {
        var modal = document.getElementById('paypal-modal');
        var openBtn = document.getElementById('paypal-auth-btn');
        var cancelBtn = document.getElementById('paypal-login-cancel');
        var loginBtn = document.getElementById('paypal-login-submit');
        var faceBtn = document.getElementById('paypal-faceid-submit');
        var loginEmail = document.getElementById('paypal-login-email');
        var loginPass = document.getElementById('paypal-login-password');
        var verified = document.getElementById('paypal-verified');
        var status = document.getElementById('paypal-status');
        var msg = document.getElementById('paypal-modal-msg');
        var selectMode = document.getElementById('mode-paiement');

        if (!modal || !openBtn || !cancelBtn || !loginBtn || !faceBtn || !verified || !status || !msg || !selectMode) return;

        function openModal() {
            if (selectMode.value !== 'paypal') return;
            modal.hidden = false;
            modal.setAttribute('aria-hidden', 'false');
            msg.hidden = true;
            msg.textContent = '';
            if (loginEmail) loginEmail.focus();
        }

        function closeModal() {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
        }

        function setSuccess(emailValue, nameValue) {
            verified.value = '1';
            status.hidden = false;
            closeModal();
        }

        openBtn.addEventListener('click', openModal);
        cancelBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeModal();
        });

        loginBtn.addEventListener('click', function () {
            var em = loginEmail ? loginEmail.value.trim() : '';
            var pw = loginPass ? loginPass.value.trim() : '';
            if (!isValidEmailLoose(em) || pw.length < 4) {
                msg.hidden = false;
                msg.textContent = 'Entrez un email valide et un mot de passe.';
                return;
            }
            msg.hidden = false;
            msg.textContent = 'Connexion PayPal... paiement en cours...';
            setTimeout(function () {
                setSuccess(em, em.split('@')[0] || 'Compte PayPal');
            }, 900);
        });

        faceBtn.addEventListener('click', function () {
            msg.hidden = false;
            msg.textContent = 'Authentification Face ID...';
            setTimeout(function () {
                setSuccess('client@paypal.com', 'Utilisateur Face ID');
            }, 900);
        });
    }

    function run() {
        var form = document.getElementById('form-commande');
        if (!form) return;

        initModePanels();
        bindModePaiementChange(form);
        bindFormCommandeDelegated(form);
        bindCommandeSubmit(form);
        bindPaypalModal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', run);
    } else {
        run();
    }
})();
