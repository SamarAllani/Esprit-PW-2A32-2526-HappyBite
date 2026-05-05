<style>
    .fo-ai-widget {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 1200;
        width: 190px;
        transition: width 0.2s ease, height 0.2s ease;
    }
    .fo-ai-widget__trigger {
        width: 100%;
        min-height: 52px;
        border: 2px solid #43a047;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 12px 34px rgba(19, 30, 23, 0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 12px;
        cursor: pointer;
        font-weight: 700;
        font-size: 0.95rem;
        font-family: inherit;
    }
    .fo-ai-widget__label {
        background: linear-gradient(90deg, #e53935 0%, #fb8c00 52%, #43a047 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .fo-ai-widget__icon {
        width: 20px;
        height: 20px;
        object-fit: contain;
        display: block;
    }
    .fo-ai-widget__panel {
        display: none;
        width: 100%;
        height: 100%;
        background: #f8fffb;
        border: 2px solid #43a047;
        border-radius: 18px;
        padding: 14px;
        box-sizing: border-box;
        position: relative;
        box-shadow: 0 12px 34px rgba(19, 30, 23, 0.25);
    }
    .fo-ai-widget.is-open {
        width: min(515px, calc(100vw - 24px));
        height: min(752px, calc(100vh - 24px));
        max-width: 515px;
        max-height: 752px;
    }
    .fo-ai-widget.is-open .fo-ai-widget__trigger {
        display: none;
    }
    .fo-ai-widget.is-open .fo-ai-widget__panel {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .fo-ai-widget__title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
    }
    .fo-ai-widget__title-text {
        font-size: 1rem;
        font-weight: 700;
        background: linear-gradient(90deg, #e53935 0%, #fb8c00 52%, #43a047 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .fo-ai-widget__close-wrap {
        display: flex;
        justify-content: flex-end;
    }
    .fo-ai-widget__close {
        border: 1px solid #9ccc9f;
        background: #fff;
        color: #1d4a2f;
        border-radius: 8px;
        padding: 5px 9px;
        font-weight: 600;
        cursor: pointer;
    }
    .fo-ai-widget__welcome-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .fo-ai-widget__messages {
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex: 1 1 auto;
        overflow-y: auto;
        min-height: 0;
        padding-right: 2px;
    }
    .fo-ai-widget__welcome-icon {
        width: 24px;
        height: 24px;
        object-fit: contain;
        flex: 0 0 auto;
        margin-top: 4px;
    }
    .fo-ai-widget__bubble {
        flex: 1 1 auto;
        border: 2px solid #43a047;
        border-radius: 14px;
        background: #fff;
        color: #173023;
        padding: 12px 14px;
        font-weight: 500;
        line-height: 1.4;
    }
    .fo-ai-widget__spacer {
        flex: 1 1 auto;
    }
    .fo-ai-widget__askbar {
        border: 2px solid #43a047;
        border-radius: 12px;
        background: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
    }
    .fo-ai-widget__suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .fo-ai-widget__suggestion {
        border: 1px solid #9ccc9f;
        background: #fff;
        color: #1d4a2f;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 0.8rem;
        line-height: 1.2;
        cursor: pointer;
    }
    .fo-ai-widget__suggestion:hover {
        background: #e8f5e9;
    }
    .fo-ai-widget__send {
        width: 18px;
        height: 18px;
        object-fit: contain;
        flex: 0 0 auto;
    }
    .fo-ai-widget__input {
        width: 100%;
        border: 0;
        outline: 0;
        background: transparent;
        color: #173023;
        font-family: inherit;
        font-size: 0.95rem;
    }
    .fo-ai-widget__askbtn {
        border: 0;
        background: transparent;
        padding: 0;
        margin: 0;
        line-height: 0;
        cursor: pointer;
    }
    .fo-ai-widget__msg-user {
        align-self: flex-end;
        max-width: 86%;
        border-radius: 14px;
        background: #2e7d32;
        color: #fff;
        padding: 10px 12px;
        line-height: 1.35;
        font-weight: 500;
    }
    .fo-ai-widget__msg-ai {
        align-self: flex-start;
        max-width: 100%;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    .fo-ai-widget__msg-ai-icon {
        width: 22px;
        height: 22px;
        object-fit: contain;
        flex: 0 0 auto;
        margin-top: 4px;
    }
    .fo-ai-widget__msg-ai-bubble {
        max-width: 86%;
        border: 2px solid #43a047;
        border-radius: 14px;
        background: #fff;
        color: #173023;
        padding: 10px 12px;
        line-height: 1.35;
        font-weight: 500;
    }
</style>

<?php $aiCurrentPage = strtolower(basename((string) ($_SERVER['PHP_SELF'] ?? ''))); ?>
<div id="fo-ai-widget" class="fo-ai-widget" aria-expanded="false" data-page="<?php echo htmlspecialchars($aiCurrentPage, ENT_QUOTES, 'UTF-8'); ?>">
    <button type="button" class="fo-ai-widget__trigger" aria-label="Ouvrir l'assistant">
        <img src="images/ai-technology.png" alt="" class="fo-ai-widget__icon">
        <span class="fo-ai-widget__label">Demande-moi</span>
    </button>
    <div class="fo-ai-widget__panel">
        <div class="fo-ai-widget__title">
            <span class="fo-ai-widget__title-text">Assistant Ai</span>
            <button type="button" class="fo-ai-widget__close" aria-label="Fermer l'assistant">Fermer</button>
        </div>
        <div class="fo-ai-widget__messages" id="fo-ai-messages">
            <div class="fo-ai-widget__welcome-row">
                <img src="images/ai-technology.png" alt="" class="fo-ai-widget__welcome-icon">
                <div class="fo-ai-widget__bubble">Bonjour ! Comment puis-je vous aider aujourd'hui ?</div>
            </div>
        </div>
        <div class="fo-ai-widget__suggestions" id="fo-ai-suggestions"></div>
        <div class="fo-ai-widget__askbar">
            <input type="text" class="fo-ai-widget__input" placeholder="Posez votre question...">
            <button type="button" class="fo-ai-widget__askbtn" aria-label="Envoyer">
                <img src="images/sent.png" alt="" class="fo-ai-widget__send">
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        var widget = document.getElementById('fo-ai-widget');
        if (!widget) return;
        var trigger = widget.querySelector('.fo-ai-widget__trigger');
        var close = widget.querySelector('.fo-ai-widget__close');
        var messages = widget.querySelector('#fo-ai-messages');
        var input = widget.querySelector('.fo-ai-widget__input');
        var sendBtn = widget.querySelector('.fo-ai-widget__askbtn');
        var suggestionsWrap = widget.querySelector('#fo-ai-suggestions');
        var currentPage = (widget.getAttribute('data-page') || '').toLowerCase();

        function setOpen(opened) {
            widget.classList.toggle('is-open', opened);
            widget.setAttribute('aria-expanded', opened ? 'true' : 'false');
        }

        function normalizeQuestion(text) {
            return text
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/[?!.,]/g, '')
                .replace(/\s+/g, ' ')
                .trim();
        }

        function getLocalFallbackReply(questionRaw) {
            var question = normalizeQuestion(questionRaw);
            if (
                question === 'ou est ma commande' ||
                question === 'quand ma commande arrivera-t-elle' ||
                question === 'quand ma commande arrivera t elle' ||
                question === 'where is my order' ||
                question === 'where is my commande' ||
                question === 'when will my order arrive'
            ) {
                if (currentPage !== 'commande.php') {
                    return { answer: 'Voici où se trouve votre commande :', link: { url: 'track.php', label: 'Suivre ma commande' } };
                }
                return { answer: 'Finalisez d\'abord votre commande, puis utilisez ce lien :', link: { url: 'track.php', label: 'Suivre ma commande' } };
            }
            return { answer: 'Je suis la pour vous aider.' };
        }

        function appendUserMessage(text) {
            if (!messages) return;
            var msg = document.createElement('div');
            msg.className = 'fo-ai-widget__msg-user';
            msg.textContent = text;
            messages.appendChild(msg);
            messages.scrollTop = messages.scrollHeight;
        }

        function appendAiMessage(payload) {
            if (!messages) return;
            var row = document.createElement('div');
            row.className = 'fo-ai-widget__msg-ai';

            var icon = document.createElement('img');
            icon.className = 'fo-ai-widget__msg-ai-icon';
            icon.src = 'images/ai-technology.png';
            icon.alt = '';

            var bubble = document.createElement('div');
            bubble.className = 'fo-ai-widget__msg-ai-bubble';
            var answerText = (payload && typeof payload.answer === 'string') ? payload.answer : '';
            bubble.textContent = answerText;
            if (payload && payload.link && payload.link.url && payload.link.label) {
                var link = document.createElement('a');
                link.href = String(payload.link.url);
                link.textContent = String(payload.link.label);
                link.style.marginLeft = '6px';
                link.style.fontWeight = '700';
                bubble.appendChild(document.createTextNode(' '));
                bubble.appendChild(link);
            }

            row.appendChild(icon);
            row.appendChild(bubble);
            messages.appendChild(row);
            messages.scrollTop = messages.scrollHeight;
        }

        function getSuggestionQuestions() {
            var suggestions = [
                'PayPal est-il securise ?',
                'Est-il possible d\'annuler ma commande ?',
                'Quel mode de paiement dois-je choisir ?',
                'Ou est ma commande ?'
            ];
            if (currentPage === 'livraison.php' || currentPage === 'track.php') {
                suggestions.push('Quand ma commande arrivera-t-elle ?');
            }
            return suggestions;
        }

        function renderSuggestions() {
            if (!suggestionsWrap) return;
            suggestionsWrap.innerHTML = '';
            getSuggestionQuestions().forEach(function (question) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'fo-ai-widget__suggestion';
                btn.textContent = question;
                btn.addEventListener('click', function () {
                    if (input) {
                        input.value = question;
                    }
                    sendCurrentMessage();
                });
                suggestionsWrap.appendChild(btn);
            });
        }

        function requestAiReply(questionRaw) {
            return fetch('ai_chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    question: questionRaw,
                    page: currentPage
                })
            }).then(function (response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            });
        }

        function sendCurrentMessage() {
            if (!input) return;
            var value = input.value.trim();
            if (value === '') return;
            appendUserMessage(value);
            if (sendBtn) sendBtn.disabled = true;
            input.value = '';
            requestAiReply(value)
                .then(function (data) {
                    if (data && data.answer) {
                        appendAiMessage(data);
                    } else {
                        appendAiMessage(getLocalFallbackReply(value));
                    }
                })
                .catch(function () {
                    appendAiMessage(getLocalFallbackReply(value));
                })
                .finally(function () {
                    if (sendBtn) sendBtn.disabled = false;
                    input.focus();
                });
        }

        renderSuggestions();

        if (trigger) {
            trigger.addEventListener('click', function () {
                setOpen(true);
            });
        }

        if (close) {
            close.addEventListener('click', function () {
                setOpen(false);
            });
        }
        if (sendBtn) {
            sendBtn.addEventListener('click', sendCurrentMessage);
        }
        if (input) {
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    sendCurrentMessage();
                }
            });
        }
    })();
</script>
