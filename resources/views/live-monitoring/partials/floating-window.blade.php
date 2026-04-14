<style>
.floating-window {
    position: fixed;
    width: 380px;
    max-width: calc(100vw - 40px);
    background: linear-gradient(180deg, rgba(10, 22, 40, 0.92) 0%, rgba(26, 38, 64, 0.95) 100%);
    border-radius: 16px;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.3), 0 25px 50px -12px rgba(0, 0, 0, 0.5);
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
    color: #60a5fa;
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
    background: rgba(30, 41, 59, 0.6);
    border-radius: 12px;
    padding: 12px;
    max-height: 120px;
    overflow-y: auto;
    font-size: 13px;
    line-height: 1.5;
}

.floating-transcript-line {
    padding: 3px 0;
}

.floating-transcript-line.agent {
    color: #60a5fa;
}

.floating-transcript-line.caller {
    color: #34d399;
}

.floating-transcript-empty {
    color: #6b7280;
    text-align: center;
    font-size: 12px;
}

.floating-suggestion {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(37, 99, 235, 0.1) 100%);
    border: 1px solid rgba(59, 130, 246, 0.3);
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
    color: #93c5fd;
    margin-bottom: 8px;
}

.floating-suggestion-text {
    color: white;
    font-size: 13px;
    line-height: 1.6;
}

.floating-suggestion-loading {
    color: #9ca3af;
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
    background: rgba(59, 130, 246, 0.15);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 20px;
    padding: 8px 14px;
    color: #93c5fd;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.floating-action-btn:hover {
    background: rgba(59, 130, 246, 0.25);
    color: #bfdbfe;
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
    border-color: #3b82f6;
}

.floating-input::placeholder {
    color: #9ca3af;
}

.floating-send-btn {
    background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
    border-radius: 12px;
    padding: 10px 16px;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5), 0 -1px 0 0 rgba(96, 165, 250, 0.3) inset, 0 1px 0 0 rgba(255, 255, 255, 0.2) inset;
}

.floating-send-btn:hover {
    background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
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
</style>

<div id="floatingWindow" class="floating-window">
    <div class="floating-header" id="floatingHeader">
        <div class="floating-header-left">
            <img src="https://res.cloudinary.com/dbviya1rj/image/upload/q_auto/f_auto/v1775144933/gkigls8alfr7bm4h1rhh.png" alt="BOB">
            <span>BOB AI Assistant</span>
        </div>
        <div class="floating-header-right">
            <span class="floating-timer" id="floatingTimer">00:00:00</span>
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
                What to Say
            </div>
            <p class="floating-suggestion-loading" id="floatingSuggestionText">Loading suggestions...</p>
        </div>

        <div class="floating-actions">
            <button class="floating-action-btn" onclick="handleAssist()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                </svg>
                Assist
            </button>
            <button class="floating-action-btn" onclick="handleWhatToSay()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                What to Say?
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
    
    const floatingWindow = document.getElementById('floatingWindow');
    const floatingHeader = document.getElementById('floatingHeader');
    const floatingTimer = document.getElementById('floatingTimer');
    const floatingTranscript = document.getElementById('floatingTranscript');
    const floatingSuggestionText = document.getElementById('floatingSuggestionText');
    const floatingZtpAlert = document.getElementById('floatingZtpAlert');
    const floatingZtpAlertText = document.getElementById('floatingZtpAlertText');
    const floatingChatInput = document.getElementById('floatingChatInput');

    function initPosition() {
        const saved = localStorage.getItem('floatingWindowPosition');
        if (saved) {
            const pos = JSON.parse(saved);
            floatingWindow.style.right = 'auto';
            floatingWindow.style.bottom = 'auto';
            floatingWindow.style.left = pos.x + 'px';
            floatingWindow.style.top = pos.y + 'px';
            windowPosition = pos;
        } else {
            const x = window.innerWidth - 400;
            const y = window.innerHeight - 500;
            floatingWindow.style.left = x + 'px';
            floatingWindow.style.top = y + 'px';
            windowPosition = { x, y };
        }
    }

    function savePosition() {
        const rect = floatingWindow.getBoundingClientRect();
        windowPosition = { x: rect.left, y: rect.top };
        localStorage.setItem('floatingWindowPosition', JSON.stringify(windowPosition));
    }

    function startDrag(e) {
        isDragging = true;
        const rect = floatingWindow.getBoundingClientRect();
        dragOffset = {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
        floatingWindow.classList.add('dragging');
        e.preventDefault();
    }

    function onDrag(e) {
        if (!isDragging) return;
        
        let newX = e.clientX - dragOffset.x;
        let newY = e.clientY - dragOffset.y;
        
        newX = Math.max(0, Math.min(newX, window.innerWidth - floatingWindow.offsetWidth));
        newY = Math.max(0, Math.min(newY, window.innerHeight - floatingWindow.offsetHeight));
        
        floatingWindow.style.left = newX + 'px';
        floatingWindow.style.top = newY + 'px';
        floatingWindow.style.right = 'auto';
        floatingWindow.style.bottom = 'auto';
    }

    function stopDrag() {
        if (isDragging) {
            isDragging = false;
            floatingWindow.classList.remove('dragging');
            savePosition();
        }
    }

    floatingHeader.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);

    function updateTimer() {
        const elapsed = Math.floor((new Date() - startTime) / 1000);
        const hours = Math.floor(elapsed / 3600).toString().padStart(2, '0');
        const minutes = Math.floor((elapsed % 3600) / 60).toString().padStart(2, '0');
        const seconds = (elapsed % 60).toString().padStart(2, '0');
        floatingTimer.textContent = `${hours}:${minutes}:${seconds}`;
    }

    function updateTranscript(transcripts) {
        if (!transcripts || transcripts.length === 0) {
            floatingTranscript.innerHTML = '<p class="floating-transcript-empty">Waiting for transcript...</p>';
            return;
        }
        
        let html = '';
        transcripts.forEach(t => {
            const speakerClass = t.speaker === 'agent' ? 'agent' : 'caller';
            const speakerName = t.speaker === 'agent' ? 'Agent' : 'Caller';
            html += `<div class="floating-transcript-line ${speakerClass}"><strong>${speakerName}:</strong> ${escapeHtml(t.text)}</div>`;
        });
        floatingTranscript.innerHTML = html;
        floatingTranscript.scrollTop = floatingTranscript.scrollHeight;
    }

    function updateSuggestions(suggestions) {
        if (suggestions && suggestions.what_to_say) {
            floatingSuggestionText.textContent = suggestions.what_to_say;
            floatingSuggestionText.classList.remove('floating-suggestion-loading');
        } else {
            floatingSuggestionText.textContent = 'No suggestions available';
            floatingSuggestionText.classList.add('floating-suggestion-loading');
        }
    }

    function updateZtpAlert(ztpAlerts) {
        if (ztpAlerts && ztpAlerts.length > 0) {
            const latest = ztpAlerts[ztpAlerts.length - 1];
            floatingZtpAlertText.textContent = latest.message || 'Zero Tolerance Policy violation detected!';
            floatingZtpAlert.style.display = 'block';
        } else {
            floatingZtpAlert.style.display = 'none';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function initEventSource() {
        if (eventSource) {
            eventSource.close();
        }
        
        eventSource = new EventSource(`/live-monitoring/session/${sessionId}/stream`);
        
        eventSource.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                updateTranscript(data.transcripts);
                updateSuggestions(data.suggestions);
                updateZtpAlert(data.ztp_alerts);
            } catch (e) {
                console.error('Error parsing SSE data:', e);
            }
        };
        
        eventSource.addEventListener('ended', () => {
            window.location.href = '/live-monitoring';
        });
        
        eventSource.onerror = () => {
            setTimeout(initEventSource, 5000);
        };
    }

    window.toggleFloatingWindow = function() {
        const isVisible = floatingWindow.classList.contains('visible');
        if (isVisible) {
            floatingWindow.classList.remove('visible');
            sessionStorage.setItem('floatingWindowVisible', 'false');
        } else {
            floatingWindow.classList.add('visible');
            sessionStorage.setItem('floatingWindowVisible', 'true');
        }
    };

    window.handleAssist = function() {
        floatingChatInput.value = 'Can you help me with this call?';
        sendChatMessage();
    };

    window.handleWhatToSay = function() {
        const suggestion = floatingSuggestionText.textContent;
        if (suggestion && !floatingSuggestionText.classList.contains('floating-suggestion-loading')) {
            floatingChatInput.value = suggestion;
            sendChatMessage();
        }
    };

    window.sendChatMessage = function() {
        const message = floatingChatInput.value.trim();
        if (!message) return;
        
        floatingChatInput.value = '';
        floatingSuggestionText.textContent = 'Thinking...';
        floatingSuggestionText.classList.add('floating-suggestion-loading');
        
        fetch('/api/live-monitoring/chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                session_id: sessionId,
                question: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.answer) {
                floatingSuggestionText.textContent = data.answer;
                floatingSuggestionText.classList.remove('floating-suggestion-loading');
            }
        })
        .catch(error => {
            console.error('Chat error:', error);
            floatingSuggestionText.textContent = 'Sorry, I could not process that.';
            floatingSuggestionText.classList.add('floating-suggestion-loading');
        });
    };

    function init() {
        initPosition();
        
        const wasVisible = sessionStorage.getItem('floatingWindowVisible') !== 'false';
        if (wasVisible) {
            floatingWindow.classList.add('visible');
        }
        
        setInterval(updateTimer, 1000);
        updateTimer();
        
        initEventSource();
    }

    init();
})();
</script>
