const CONFIG = {
    yoloApiUrl: document.querySelector('meta[name="yolo-api-url"]')?.content || 'http://localhost:5000',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    frameIntervalMs: 400,
    stableThreshold: 0.35,
    stableFramesRequired: 3,
    pollIntervalMs: 3000,
};

let isSubmitting = false;
let cameraStream = null;
let detectInterval = null;
let stableCount = 0;
let lastStableFood = null;
let currentDetection = { food_name: null, confidence: 0 };
let lastCapturedBlob = null;
let activeMealType = 'pagi';

const elements = {};

function initCameraModule() {
    elements.modal = document.getElementById('camera-modal');
    elements.video = document.getElementById('camera-video');
    elements.canvas = document.getElementById('camera-canvas');
    elements.mealTypeSelect = document.getElementById('camera-meal-type');
    elements.detectionLabel = document.getElementById('camera-detection-label');
    elements.confidenceLabel = document.getElementById('camera-confidence-label');
    elements.statusLabel = document.getElementById('camera-status-label');
    elements.confirmBtn = document.getElementById('camera-confirm-btn');
    elements.errorLabel = document.getElementById('camera-error-label');

    if (!elements.modal) {
        return;
    }

    window.openUploadModal = openCameraModal;
    window.closeUploadModal = closeCameraModal;

    elements.confirmBtn?.addEventListener('click', submitDetection);
}

export function openCameraModal(mealType) {
    if (!elements.modal) {
        return;
    }

    activeMealType = mealType || 'pagi';
    if (elements.mealTypeSelect) {
        elements.mealTypeSelect.value = activeMealType;
    }

    resetDetectionState();
    elements.modal.classList.remove('hidden');
    elements.errorLabel?.classList.add('hidden');
    setStatus('Membuka kamera...');

    startCamera();
}

export function closeCameraModal() {
    stopCamera();
    elements.modal?.classList.add('hidden');
}

function resetDetectionState() {
    stableCount = 0;
    lastStableFood = null;
    isSubmitting = false;
    currentDetection = { food_name: null, confidence: 0 };
    lastCapturedBlob = null;
    updateDetectionUI(null, 0);
    if (elements.confirmBtn) {
        elements.confirmBtn.disabled = true;
    }
}

async function startCamera() {
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } },
            audio: false,
        });

        if (elements.video) {
            elements.video.srcObject = cameraStream;
            await elements.video.play();
        }

        setStatus('Arahkan kamera ke makanan...');
        startDetectionLoop();
    } catch (error) {
        showError('Tidak bisa akses kamera. Izinkan permission kamera di browser.');
        setStatus('Kamera gagal dibuka');
        console.error(error);
    }
}

function stopCamera() {
    if (detectInterval) {
        clearInterval(detectInterval);
        detectInterval = null;
    }

    if (cameraStream) {
        cameraStream.getTracks().forEach((track) => track.stop());
        cameraStream = null;
    }

    if (elements.video) {
        elements.video.srcObject = null;
    }
}

function startDetectionLoop() {
    if (detectInterval) {
        clearInterval(detectInterval);
    }

    detectInterval = setInterval(captureAndDetect, CONFIG.frameIntervalMs);
}

async function captureAndDetect() {
    if (!elements.video || !elements.canvas || elements.video.readyState < 2) {
        return;
    }

    const canvas = elements.canvas;
    const video = elements.video;
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;

    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

    try {
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
        if (!blob) {
            return;
        }

        lastCapturedBlob = blob;

        const form = new FormData();
        form.append('frame', blob, 'frame.jpg');

        const response = await fetch(`${CONFIG.yoloApiUrl}/api/detect/frame`, {
            method: 'POST',
            body: form,
        });

        const result = await response.json();
        currentDetection = {
            food_name: result.food_name,
            confidence: result.confidence || 0,
        };

        updateDetectionUI(currentDetection.food_name, currentDetection.confidence);
        trackStability(currentDetection);
    } catch (error) {
        setStatus('Flask YOLO tidak terjangkau. Pastikan python run.py aktif.');
        console.error(error);
    }
}

function trackStability(detection) {
    if (
        detection.food_name &&
        detection.confidence >= CONFIG.stableThreshold &&
        detection.food_name === lastStableFood
    ) {
        stableCount += 1;
    } else if (
        detection.food_name &&
        detection.confidence >= CONFIG.stableThreshold
    ) {
        stableCount = 1;
        lastStableFood = detection.food_name;
    } else {
        stableCount = 0;
        lastStableFood = null;
    }

    // if (stableCount >= CONFIG.stableFramesRequired && elements.confirmBtn) {
    //     elements.confirmBtn.disabled = false;
    //     setStatus(`Terdeteksi: ${detection.food_name}. Klik Simpan atau tunggu auto-submit.`);
    // }

    // if (stableCount >= CONFIG.stableFramesRequired + 2) {
    //     submitDetection();
    // }
}

function updateDetectionUI(foodName, confidence) {
    if (elements.detectionLabel) {
        elements.detectionLabel.textContent = foodName || '—';
    }
    if (elements.confidenceLabel) {
        elements.confidenceLabel.textContent = foodName
            ? `${Math.round(confidence * 100)}%`
            : '—';
    }
    if (elements.confirmBtn && foodName && confidence >= CONFIG.stableThreshold) {
        elements.confirmBtn.disabled = false;
    }
}

function setStatus(message) {
    if (elements.statusLabel) {
        elements.statusLabel.textContent = message;
    }
}

function showError(message) {
    if (elements.errorLabel) {
        elements.errorLabel.textContent = message;
        elements.errorLabel.classList.remove('hidden');
    }
}

async function submitDetection() {
    if (isSubmitting) {
        return;
    }

    if (!currentDetection.food_name || currentDetection.confidence < CONFIG.stableThreshold) {
        showError('Belum ada makanan terdeteksi dengan confidence cukup.');
        return;
    }

    isSubmitting = true;

    if (elements.confirmBtn) {
        elements.confirmBtn.disabled = true;
    }

    setStatus('Menyimpan deteksi...');
    stopCamera();

    const mealType = elements.mealTypeSelect?.value || activeMealType;

    try {
        const formData = new FormData();
        formData.append('meal_type', mealType);
        formData.append('detected_food_name', currentDetection.food_name);
        formData.append('detection_confidence', currentDetection.confidence);
        formData.append('date', new Date().toISOString().slice(0, 10));
        if (lastCapturedBlob) {
            formData.append('photo', lastCapturedBlob, 'capture.jpg');
        }

        const response = await fetch('/meal-logs', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': CONFIG.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();

        if (!response.ok) {
            let message = data.message || 'Gagal menyimpan deteksi.';
            if (data.errors) {
                message = Object.values(data.errors).flat().join(', ');
            }
            throw new Error(message);
        }

        closeCameraModal();
        startPollingMealLog(data.data?.id);
        window.location.reload();
    } catch (error) {
        isSubmitting = false;
        showError(error.message || 'Gagal menyimpan ke Laravel.');
        setStatus('Gagal menyimpan. Coba lagi.');
        if (elements.confirmBtn) {
            elements.confirmBtn.disabled = false;
        }
        startCamera();
    }
}

function startPollingMealLog(mealLogId) {
    if (!mealLogId) {
        return;
    }

    sessionStorage.setItem('pollMealLogId', String(mealLogId));
}

export function pollPendingMealLogs() {
    const pendingCards = document.querySelectorAll('[data-meal-log-id][data-meal-status="pending"]');

    pendingCards.forEach((card) => {
        const id = card.dataset.mealLogId;
        if (!id) {
            return;
        }

        pollMealLogStatus(id);
    });

    const storedId = sessionStorage.getItem('pollMealLogId');
    if (storedId) {
        pollMealLogStatus(storedId);
    }
}

async function pollMealLogStatus(mealLogId) {
    try {
        const response = await fetch(`/meal-logs/${mealLogId}/status`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();

        if (data.status === 'done') {
            sessionStorage.removeItem('pollMealLogId');
            window.location.reload();
        }
    } catch (error) {
        console.error('Poll failed', error);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initCameraModule();
    pollPendingMealLogs();
    setInterval(pollPendingMealLogs, CONFIG.pollIntervalMs);
});
