<style>
.floating-window {
    position: fixed;
    width: 380px;
    max-width: calc(100vw - 40px);
    background: linear-gradient(180deg, rgba(10, 22, 40, 0.92) 0%, rgba(26, 38, 64, 0.95) 100%);
    border-radius: 16px;
    box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.1), 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    z-index: 99999;
    display: none;
    flex-direction: column;
    overflow: hidden;
    font-family: 'Figtree', sans-serif;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.floating-window.visible {
    display: flex;
}

.floating-window.dragging {
    cursor: grabbing;
    user-select: none;
}

.floating-header {
    background: linear-gradient(90deg, #0A1628 0%, #1A2640 100%);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    cursor: grab;
}

.floating-header:active {
    cursor: grabbing;
}

.floating-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.floating-header-left img {
    width: 24px;
    height: 24px;
}

.floating-header-left span {
    color: white;
    font-weight: 600;
    font-size: 14px;
}

.floating-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.floating-timer {
    font-family: 'SF Mono', Monaco, monospace;
    font-size: 13px;
    color: #ffffff;
}

.floating-close-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 6px;
    padding: 6px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.floating-close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.floating-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 350px;
}

.floating-transcript {
    background: rgba(10, 22, 40, 0.7);
    border-radius: 12px;
    padding: 12px;
    max-height: 120px;
    overflow-y: auto;
    font-size: 13px;
    line-height: 1.5;
}

.floating-transcript-line {
    padding: 3px 0;
    color: #e5e7eb;
}

.floating-transcript-line.agent {
    color: #e5e7eb;
}

.floating-transcript-line.caller {
    color: #ffffff;
}

.floating-transcript-empty {
    color: #6b7280;
    text-align: center;
    font-size: 12px;
}

.floating-suggestion {
    background: linear-gradient(135deg, rgba(10, 22, 40, 0.9) 0%, rgba(26, 38, 64, 0.9) 100%);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 14px;
}

.floating-suggestion-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #ffffff;
    margin-bottom: 8px;
}

.floating-suggestion-text {
    color: white;
    font-size: 13px;
    line-height: 1.6;
}

.floating-suggestion-loading {
    color: #d1d5db;
    font-size: 12px;
    font-style: italic;
}

.floating-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.floating-action-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(10, 22, 40, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 8px 14px;
    color: #ffffff;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.floating-action-btn:hover {
    background: rgba(26, 38, 64, 0.8);
    border-color: rgba(255, 255, 255, 0.4);
}

.floating-refresh-btn {
    opacity: 0.6;
    transition: opacity 0.2s;
}

.floating-refresh-btn:hover {
    opacity: 1;
}

.floating-refresh-btn.spinning svg {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.floating-input-area {
    padding: 12px 16px 16px;
    background: rgba(10, 22, 40, 0.8);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.floating-input-wrapper {
    display: flex;
    gap: 8px;
    align-items: center;
}

.floating-input {
    flex: 1;
    background: rgba(30, 41, 59, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 10px 14px;
    color: white;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
}

.floating-input:focus {
    border-color: rgba(255, 255, 255, 0.4);
}

.floating-input::placeholder {
    color: #9ca3af;
}

.floating-send-btn {
    background: linear-gradient(180deg, #1A2640 0%, #0A1628 100%);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 10px 16px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.floating-send-btn:hover {
    background: linear-gradient(180deg, #2a3a5c 0%, #1A2640 100%);
    border-color: rgba(255, 255, 255, 0.4);
    transform: scale(1.02);
}

.floating-send-btn:active {
    transform: scale(0.98);
}

.floating-ztp-alert {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2) 0%, rgba(220, 38, 38, 0.15) 100%);
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: 12px;
    padding: 14px;
    animation: pulse-alert 2s infinite;
}

.floating-ztp-alert-header {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #fca5a5;
    margin-bottom: 6px;
}

.floating-ztp-alert-text {
    color: #fecaca;
    font-size: 12px;
}

@keyframes pulse-alert {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
}

.floating-loading-dots {
    display: flex;
    gap: 4px;
    padding: 8px 0;
}

.floating-loading-dots span {
    width: 6px;
    height: 6px;
    background: #60a5fa;
    border-radius: 50%;
    animation: loading-bounce 1.4s infinite ease-in-out both;
}

    .floating-loading-dots span:nth-child(1) { animation-delay: -0.32s; }
    .floating-loading-dots span:nth-child(2) { animation-delay: -0.16s; }

@keyframes loading-bounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.floating-typing-indicator {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 0;
}

.floating-typing-indicator span {
    width: 8px;
    height: 8px;
    background: #60a5fa;
    border-radius: 50%;
    animation: typing-bounce 1.4s infinite ease-in-out both;
}

.floating-typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
.floating-typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
.floating-typing-indicator span:nth-child(3) { animation-delay: 0s; }

@keyframes typing-bounce {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40% { transform: scale(1); opacity: 1; }
}

.floating-word-display {
    display: inline;
}

.floating-word {
    opacity: 0;
    animation: word-appear 0.15s ease-out forwards;
}

@keyframes word-appear {
    from { opacity: 0; transform: translateY(2px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div id="floatingWindow" class="floating-window">
    <div class="floating-header" id="floatingHeader">
        <div class="floating-header-left">
            <img src="https://res.cloudinary.com/dbviya1rj/image/upload/q_auto/f_auto/v1775144933/gkigls8alfr7bm4h1rhh.png" alt="BOB">
            <span>BOB AI Assistant</span>
        </div>
        <div class="floating-header-right">
            <span class="floating-timer" id="floatingTimer">00:00:00</span>
            <button class="floating-close-btn" id="floatingPipBtn" onclick="togglePipMode()" title="Pop out" style="display:none;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M15 3h6v6M9 21H3v-6M21 3l-9 9M3 21l9-9"/>
                </svg>
            </button>
            <button class="floating-close-btn" onclick="toggleFloatingWindow()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="floating-body">
        <div class="floating-transcript" id="floatingTranscript">
            <p class="floating-transcript-empty">Waiting for transcript...</p>
        </div>

        <div class="floating-suggestion" id="floatingSuggestion">
            <div class="floating-suggestion-header">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>What to Say</span>
                <button class="floating-refresh-btn" onclick="handleRefreshSuggestions()" title="Refresh suggestions" style="background:none;border:none;cursor:pointer;padding:2px;margin-left:4px;color:#ffffff;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                    </svg>
                </button>
            </div>
            <p class="floating-suggestion-text" id="floatingSuggestionText">Click refresh to get AI suggestions...</p>
        </div>

        <div class="floating-actions">
            <button class="floating-action-btn" onclick="handleRefreshSuggestions()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
                </svg>
                Refresh
            </button>
            <button class="floating-action-btn" onclick="handleWhatToSay()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Use Suggestion
            </button>
        </div>

        <div class="floating-ztp-alert" id="floatingZtpAlert" style="display: none;">
            <div class="floating-ztp-alert-header">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                ZTP Alert
            </div>
            <p class="floating-ztp-alert-text" id="floatingZtpAlertText"></p>
        </div>
    </div>

    <div class="floating-input-area">
        <div class="floating-input-wrapper">
            <input 
                type="text" 
                id="floatingChatInput" 
                class="floating-input" 
                placeholder="Ask a question..."
                onkeypress="if(event.key==='Enter') sendChatMessage()"
            >
            <button class="floating-send-btn" onclick="sendChatMessage()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2.5 1.5L10.5 12L2.5 22.5V1.5Z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
(function() {
    const sessionId = '{{ $session->session_id }}';
    const startTime = new Date('{{ $session->started_at?->toIso8601String() ?? now()->toIso8601String() }}');

    let eventSource = null;
    let isDragging = false;
    let dragOffset = { x: 0, y: 0 };
    let windowPosition = { x: 0, y: 0 };
    let _doc = document;
    let isPipMode = false;

    const popOutIconSvg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-9 9M3 21l9-9"/></svg>';
    const returnIconSvg = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 9L3 3m0 0h6m-6 0v6M15 15l6 6m0 0h-6m6 0v-6"/></svg>';

    function initPosition() {
        const saved = localStorage.getItem('floatingWindowPosition');
        const win = document.getElementById('floatingWindow');
        if (saved) {
            const pos = JSON.parse(saved);
            win.style.right = 'auto';
            win.style.bottom = 'auto';
            win.style.left = pos.x + 'px';
            win.style.top = pos.y + 'px';
            windowPosition = pos;
        } else {
            const x = window.innerWidth - 400;
            const y = window.innerHeight - 500;
            win.style.left = x + 'px';
            win.style.top = y + 'px';
            windowPosition = { x, y };
        }
    }

    function savePosition() {
        const rect = document.getElementById('floatingWindow').getBoundingClientRect();
        windowPosition = { x: rect.left, y: rect.top };
        localStorage.setItem('floatingWindowPosition', JSON.stringify(windowPosition));
    }

    function startDrag(e) {
        if (isPipMode) return;
        isDragging = true;
        const rect = document.getElementById('floatingWindow').getBoundingClientRect();
        dragOffset = { x: e.clientX - rect.left, y: e.clientY - rect.top };
        document.getElementById('floatingWindow').classList.add('dragging');
        e.preventDefault();
    }

    function onDrag(e) {
        if (!isDragging || isPipMode) return;
        const win = document.getElementById('floatingWindow');
        let newX = Math.max(0, Math.min(e.clientX - dragOffset.x, window.innerWidth - win.offsetWidth));
        let newY = Math.max(0, Math.min(e.clientY - dragOffset.y, window.innerHeight - win.offsetHeight));
        win.style.left = newX + 'px';
        win.style.top = newY + 'px';
        win.style.right = 'auto';
        win.style.bottom = 'auto';
    }

    function stopDrag() {
        if (isDragging) {
            isDragging = false;
            document.getElementById('floatingWindow').classList.remove('dragging');
            savePosition();
        }
    }

    document.getElementById('floatingHeader').addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);

    function updateTimer() {
        const elapsed = Math.floor((new Date() - startTime) / 1000);
        const hours = Math.floor(elapsed / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        _doc.getElementById('floatingTimer').textContent = `${hours}:${minutes}:${seconds}`;
    }

    function updateTranscript(transcripts) {
        const el = _doc.getElementById('floatingTranscript');
        if (!transcripts || transcripts.length === 0) {
            el.innerHTML = '<p class="floating-transcript-empty">Waiting for transcript...</p>';
            return;
        }
        let html = '';
        transcripts.forEach(t => {
            const speakerClass = t.speaker === 'agent' ? 'agent' : 'caller';
            const speakerName = t.speaker === 'agent' ? 'Agent' : 'Caller';
            html += `<div class="floating-transcript-line ${speakerClass}"><strong>${speakerName}:</strong> ${escapeHtml(t.text)}</div>`;
        });
        el.innerHTML = html;
        el.scrollTop = el.scrollHeight;
    }

    function updateSuggestions(suggestions) {
        const el = _doc.getElementById('floatingSuggestionText');
        if (suggestions && suggestions.what_to_say) {
            el.innerHTML = suggestions.what_to_say.startsWith('<span')
                ? suggestions.what_to_say
                : escapeHtml(suggestions.what_to_say);
            el.classList.remove('floating-suggestion-loading');
        } else {
            el.textContent = 'No suggestions available';
            el.classList.add('floating-suggestion-loading');
        }
    }

    function updateZtpAlert(ztpAlerts) {
        const alertEl = _doc.getElementById('floatingZtpAlert');
        const alertTextEl = _doc.getElementById('floatingZtpAlertText');
        if (ztpAlerts && ztpAlerts.length > 0) {
            alertTextEl.textContent = ztpAlerts[ztpAlerts.length - 1].message || 'Zero Tolerance Policy violation detected!';
            alertEl.style.display = 'block';
        } else {
            alertEl.style.display = 'none';
        }
    }

    function escapeHtml(text) {
        const div = _doc.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function initEventSource() {
        if (eventSource) eventSource.close();

        eventSource = new EventSource(`/live-monitoring/session/${sessionId}/stream`);
        let lastTranscriptLength = 0;

        eventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                updateTranscript(data.transcripts);
                updateSuggestions(data.suggestions);
                updateZtpAlert(data.ztp_alerts);

                const currentLength = (data.transcript || '').length;
                if (currentLength > lastTranscriptLength && lastTranscriptLength > 0) {
                    window.handleRefreshSuggestions();
                }
                lastTranscriptLength = currentLength;
            } catch (e) {
                console.error('Error parsing SSE data:', e);
            }
        };

        eventSource.addEventListener('ended', () => {
            window.location.href = '/live-monitoring';
        });

        eventSource.onerror = () => { setTimeout(initEventSource, 5000); };
    }

    async function enterPipMode() {
        const pipWin = await window.documentPictureInPicture.requestWindow({
            width: 400,
            height: 580,
            disallowReturnToOpener: false,
        });

        [...document.styleSheets].forEach(sheet => {
            try {
                const style = pipWin.document.createElement('style');
                style.textContent = [...sheet.cssRules].map(r => r.cssText).join('\n');
                pipWin.document.head.appendChild(style);
            } catch (e) {}
        });

        // Dark background on html + body so no white shows at edges or bottom
        pipWin.document.documentElement.style.cssText = 'margin:0;padding:0;overflow:hidden;height:100%;background:linear-gradient(180deg,rgba(10,22,40,0.92)0%,rgba(26,38,64,0.95)100%)';
        pipWin.document.body.style.cssText = 'margin:0;padding:0;overflow:hidden;height:100%;background:transparent';

        const clone = document.getElementById('floatingWindow').cloneNode(true);
        clone.style.position = 'static';
        clone.style.width = '100%';
        clone.style.height = '100%';
        clone.style.inset = 'auto';
        clone.style.borderRadius = '0';
        clone.style.boxShadow = 'none';
        clone.style.border = 'none';
        clone.classList.add('visible');

        // Let .floating-body fill all available vertical space instead of being capped at 350px
        const bodyEl = clone.querySelector('.floating-body');
        if (bodyEl) {
            bodyEl.style.maxHeight = 'none';
            bodyEl.style.flex = '1';
        }

        pipWin.document.body.appendChild(clone);

        // Snap back to original size on any resize attempt
        pipWin.addEventListener('resize', () => pipWin.resizeTo(400, 580));

        _doc = pipWin.document;

        _doc.getElementById('floatingHeader').style.cursor = 'default';

        _doc.getElementById('floatingPipBtn').onclick = exitPipMode;
        _doc.getElementById('floatingPipBtn').innerHTML = returnIconSvg;
        _doc.getElementById('floatingPipBtn').title = 'Pop back in';
        _doc.getElementById('floatingPipBtn').style.display = 'flex';

        _doc.querySelectorAll('.floating-close-btn').forEach(btn => {
            if (btn.id !== 'floatingPipBtn') btn.onclick = () => window.toggleFloatingWindow();
        });

        const actionBtns = _doc.querySelectorAll('.floating-action-btn');
        if (actionBtns[0]) actionBtns[0].onclick = () => window.handleRefreshSuggestions();
        if (actionBtns[1]) actionBtns[1].onclick = () => window.handleWhatToSay();

        _doc.querySelector('.floating-send-btn').onclick = () => window.sendChatMessage();
        _doc.getElementById('floatingChatInput').onkeypress = (e) => { if (e.key === 'Enter') window.sendChatMessage(); };

        const refreshBtn = _doc.querySelector('.floating-refresh-btn');
        if (refreshBtn) refreshBtn.onclick = () => window.handleRefreshSuggestions();

        document.getElementById('floatingWindow').classList.remove('visible');
        document.getElementById('floatingPipBtn').innerHTML = returnIconSvg;
        document.getElementById('floatingPipBtn').title = 'Pop back in';

        pipWin.addEventListener('pagehide', exitPipMode);

        isPipMode = true;
    }

    function exitPipMode() {
        if (!isPipMode) return;
        isPipMode = false;
        _doc = document;
        document.getElementById('floatingWindow').classList.add('visible');
        document.getElementById('floatingPipBtn').innerHTML = popOutIconSvg;
        document.getElementById('floatingPipBtn').title = 'Pop out';
        try { window.documentPictureInPicture.window?.close(); } catch (e) {}
    }

    window.toggleFloatingWindow = function() {
        const win = document.getElementById('floatingWindow');
        const isVisible = win.classList.contains('visible');
        if (isVisible) {
            win.classList.remove('visible');
            sessionStorage.setItem('floatingWindowVisible', 'false');
        } else {
            win.classList.add('visible');
            sessionStorage.setItem('floatingWindowVisible', 'true');
        }
    };

    window.togglePipMode = function() {
        if (isPipMode) exitPipMode();
        else enterPipMode().catch(console.error);
    };

    window.handleAssist = function() {
        _doc.getElementById('floatingChatInput').value = 'Can you help me with this call?';
        window.sendChatMessage();
    };

    window.handleWhatToSay = function() {
        const suggestionEl = _doc.getElementById('floatingSuggestionText');
        const suggestion = suggestionEl.textContent;
        if (suggestion && !suggestionEl.classList.contains('floating-suggestion-loading')) {
            _doc.getElementById('floatingChatInput').value = suggestion;
            window.sendChatMessage();
        }
    };

    window.handleRefreshSuggestions = function() {
        const t0 = performance.now();
        let fullText = '';

        _doc.getElementById('floatingSuggestionText').innerHTML = '<div class="floating-typing-indicator"><span></span><span></span><span></span></div>';

        const refreshBtn = _doc.querySelector('.floating-refresh-btn');
        if (refreshBtn) refreshBtn.classList.add('spinning');

        fetch('/api/live-monitoring/suggestion-stream', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ session_id: sessionId, type: 'what_to_say' })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            const reader = response.body.getReader();
            const decoder = new TextDecoder();

            function processStream() {
                reader.read().then(({ done, value }) => {
                    if (done) {
                        const rb = _doc.querySelector('.floating-refresh-btn');
                        if (rb) rb.classList.remove('spinning');
                        console.log(`Suggestion latency: ${(performance.now() - t0).toFixed(0)}ms`);
                        return;
                    }
                    const lines = decoder.decode(value, { stream: true }).split('\n');
                    for (const line of lines) {
                        if (!line.startsWith('data: ')) continue;
                        const data = line.slice(6);
                        if (data === '[DONE]') continue;
                        try {
                            const parsed = JSON.parse(data);
                            if (parsed.token) {
                                fullText += parsed.token;
                                _doc.getElementById('floatingSuggestionText').textContent = fullText;
                            }
                        } catch (e) {}
                    }
                    processStream();
                });
            }
            processStream();
        })
        .catch(error => {
            console.error('Suggestion error:', error);
            const rb = _doc.querySelector('.floating-refresh-btn');
            if (rb) rb.classList.remove('spinning');
            _doc.getElementById('floatingSuggestionText').textContent = 'Failed to load suggestions. Click refresh to try again.';
        });
    };

    window.sendChatMessage = function() {
        const chatInput = _doc.getElementById('floatingChatInput');
        const message = chatInput.value.trim();
        if (!message) return;

        chatInput.value = '';
        _doc.getElementById('floatingSuggestionText').innerHTML = '<div class="floating-typing-indicator"><span></span><span></span><span></span></div>';

        const t0 = performance.now();
        let fullText = '';

        function animateWord(token) {
            fullText += token;
            const words = fullText.split(/(\s+)/);
            let html;
            if (words.length > 20) {
                const recent = words.slice(-20);
                html = escapeHtml(recent.slice(0, -1).join('')) +
                    '<span class="floating-word">' + escapeHtml(recent[recent.length - 1]) + '</span>';
            } else {
                html = escapeHtml(fullText);
            }
            _doc.getElementById('floatingSuggestionText').innerHTML = html;
        }

        function processStream(reader, decoder) {
            reader.read().then(({ done, value }) => {
                if (done) return;
                const lines = decoder.decode(value, { stream: true }).split('\n');
                for (const line of lines) {
                    if (!line.startsWith('data: ')) continue;
                    const data = line.slice(6);
                    if (data === '[DONE]') {
                        console.log(`Chat response latency: ${(performance.now() - t0).toFixed(0)}ms`);
                        return;
                    }
                    try {
                        const parsed = JSON.parse(data);
                        if (parsed.token) animateWord(parsed.token);
                    } catch (e) {}
                }
                processStream(reader, decoder);
            });
        }

        fetch('/api/live-monitoring/chat-stream', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ session_id: sessionId, question: message })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            console.log(`First response latency: ${(performance.now() - t0).toFixed(0)}ms`);
            processStream(response.body.getReader(), new TextDecoder());
        })
        .catch(error => {
            console.error('Chat error:', error);
            _doc.getElementById('floatingSuggestionText').innerHTML = '<div class="floating-typing-indicator"><span></span><span></span><span></span></div>';
            fetch('/api/live-monitoring/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ session_id: sessionId, question: message })
            })
            .then(r => r.json())
            .then(data => {
                _doc.getElementById('floatingSuggestionText').textContent =
                    (data.success && data.answer) ? data.answer : 'Sorry, I could not process that.';
            })
            .catch(() => {
                _doc.getElementById('floatingSuggestionText').textContent = 'Sorry, I could not process that.';
            });
        });
    };

    function init() {
        initPosition();

        const wasVisible = sessionStorage.getItem('floatingWindowVisible') !== 'false';
        if (wasVisible) {
            document.getElementById('floatingWindow').classList.add('visible');
        }

        if ('documentPictureInPicture' in window) {
            document.getElementById('floatingPipBtn').style.display = 'flex';
        }

        setInterval(updateTimer, 1000);
        updateTimer();
        initEventSource();
    }

    init();
})();
</script>
