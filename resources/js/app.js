import './bootstrap';
import { createApp } from 'vue';
import FloatingWindow from './components/FloatingWindow.vue';

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing Vue...');
    const appElement = document.getElementById('app');
    
    if (appElement) {
        try {
            const vueApp = createApp({
                components: {
                    FloatingWindow
                },
                data() {
                    return {
                        floatingWindowVisible: false
                    };
                },
                methods: {
                    updateMainTimer(time) {
                        const timer = document.getElementById('mainTimer');
                        if (timer) timer.textContent = time;
                    },
                    toggleFloatingWindow() {
                        this.floatingWindowVisible = !this.floatingWindowVisible;
                    }
                },
                mounted() {
                    window.vueApp = this;
                    window.toggleFloatingWindow = this.toggleFloatingWindow;
                }
            });
            
            vueApp.mount('#app');
            console.log('Vue app mounted successfully');
        } catch (error) {
            console.error('Error mounting Vue app:', error);
        }
    } else {
        console.log('#app element not found');
    }
});
