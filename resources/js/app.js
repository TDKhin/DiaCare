import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

window.diaCareAssistant = () => ({
    sidebarOpen: false,
    assistantOpen: false,
    messages: [],
    draft: '',
    sending: false,

    async init() {
        document.addEventListener('open-assistant', () => {
            this.assistantOpen = true;
            this.$nextTick(() => this.scrollMessages());
        });

        try {
            const response = await fetch('/assistant/history', { headers: { Accept: 'application/json' } });
            if (response.ok) {
                const data = await response.json();
                this.messages = data.messages || [];
            }
        } catch (error) {
            // The assistant remains available even when history cannot be loaded.
        }
    },

    async sendMessage() {
        const content = this.draft.trim();
        if (!content || this.sending) return;

        this.messages.push({ id: `local-${Date.now()}`, role: 'user', content });
        this.draft = '';
        this.sending = true;
        this.$nextTick(() => this.scrollMessages());

        try {
            const response = await fetch('/assistant/messages', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ message: content }),
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'The assistant is unavailable right now.');
            this.messages.push(data.message);
        } catch (error) {
            this.messages.push({ id: `error-${Date.now()}`, role: 'assistant', content: error.message });
        } finally {
            this.sending = false;
            this.$nextTick(() => this.scrollMessages());
        }
    },

    scrollMessages() {
        if (this.$refs.assistantMessages) this.$refs.assistantMessages.scrollTop = this.$refs.assistantMessages.scrollHeight;
    },
});

window.mealPhotoAnalyzer = () => ({
    file: null,
    fileName: '',
    photoUrl: '',
    cameraOpen: false,
    stream: null,
    analyzing: false,
    result: null,
    error: '',

    chooseFile(event) {
        this.setFile(event.target.files[0]);
    },

    setFile(file) {
        if (!file) return;
        this.file = file;
        this.fileName = file.name;
        this.photoUrl = URL.createObjectURL(file);
        this.result = null;
        this.error = '';
    },

    async openCamera() {
        this.error = '';
        if (!navigator.mediaDevices?.getUserMedia) {
            this.error = 'Camera access is not supported by this browser. Choose a photo from your device instead.';
            return;
        }
        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
            this.cameraOpen = true;
            this.$nextTick(() => { if (this.$refs.video) this.$refs.video.srcObject = this.stream; });
        } catch (error) {
            this.error = 'Camera permission was not granted. You can still choose a photo from your device.';
        }
    },

    capture() {
        const video = this.$refs.video;
        const canvas = this.$refs.canvas;
        if (!video || !canvas) return;
        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
        canvas.toBlob((blob) => this.setFile(new File([blob], `meal-${Date.now()}.jpg`, { type: 'image/jpeg' })), 'image/jpeg', .9);
        this.closeCamera();
    },

    closeCamera() {
        this.stream?.getTracks().forEach((track) => track.stop());
        this.stream = null;
        this.cameraOpen = false;
    },

    async analyze() {
        if (!this.file || this.analyzing) return;
        this.analyzing = true;
        this.error = '';
        this.result = null;
        const form = new FormData();
        form.append('photo', this.file);
        try {
            const response = await fetch('/meal-photo/analyze', {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: form,
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'The meal could not be analyzed.');
            this.result = data.analysis;
        } catch (error) {
            this.error = error.message;
        } finally {
            this.analyzing = false;
        }
    },
});

Alpine.start();
