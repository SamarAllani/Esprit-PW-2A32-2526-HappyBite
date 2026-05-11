/**
 * Face ID auth — même principe que commande.php (caméra, détection visage, capture JPEG).
 * Envoie le cliché à AuthProcess (face_enroll / face_login).
 */
(function () {
    'use strict';

    function controllerUrl() {
        return new URL('../../Controllers/AuthProcess.php', window.location.href).href;
    }

    function getEl(id) {
        return document.getElementById(id);
    }

    function stopCamera(stream, videoEl) {
        if (stream && stream.getTracks) {
            stream.getTracks().forEach(function (t) {
                t.stop();
            });
        }
        if (videoEl) {
            videoEl.srcObject = null;
        }
    }

    function startCamera(videoEl) {
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            return Promise.reject(new Error('CAMERA_UNSUPPORTED'));
        }
        return navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false }).then(function (stream) {
            if (videoEl) {
                videoEl.srcObject = stream;
            }
            return stream;
        });
    }

    function captureSnapshotDataUrl(videoEl, maxWidth) {
        if (!videoEl || !videoEl.videoWidth || !videoEl.videoHeight) {
            return null;
        }
        var vw = videoEl.videoWidth;
        var vh = videoEl.videoHeight;
        var scale = 1;
        if (maxWidth && vw > maxWidth) {
            scale = maxWidth / vw;
        }
        var cw = Math.round(vw * scale);
        var ch = Math.round(vh * scale);
        var canvas = document.createElement('canvas');
        canvas.width = cw;
        canvas.height = ch;
        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return null;
        }
        ctx.translate(cw, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(videoEl, 0, 0, cw, ch);
        return canvas.toDataURL('image/jpeg', 0.88);
    }

    function detectFaceFromPreview(videoEl) {
        if (!window.FaceDetector) {
            return detectFaceWithMediaPipeFallback(videoEl);
        }
        if (!videoEl || !videoEl.videoWidth || !videoEl.videoHeight) {
            return Promise.resolve(false);
        }
        var canvas = document.createElement('canvas');
        canvas.width = videoEl.videoWidth;
        canvas.height = videoEl.videoHeight;
        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return Promise.resolve(false);
        }
        ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
        var detector = new window.FaceDetector({ fastMode: true, maxDetectedFaces: 1 });
        return createImageBitmap(canvas)
            .then(function (bitmap) {
                return detector.detect(bitmap).then(function (faces) {
                    return Array.isArray(faces) && faces.length > 0;
                });
            })
            .catch(function () {
                return false;
            });
    }

    var mediaPipeReadyPromise = null;
    function loadScriptOnce(src) {
        return new Promise(function (resolve, reject) {
            var exists = document.querySelector('script[data-auth-face-lib="' + src + '"]');
            if (exists) {
                if (exists.getAttribute('data-loaded') === '1') {
                    resolve();
                } else {
                    exists.addEventListener('load', function () {
                        resolve();
                    }, { once: true });
                    exists.addEventListener('error', function () {
                        reject(new Error('SCRIPT_LOAD_FAIL'));
                    }, { once: true });
                }
                return;
            }
            var script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.setAttribute('data-auth-face-lib', src);
            script.onload = function () {
                script.setAttribute('data-loaded', '1');
                resolve();
            };
            script.onerror = function () {
                reject(new Error('SCRIPT_LOAD_FAIL'));
            };
            document.head.appendChild(script);
        });
    }

    function ensureMediaPipeLoaded() {
        if (mediaPipeReadyPromise) {
            return mediaPipeReadyPromise;
        }
        mediaPipeReadyPromise = Promise.all([
            loadScriptOnce('https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/face_detection.js'),
            loadScriptOnce('https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js')
        ]);
        return mediaPipeReadyPromise;
    }

    function detectFaceWithMediaPipeFallback(videoEl) {
        return ensureMediaPipeLoaded()
            .then(function () {
                if (!window.FaceDetection || !videoEl || !videoEl.videoWidth || !videoEl.videoHeight) {
                    throw new Error('FACE_DETECT_UNSUPPORTED');
                }
                var canvas = document.createElement('canvas');
                canvas.width = videoEl.videoWidth;
                canvas.height = videoEl.videoHeight;
                var ctx = canvas.getContext('2d');
                if (!ctx) {
                    return false;
                }
                ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
                return new Promise(function (resolve) {
                    var resolved = false;
                    var detector = new window.FaceDetection({
                        locateFile: function (file) {
                            return 'https://cdn.jsdelivr.net/npm/@mediapipe/face_detection/' + file;
                        }
                    });
                    detector.setOptions({ model: 'short', minDetectionConfidence: 0.5 });
                    detector.onResults(function (results) {
                        if (resolved) {
                            return;
                        }
                        resolved = true;
                        resolve(!!(results && results.detections && results.detections.length > 0));
                        if (typeof detector.close === 'function') {
                            detector.close();
                        }
                    });
                    detector.send({ image: canvas }).catch(function () {
                        if (resolved) {
                            return;
                        }
                        resolved = true;
                        resolve(false);
                        if (typeof detector.close === 'function') {
                            detector.close();
                        }
                    });
                    setTimeout(function () {
                        if (resolved) {
                            return;
                        }
                        resolved = true;
                        resolve(false);
                        if (typeof detector.close === 'function') {
                            detector.close();
                        }
                    }, 2000);
                });
            })
            .catch(function () {
                throw new Error('FACE_DETECT_UNSUPPORTED');
            });
    }

    function setModalVisible(visible) {
        var modal = getEl('auth-face-modal');
        if (!modal) {
            return;
        }
        modal.hidden = !visible;
        modal.setAttribute('aria-hidden', visible ? 'false' : 'true');
    }

    function setScanVisible(visible) {
        var scan = getEl('auth-face-scan');
        if (!scan) {
            return;
        }
        scan.hidden = !visible;
        scan.setAttribute('aria-hidden', visible ? 'false' : 'true');
    }

    function setMsg(text) {
        var el = getEl('auth-face-msg');
        if (el) {
            el.textContent = text || '';
            el.hidden = !text;
        }
    }

    /**
     * @param {{ mode: 'login'|'enroll', getEmail: () => string, onDone?: (ok: boolean, data?: object) => void }} opts
     */
    function runFaceScan(opts) {
        var modal = getEl('auth-face-modal');
        var videoEl = getEl('auth-face-video');
        var stream = null;

        function cleanup() {
            stopCamera(stream, videoEl);
            stream = null;
            setScanVisible(false);
        }

        function close() {
            cleanup();
            setModalVisible(false);
            setMsg('');
        }

        if (!modal || !videoEl) {
            return;
        }

        setModalVisible(true);
        setScanVisible(false);
        setMsg("Demande d'accès à la caméra...");

        startCamera(videoEl)
            .then(function (s) {
                stream = s;
                setScanVisible(true);
                setMsg('Scan du visage en cours...');
                return new Promise(function (r) {
                    setTimeout(r, 1600);
                });
            })
            .then(function () {
                return detectFaceFromPreview(videoEl);
            })
            .then(function (hasFace) {
                if (!hasFace) {
                    cleanup();
                    setMsg('Aucun visage détecté. Réessayez.');
                    setModalVisible(true);
                    if (opts.onDone) {
                        opts.onDone(false);
                    }
                    return;
                }
                var snap = captureSnapshotDataUrl(videoEl, 480);
                if (!snap) {
                    cleanup();
                    setModalVisible(true);
                    setMsg('Impossible de capturer l image. Réessayez.');
                    if (opts.onDone) {
                        opts.onDone(false);
                    }
                    return;
                }
                cleanup();
                setModalVisible(false);
                var email = (opts.getEmail && opts.getEmail()) || '';
                email = email.trim();
                if (!email) {
                    if (opts.onDone) {
                        opts.onDone(false);
                    }
                    return;
                }
                var action = opts.mode === 'enroll' ? 'face_enroll' : 'face_login';
                var body = new URLSearchParams();
                body.set('action', action);
                body.set('email', email);
                body.set('snapshot', snap);
                return fetch(controllerUrl(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body
                }).then(function (r) {
                    if (!r.ok) {
                        return Promise.reject(new Error('HTTP_' + r.status));
                    }
                    return r.json().catch(function () {
                        return Promise.reject(new Error('BAD_JSON'));
                    });
                });
            })
            .then(function (json) {
                if (!json) {
                    return;
                }
                if (json.ok) {
                    if (opts.mode === 'login' && json.redirect) {
                        window.location.href = json.redirect;
                    } else if (opts.onDone) {
                        opts.onDone(true, json);
                    }
                } else {
                    if (opts.onDone) {
                        opts.onDone(false, json);
                    } else {
                        alert(json.error || 'Erreur');
                    }
                }
            })
            .catch(function (err) {
                cleanup();
                setModalVisible(false);
                var m = err && err.message;
                if (m === 'CAMERA_UNSUPPORTED') {
                    alert('Caméra non disponible sur ce navigateur.');
                } else if (m === 'FACE_DETECT_UNSUPPORTED') {
                    alert('Détection faciale indisponible. Utilisez Chrome ou Edge à jour.');
                } else if (m === 'BAD_JSON') {
                    alert('Réponse serveur invalide.');
                } else {
                    alert('Caméra refusée ou erreur. Réessayez.');
                }
                if (opts.onDone) {
                    opts.onDone(false);
                }
            });
    }

    window.HappyBiteAuthFace = {
        runLogin: function (getEmail) {
            runFaceScan({ mode: 'login', getEmail: getEmail });
        },
        runEnroll: function (getEmail, onDone) {
            runFaceScan({
                mode: 'enroll',
                getEmail: getEmail,
                onDone: onDone
            });
        },
        closeModal: function () {
            var videoEl = getEl('auth-face-video');
            if (videoEl && videoEl.srcObject) {
                stopCamera(videoEl.srcObject, videoEl);
            }
            setScanVisible(false);
            setModalVisible(false);
            setMsg('');
        }
    };

    document.addEventListener('click', function (e) {
        var modal = getEl('auth-face-modal');
        if (modal && e.target === modal) {
            window.HappyBiteAuthFace.closeModal();
        }
    });

    var closeBtn = getEl('auth-face-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            window.HappyBiteAuthFace.closeModal();
        });
    }
})();
