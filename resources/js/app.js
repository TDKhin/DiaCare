import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.diaCareAlertSound = (unreadKeys = []) => ({
    enabled: localStorage.getItem('diacare-alert-sound-enabled') === 'true', unreadKeys,
    init() {
        if (!this.enabled || !this.unreadKeys.length) return;
        const seen = JSON.parse(localStorage.getItem('diacare-seen-alert-keys') || '[]');
        if (this.unreadKeys.some((key) => !seen.includes(key))) this.play();
        localStorage.setItem('diacare-seen-alert-keys', JSON.stringify([...new Set([...seen, ...this.unreadKeys])].slice(-100)));
    },
    toggle() { this.enabled = !this.enabled; localStorage.setItem('diacare-alert-sound-enabled', this.enabled.toString()); if (this.enabled) { this.play(); localStorage.setItem('diacare-seen-alert-keys', JSON.stringify(this.unreadKeys.slice(-100))); } },
    play() {
        try { const context = new (window.AudioContext || window.webkitAudioContext)(); const beep = (frequency, start) => { const oscillator = context.createOscillator(); const gain = context.createGain(); oscillator.frequency.value = frequency; gain.gain.setValueAtTime(.07, context.currentTime + start); gain.gain.exponentialRampToValueAtTime(.001, context.currentTime + start + .18); oscillator.connect(gain).connect(context.destination); oscillator.start(context.currentTime + start); oscillator.stop(context.currentTime + start + .2); }; beep(880, 0); beep(1175, .23); } catch (error) { /* Audio is unavailable or blocked by this browser. */ }
    },
});

window.activityCheckin = () => ({
    showForm: false, glucose: '', symptoms: false, complications: false, fitness: 'new', minutes: 15, recommendation: null, checking: false, error: '',
    async checkExercise() {
        this.checking = true; this.error = ''; this.recommendation = null;
        try { const response = await fetch('/activity/recommendation', { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ glucose: this.glucose || null, symptoms: this.symptoms, complications: this.complications, fitness: this.fitness, minutes: this.minutes }) }); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'Unable to prepare a recommendation.'); this.recommendation = data; } catch (error) { this.error = error.message; } finally { this.checking = false; }
    },
});

window.diaCareAssistant = () => ({
    sidebarOpen: false,
    assistantOpen: false,
    messages: [],
    draft: '',
    sending: false,

    async init() {
        document.addEventListener('open-assistant', () => { this.assistantOpen = true; this.$nextTick(() => this.scrollMessages()); });
        try {
            const response = await fetch('/assistant/history', { credentials: 'same-origin', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (response.ok) this.messages = (await response.json()).messages || [];
        } catch (error) { /* Assistant remains available. */ }

        if (this.isAuthenticated()) {
            localStorage.removeItem('diacare-guest-chat');
        } else if (!this.messages.length) {
            try { this.messages = JSON.parse(localStorage.getItem('diacare-guest-chat') || '[]'); } catch (error) { this.messages = []; }
        }
    },
    async sendMessage() {
        const content = this.draft.trim(); if (!content || this.sending) return;
        this.messages.push({ id: `local-${Date.now()}`, role: 'user', content }); this.draft = ''; this.sending = true; this.$nextTick(() => this.scrollMessages());
        try {
            const data = await this.request('/assistant/messages', {
                method: 'POST',
                body: JSON.stringify({ message: content, history: this.messages.slice(-10).map(({ role, content }) => ({ role, content })), _token: this.csrfToken() }),
            });
            this.messages.push(data.message);
            if (!this.isAuthenticated()) { try { localStorage.setItem('diacare-guest-chat', JSON.stringify(this.messages.slice(-20))); } catch (error) { /* Storage is optional. */ } }
        } catch (error) { this.messages.push({ id: `error-${Date.now()}`, role: 'assistant', content: error.message }); } finally { this.sending = false; this.$nextTick(() => this.scrollMessages()); }
    },
    async clearHistory() {
        if (this.sending) return;
        const authenticated = this.isAuthenticated();
        this.messages = [];
        localStorage.removeItem('diacare-guest-chat');
        this.$nextTick(() => this.scrollMessages());

        if (!authenticated) return;

        try {
            await this.request('/assistant/history/clear', { method: 'POST', body: JSON.stringify({ _token: this.csrfToken() }) });
        } catch (error) {
            console.error('Unable to clear saved assistant history:', error);
        }
    },
    isAuthenticated() { return document.body?.dataset.authenticated === 'true'; },
    csrfToken() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) throw new Error('Your security token is unavailable. Refresh the page and try again.');
        return token;
    },
    async request(url, options = {}) {
        const token = this.csrfToken();
        const response = await fetch(url, {
            ...options,
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token, ...(options.headers || {}) },
        });
        const isJson = response.headers.get('content-type')?.includes('application/json');
        const data = isJson ? await response.json() : { message: await response.text() };
        if (!response.ok) {
            if (response.status === 419) throw new Error('Your session expired. Refresh the page and send your message again.');
            throw new Error(data.message || 'The assistant is unavailable right now.');
        }
        return data;
    },
    scrollMessages() { if (this.$refs.assistantMessages) this.$refs.assistantMessages.scrollTop = this.$refs.assistantMessages.scrollHeight; },
});

window.mealPhotoAnalyzer = (targetMin = 90, targetMax = 130) => ({
    file: null, fileName: '', photoUrl: '', cameraOpen: false, stream: null, analyzing: false, saving: false, saved: false, result: null, error: '', preMealGlucose: null, targetMin, targetMax,
    chooseFile(event) { this.setFile(event.target.files[0]); },
    get estimatedPostMealGlucose() { const carbs = Number(this.result?.carbohydrates_g); const baseline = Number(this.preMealGlucose); if (!Number.isFinite(carbs) || !Number.isFinite(baseline)) return null; return Math.round(baseline + (carbs * 1.5)); },
    get isMealEstimateHigh() { return this.estimatedPostMealGlucose !== null && this.estimatedPostMealGlucose > this.targetMax; },
    setFile(file) { if (!file) return; this.file = file; this.fileName = file.name; this.photoUrl = URL.createObjectURL(file); this.result = null; this.saved = false; this.error = ''; },
    async openCamera() {
        this.error = ''; if (!navigator.mediaDevices?.getUserMedia) { this.error = 'Camera access is not supported by this browser. Choose a photo instead.'; return; }
        try { this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false }); this.cameraOpen = true; this.$nextTick(() => { if (this.$refs.video) this.$refs.video.srcObject = this.stream; }); } catch (error) { this.error = 'Camera permission was not granted. You can still choose a photo.'; }
    },
    capture() { const video = this.$refs.video; const canvas = this.$refs.canvas; if (!video || !canvas) return; canvas.width = video.videoWidth || 1280; canvas.height = video.videoHeight || 720; canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height); canvas.toBlob((blob) => this.setFile(new File([blob], `meal-${Date.now()}.jpg`, { type: 'image/jpeg' })), 'image/jpeg', .9); this.closeCamera(); },
    closeCamera() { this.stream?.getTracks().forEach((track) => track.stop()); this.stream = null; this.cameraOpen = false; },
    compressImage(file) { return new Promise((resolve) => { const image = new Image(); image.onload = () => { const scale = Math.min(1, 1280 / Math.max(image.width, image.height)); const canvas = document.createElement('canvas'); canvas.width = Math.round(image.width * scale); canvas.height = Math.round(image.height * scale); canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height); canvas.toBlob((blob) => resolve(new File([blob], 'meal-optimized.jpg', { type: 'image/jpeg' })), 'image/jpeg', .82); }; image.onerror = () => resolve(file); image.src = URL.createObjectURL(file); }); },
    async analyze() {
        if (!this.file || this.analyzing) return; if (!Number.isFinite(Number(this.preMealGlucose)) || this.preMealGlucose < 20 || this.preMealGlucose > 600) { this.error = 'Enter a current pre-meal glucose reading from 20 to 600 mg/dL before analysing.'; return; } this.analyzing = true; this.error = ''; this.result = null;
        const form = new FormData(); form.append('photo', await this.compressImage(this.file));
        try { const response = await fetch('/meal-photo/analyze', { method: 'POST', credentials: 'same-origin', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: form }); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'The meal could not be analyzed.'); this.result = data.analysis; } catch (error) { this.error = error.message; } finally { this.analyzing = false; }
    },
    async saveToDiet() {
        if (!this.file || !this.result || this.saving || this.saved) return;
        const baseline = Number(this.preMealGlucose);
        if (!Number.isFinite(baseline) || baseline < 20 || baseline > 600) { this.error = 'Please enter a glucose value from 20 to 600 mg/dL.'; return; }
        this.preMealGlucose = Math.round(baseline);
        this.saving = true; this.error = '';
        const form = new FormData(); form.append('photo', await this.compressImage(this.file)); form.append('analysis', JSON.stringify({ ...this.result, pre_meal_glucose: Number(this.preMealGlucose), estimated_post_meal_glucose: this.estimatedPostMealGlucose, glucose_level: null }));
        try { const response = await fetch('/meal-photo/save', { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: form }); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'The meal could not be saved.'); this.saved = true; } catch (error) { this.error = error.message; } finally { this.saving = false; }
    },
});

window.glucosePhotoAnalyzer = () => ({
    file: null, fileName: '', photoUrl: '', cameraOpen: false, stream: null, analyzing: false, confirming: false, result: null, error: '', context: 'Photo reading',
    chooseFile(event) { this.setFile(event.target.files[0]); },
    setFile(file) { if (!file) return; if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) { this.error = 'Please choose a JPG, PNG, or WebP image.'; return; } this.file = file; this.fileName = file.name; this.photoUrl = URL.createObjectURL(file); this.result = null; this.error = ''; },
    async openCamera() { this.error = ''; if (!navigator.mediaDevices?.getUserMedia) { this.error = 'Camera access is not supported by this browser. Choose a photo instead.'; return; } try { this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false }); this.cameraOpen = true; this.$nextTick(() => { if (this.$refs.video) this.$refs.video.srcObject = this.stream; }); } catch (error) { this.error = 'Camera permission was not granted. You can still choose a photo.'; } },
    capture() { const video = this.$refs.video; const canvas = this.$refs.canvas; if (!video || !canvas) return; canvas.width = video.videoWidth || 1280; canvas.height = video.videoHeight || 720; canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height); canvas.toBlob((blob) => this.setFile(new File([blob], `glucose-${Date.now()}.jpg`, { type: 'image/jpeg' })), 'image/jpeg', .92); this.closeCamera(); },
    closeCamera() { this.stream?.getTracks().forEach((track) => track.stop()); this.stream = null; this.cameraOpen = false; },
    async analyze() { if (!this.file || this.analyzing) return; this.analyzing = true; this.error = ''; this.result = null; const form = new FormData(); form.append('photo', this.file); try { const response = await fetch('/glucose-photo/analyze', { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: form }); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'The glucose photo could not be analyzed.'); this.result = { ...data.analysis, id: data.id }; if (!this.result.value) this.error = 'No clear glucose value was found. Please try a sharper photo with the full display visible.'; } catch (error) { this.error = error.message; } finally { this.analyzing = false; } },
    async confirmReading() { if (!this.result?.value || this.confirming) return; this.confirming = true; this.error = ''; try { const response = await fetch(`/glucose-photo/${this.result.id || ''}/confirm`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ value: this.result.value, context: this.context }) }); const data = await response.json(); if (!response.ok) throw new Error(data.message || 'The reading could not be saved.'); window.location.reload(); } catch (error) { this.error = error.message; } finally { this.confirming = false; } },
});

Alpine.start();
