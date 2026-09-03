<x-app-layout>
    <div class="page-container" x-data="mealPhotoAnalyzer()">
        <div class="page-heading">
            <div><p class="eyebrow">More</p><h1>Analyze Your Meal</h1><p>Take or upload a photo to estimate nutrition and make meal logging easier.</p></div>
            <a href="{{ route('meals') }}" class="secondary-button">Back to meals</a>
        </div>

        <section class="card content-card meal-photo-card">
            <div class="meal-photo-grid">
                <div class="meal-photo-preview" :class="photoUrl ? 'has-photo' : ''">
                    <template x-if="photoUrl"><img :src="photoUrl" alt="Selected meal photo"></template>
                    <template x-if="!photoUrl"><div class="meal-photo-empty"><span class="upload-zone-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h3l1.5-2h7L17 8h3v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><circle cx="12" cy="13" r="3"/></svg></span><h3>Upload a meal photo</h3><p>Use a clear photo of your meal for the best estimate.</p></div></template>
                </div>
                <div class="meal-photo-controls">
                    <div class="card-title">Choose how to add your meal</div>
                    <p class="card-subtitle" style="line-height:1.7;margin-top:8px;">Your image is sent securely to the Laravel backend for analysis. Nothing is analyzed until you choose an image and press the button below.</p>
                    <div class="upload-options" style="margin-top:17px;">
                        <label class="secondary-button cursor-pointer"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M8 12h8M12 8v8"/></svg>Choose from device<input x-ref="file" type="file" class="hidden" accept="image/jpeg,image/png,image/webp" @change="chooseFile"></label>
                        <button type="button" class="primary-button" style="background:#ed9d39;border-color:#ed9d39;" @click="openCamera"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h3l1.5-2h7L17 8h3v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2Z"/><circle cx="12" cy="13" r="3"/></svg>Use camera</button>
                    </div>
                    <p class="selected-file" x-show="fileName" x-text="fileName"></p>
                    <button type="button" class="primary-button" style="margin-top:15px;width:100%;" :disabled="!file || analyzing" @click="analyze" x-text="analyzing ? 'Analyzing securely…' : 'Analyze photo'"></button>
                    <p class="form-help" x-show="error" x-text="error" style="color:#c4564f;margin-top:10px;"></p>
                </div>
            </div>

            <template x-if="result"><div class="analysis-result" style="margin-top:18px;"><div class="list-header"><div><div class="card-title" x-text="result.meal_name"></div><div class="card-subtitle" style="margin-top:4px;">Estimated nutrition · <span x-text="Math.round(result.confidence * 100) + '% confidence'"></span></div></div><span class="status-badge green">Analysis complete</span></div><div class="nutrition-grid" style="margin-top:15px;"><div class="nutrition-stat"><div class="nutrition-stat-label">Calories</div><div class="nutrition-stat-value" x-text="result.calories"></div></div><div class="nutrition-stat"><div class="nutrition-stat-label">Carbs</div><div class="nutrition-stat-value"><span x-text="result.carbohydrates_g"></span><span> g</span></div></div><div class="nutrition-stat"><div class="nutrition-stat-label">Protein</div><div class="nutrition-stat-value"><span x-text="result.protein_g"></span><span> g</span></div></div><div class="nutrition-stat"><div class="nutrition-stat-label">Fat</div><div class="nutrition-stat-value"><span x-text="result.fat_g"></span><span> g</span></div></div><div class="nutrition-stat"><div class="nutrition-stat-label">Fiber</div><div class="nutrition-stat-value"><span x-text="result.fiber_g"></span><span> g</span></div></div></div><p class="analysis-advice" style="margin-top:14px;" x-text="result.advice"></p><p class="form-help" style="margin-top:10px;">Estimates are for tracking only and are not medical advice.</p></div></template>
        </section>

        <div class="section-grid" style="margin-top:17px;"><section class="card content-card"><div class="card-title">Tips for a better estimate</div><div class="alert-list" style="margin-top:15px;"><div class="alert-item" style="padding:11px;"><span class="alert-item-icon green"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m5 12 4 4L19 6"/></svg></span><div><h3>Capture the full plate</h3><p>Include all visible ingredients and sides.</p></div></div><div class="alert-item" style="padding:11px;"><span class="alert-item-icon blue"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg></span><div><h3>Use natural light</h3><p>Bright, clear images help improve recognition.</p></div></div></div></section><section class="card content-card"><div class="card-title">Your privacy</div><p class="card-subtitle" style="line-height:1.7;margin-top:10px;">Images are stored privately under your account and are used only for your analysis request. Delete the saved record when you no longer need it.</p></section></div>

        <div x-show="cameraOpen" x-cloak class="camera-modal"><div class="camera-dialog"><div class="list-header"><div class="card-title">Take a meal photo</div><button type="button" class="icon-button" @click="closeCamera" aria-label="Close camera">×</button></div><video x-ref="video" autoplay playsinline class="camera-video"></video><canvas x-ref="canvas" class="hidden"></canvas><div class="form-actions" style="padding-top:13px;"><button type="button" class="secondary-button" @click="closeCamera">Cancel</button><button type="button" class="primary-button" @click="capture">Capture photo</button></div></div></div>
    </div>
</x-app-layout>
