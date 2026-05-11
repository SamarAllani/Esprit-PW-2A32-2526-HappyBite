// WebAuthn (Face ID / Windows Hello) — adapté depuis PR ; URL du contrôleur pour ce dépôt.

function bufToBase64URL(buf) {
    return btoa(String.fromCharCode.apply(null, new Uint8Array(buf)))
        .replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function base64URLToBuf(base64url) {
    base64url = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const pad = base64url.length % 4;
    if (pad) base64url += '='.repeat(4 - pad);
    const str = atob(base64url);
    const buf = new Uint8Array(str.length);
    for (let i = 0; i < str.length; i++) buf[i] = str.charCodeAt(i);
    return buf.buffer;
}

function getControllerURL() {
    return new URL('../../Controllers/AuthProcess.php', window.location.href).href;
}

async function registerWithFace(email) {
    try {
        const controllerUrl = getControllerURL();
        const res = await fetch(controllerUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action: 'webauthn_register_challenge', email})
        });
        const text = await res.text();
        let options;
        try { options = JSON.parse(text); } catch (err) { console.error('Non-JSON response', text); alert('Erreur serveur'); return; }
        if (options.error) { alert(options.error); return; }

        options.challenge = base64URLToBuf(options.challenge);
        options.user.id = base64URLToBuf(options.user.id);
        options.pubKeyCredParams = options.pubKeyCredParams || [{type: 'public-key', alg: -7}];

        let cred;
        try {
            cred = await navigator.credentials.create({publicKey: options});
        } catch (e) {
            console.warn(e);
            alert('Face ID annulé ou non disponible.');
            return;
        }
        if (!cred) {
            alert('Enregistrement Face ID annulé.');
            return;
        }

        const attestationObject = cred.response.attestationObject;
        const clientDataJSON = cred.response.clientDataJSON;
        const id = cred.id;

        const payload = {
            action: 'webauthn_register_response',
            email,
            id,
            attestation: bufToBase64URL(attestationObject),
            clientData: bufToBase64URL(clientDataJSON)
        };

        const r = await fetch(controllerUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(payload)
        });
        const text2 = await r.text();
        let jr;
        try { jr = JSON.parse(text2); } catch (err) { console.error(text2); alert('Erreur serveur'); return; }
        if (jr.ok) alert('Face ID enregistré avec succès !'); else alert('Erreur lors de l\'enregistrement');
    } catch (e) {
        console.error(e);
        alert('Face ID indisponible: ' + e.message);
    }
}

async function authenticateWithFace(email) {
    try {
        const controllerUrl = getControllerURL();
        const res = await fetch(controllerUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({action: 'webauthn_auth_challenge', email})
        });
        const text = await res.text();
        let options;
        try { options = JSON.parse(text); } catch (err) { console.error(text); alert('Erreur serveur'); return; }
        if (options.error) { alert(options.error); return; }

        options.challenge = base64URLToBuf(options.challenge);
        if (options.allowCredentials) {
            options.allowCredentials = options.allowCredentials.map(c => ({
                id: base64URLToBuf(c.id),
                type: c.type || 'public-key'
            }));
        }

        let assertion;
        try {
            assertion = await navigator.credentials.get({publicKey: options});
        } catch (e) {
            console.warn(e);
            alert('Face ID annulé ou non disponible. Utilisez votre mot de passe.');
            return;
        }
        if (!assertion) {
            alert('Authentification Face ID annulée.');
            return;
        }

        const clientDataJSON = assertion.response.clientDataJSON;
        const authData = assertion.response.authenticatorData;
        const sig = assertion.response.signature;
        const id = assertion.id;

        const payload = {
            action: 'webauthn_auth_response',
            email,
            id,
            clientData: bufToBase64URL(clientDataJSON),
            authData: bufToBase64URL(authData),
            signature: bufToBase64URL(sig)
        };

        const r = await fetch(controllerUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(payload)
        });
        const text2 = await r.text();
        let jr;
        try { jr = JSON.parse(text2); } catch (err) { console.error(text2); alert('Erreur serveur'); return; }
        if (jr.ok) {
            window.location.href = '../Home.php';
        } else if (jr.error) {
            alert('Échec : ' + jr.error);
        } else {
            alert('Échec de l\'authentification');
        }
    } catch (e) {
        console.error(e);
        alert('Face ID indisponible: ' + e.message);
    }
}
