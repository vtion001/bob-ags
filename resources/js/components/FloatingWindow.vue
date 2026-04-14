<template>
    <div 
        v-if="show"
        class="floating-window"
        :style="{ left: position.x + 'px', top: position.y + 'px', right: 'auto', bottom: 'auto' }"
    >
        <div class="floating-header drag-handle" @mousedown="startDrag">
            <div class="flex items-center gap-3">
                <img src="https://res.cloudinary.com/dbviya1rj/image/upload/q_auto/f_auto/v1775144933/gkigls8alfr7bm4h1rhh.png" alt="BOB" class="w-6 h-6">
                <span class="text-white font-semibold">BOB AI Assistant</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="session-timer text-xs">{{ formattedTime }}</span>
                <button class="minimize-btn" @click="$emit('toggle', false)" title="Hide Assistant">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="floating-body">
            <div class="transcript-section">
                <h4 class="text-xs text-gray-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Live Transcript
                </h4>
                <div class="transcript-lines">
                    <div 
                        v-for="(line, index) in transcripts" 
                        :key="index"
                        :class="['transcript-line', line.speaker]"
                    >
                        <strong>{{ line.speaker === 'agent' ? 'Agent' : 'Caller' }}:</strong> 
                        {{ line.text }}
                    </div>
                    <p v-if="transcripts.length === 0" class="text-gray-500 text-sm">
                        Waiting for transcript...
                    </p>
                </div>
            </div>

            <div class="suggestion-card">
                <h4 class="text-blue-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    What to Say
                </h4>
                <p class="text-white text-sm leading-relaxed">{{ whatToSay || 'Loading suggestions...' }}</p>
            </div>

            <div class="suggestion-card">
                <h4 class="text-green-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Follow-up Questions
                </h4>
                <div class="follow-up-list">
                    <div 
                        v-for="(question, index) in followUpQuestions" 
                        :key="index"
                        class="follow-up-item"
                        @click="selectFollowUp(question)"
                    >
                        {{ question }}
                    </div>
                    <p v-if="followUpQuestions.length === 0" class="text-gray-400 text-sm">
                        No follow-up questions available
                    </p>
                </div>
            </div>

            <div class="suggestion-card">
                <h4 class="text-purple-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Suggested Resources
                </h4>
                <div class="resource-tags">
                    <span 
                        v-for="(resource, index) in suggestedResources" 
                        :key="index"
                        class="resource-tag"
                    >
                        {{ resource }}
                    </span>
                    <span v-if="suggestedResources.length === 0" class="text-gray-400 text-sm">
                        No resources available
                    </span>
                </div>
            </div>

            <div v-if="ztpAlert" class="ztp-alert">
                <h4 class="text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    ZTP Alert
                </h4>
                <p class="text-red-300 text-sm">{{ ztpAlert.message }}</p>
            </div>
        </div>

        <div class="floating-input">
            <div class="input-wrapper">
                <input 
                    type="text" 
                    v-model="chatMessage"
                    @keypress.enter="sendChat"
                    class="chat-input" 
                    placeholder="Ask a question or type transcript..."
                >
                <button class="send-btn" @click="sendChat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FloatingWindow',
    props: {
        sessionId: {
            type: String,
            required: true
        },
        startTime: {
            type: String,
            default: () => new Date().toISOString()
        },
        show: {
            type: Boolean,
            default: true
        }
    },
    data() {
        return {
            position: { x: window.innerWidth - 440, y: window.innerHeight - 620 },
            isDragging: false,
            dragOffset: { x: 0, y: 0 },
            elapsedSeconds: 0,
            transcripts: [],
            whatToSay: '',
            followUpQuestions: [],
            suggestedResources: [],
            ztpAlert: null,
            chatMessage: '',
            eventSource: null
        };
    },
    computed: {
        formattedTime() {
            const hours = Math.floor(this.elapsedSeconds / 3600).toString().padStart(2, '0');
            const minutes = Math.floor((this.elapsedSeconds % 3600) / 60).toString().padStart(2, '0');
            const seconds = (this.elapsedSeconds % 60).toString().padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        },
        sessionStartTime() {
            return new Date(this.startTime);
        }
    },
    mounted() {
        this.initEventSource();
        this.startTimer();
    },
    beforeUnmount() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    },
    methods: {
        initEventSource() {
            this.eventSource = new EventSource(`/live-monitoring/session/${this.sessionId}/stream`);
            
            this.eventSource.onmessage = (event) => {
                const data = JSON.parse(event.data);
                this.updateData(data);
            };
            
            this.eventSource.addEventListener('ended', () => {
                window.location.href = '/live-monitoring';
            });
            
            this.eventSource.onerror = () => {
                setTimeout(() => this.initEventSource(), 3000);
            };
        },
        updateData(data) {
            if (data.transcripts) {
                this.transcripts = data.transcripts;
            }
            if (data.suggestions) {
                this.whatToSay = data.suggestions.what_to_say || '';
                this.followUpQuestions = data.suggestions.follow_up_questions || [];
                this.suggestedResources = data.suggestions.suggested_resources || [];
            }
            if (data.ztp_alerts && data.ztp_alerts.length > 0) {
                this.ztpAlert = data.ztp_alerts[data.ztp_alerts.length - 1];
            }
        },
        startTimer() {
            setInterval(() => {
                this.elapsedSeconds = Math.floor((new Date() - this.sessionStartTime) / 1000);
                this.$emit('time-update', this.formattedTime);
            }, 1000);
        },
        startDrag(e) {
            this.isDragging = true;
            this.dragOffset = {
                x: e.clientX - this.position.x,
                y: e.clientY - this.position.y
            };
            
            document.addEventListener('mousemove', this.onDrag);
            document.addEventListener('mouseup', this.stopDrag);
        },
        onDrag(e) {
            if (!this.isDragging) return;
            
            this.position = {
                x: Math.max(0, Math.min(e.clientX - this.dragOffset.x, window.innerWidth - 420)),
                y: Math.max(0, Math.min(e.clientY - this.dragOffset.y, window.innerHeight - 600))
            };
        },
        stopDrag() {
            this.isDragging = false;
            document.removeEventListener('mousemove', this.onDrag);
            document.removeEventListener('mouseup', this.stopDrag);
        },
        selectFollowUp(question) {
            this.chatMessage = question;
            this.sendChat();
        },
        async sendChat() {
            if (!this.chatMessage.trim()) return;
            
            try {
                const response = await fetch('/api/live-monitoring/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        session_id: this.sessionId,
                        question: this.chatMessage
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.whatToSay = data.answer;
                }
                this.chatMessage = '';
            } catch (error) {
                console.error('Chat error:', error);
            }
        }
    }
};
</script>

<style scoped>
.floating-window {
    position: fixed;
    width: 420px;
    height: 600px;
    background: linear-gradient(145deg, #1a1f35 0%, #0d1117 100%);
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.floating-header {
    background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
    padding: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    cursor: move;
}

.floating-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.transcript-section {
    background: rgba(30, 41, 59, 0.5);
    border-radius: 12px;
    padding: 12px;
    max-height: 200px;
    overflow-y: auto;
}

.transcript-line {
    padding: 4px 0;
    font-size: 13px;
    line-height: 1.5;
}

.transcript-line.agent {
    color: #60a5fa;
}

.transcript-line.caller {
    color: #34d399;
}

.suggestion-card {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(99, 102, 241, 0.1) 100%);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 12px;
    padding: 14px;
}

.suggestion-card h4 {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.follow-up-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.follow-up-item {
    background: rgba(52, 211, 153, 0.1);
    border: 1px solid rgba(52, 211, 153, 0.3);
    border-radius: 8px;
    padding: 10px 12px;
    font-size: 13px;
    color: #6ee7b7;
    cursor: pointer;
    transition: all 0.2s;
}

.follow-up-item:hover {
    background: rgba(52, 211, 153, 0.2);
    transform: translateX(4px);
}

.ztp-alert {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.1) 100%);
    border: 1px solid rgba(239, 68, 68, 0.4);
    border-radius: 12px;
    padding: 14px;
    animation: pulse-alert 2s infinite;
}

@keyframes pulse-alert {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    50% { box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
}

.ztp-alert h4 {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.resource-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.resource-tag {
    background: rgba(168, 85, 247, 0.15);
    border: 1px solid rgba(168, 85, 247, 0.3);
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 12px;
    color: #c4b5fd;
}

.floating-input {
    padding: 16px;
    background: rgba(15, 23, 42, 0.8);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.input-wrapper {
    display: flex;
    gap: 8px;
}

.chat-input {
    flex: 1;
    background: rgba(30, 41, 59, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 12px 16px;
    color: white;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.chat-input:focus {
    border-color: #3b82f6;
}

.send-btn {
    background: #3b82f6;
    border: none;
    border-radius: 12px;
    padding: 12px 16px;
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}

.send-btn:hover {
    background: #2563eb;
}

.minimize-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 6px;
    padding: 6px;
    color: white;
    cursor: pointer;
    transition: background 0.2s;
}

.minimize-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.session-timer {
    font-family: monospace;
    font-size: 14px;
    color: #60a5fa;
}
</style>
