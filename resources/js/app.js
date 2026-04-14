import './bootstrap';

import Alpine from 'alpinejs';
import { createApp } from 'vue';
import FloatingWindow from './components/FloatingWindow.vue';

window.Alpine = Alpine;
window.createApp = createApp;

Alpine.start();

let vueApp = null;

document.addEventListener('DOMContentLoaded', function() {
    const appElement = document.getElementById('app');
    
    if (appElement) {
        vueApp = createApp({
            components: {
                FloatingWindow
            },
            data() {
                return {
                    floatingWindowVisible: true
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
            }
        });
        
        vueApp.mount('#app');
        
        window.vueApp = vueApp;
        
        console.log('Vue app mounted successfully');
        console.log('vueApp:', vueApp);
        console.log('FloatingWindow should be visible:', vueApp._instance.proxy.floatingWindowVisible);
    } else {
        console.error('Could not find #app element');
    }
});
