<style>
    .common-process-modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .54);
        backdrop-filter: blur(6px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
    }
    .common-process-modal.show { display:flex; }
    .common-process-card {
        width: min(460px, 100%);
        background:#fff;
        border-radius:20px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .22);
        padding: 28px 24px;
        border:1px solid rgba(148,163,184,.2);
        display:grid;
        gap:18px;
        text-align:center;
    }
    .common-process-badge {
        width:76px;
        height:76px;
        border-radius:50%;
        margin:0 auto;
        display:grid;
        place-items:center;
        background: radial-gradient(circle at top, rgba(99,102,241,.24), rgba(99,102,241,.08));
        color:#6366f1;
        font-size:26px;
        font-weight:800;
        position:relative;
        overflow:hidden;
    }
    .common-process-badge::after {
        content:'';
        position:absolute;
        inset:-40%;
        background: conic-gradient(from 0deg, rgba(99,102,241,0) 0deg, rgba(99,102,241,.5) 120deg, rgba(99,102,241,0) 240deg);
        animation: commonSpin 1.35s linear infinite;
    }
    .common-process-badge span { position:relative; z-index:1; }
    .common-process-title { font-size:22px; font-weight:800; color:#0f172a; }
    .common-process-subtitle { font-size:13px; color:#64748b; line-height:1.7; }
    .common-process-progress {
        height:12px;
        border-radius:999px;
        background:#e2e8f0;
        overflow:hidden;
        position:relative;
    }
    .common-process-progress-bar {
        height:100%;
        width:0;
        background: linear-gradient(90deg, #6366f1, #22c55e);
        border-radius:999px;
        transition: width .35s ease;
        position:relative;
    }
    .common-process-progress-bar::after {
        content:'';
        position:absolute;
        top:0;
        right:-30%;
        width:30%;
        height:100%;
        background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,.45), rgba(255,255,255,0));
        animation: commonShimmer 1.25s linear infinite;
    }
    .common-process-percent {
        font-size:34px;
        font-weight:800;
        color:#6366f1;
        letter-spacing:-1px;
    }
    .common-process-stage {
        font-size:13px;
        color:#64748b;
        min-height:20px;
    }
    .common-process-note {
        font-size:12px;
        color:#475569;
        background:#f8fafc;
        border:1px solid #e2e8f0;
        border-radius:12px;
        padding:12px 14px;
        line-height:1.6;
    }
    @keyframes commonSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @keyframes commonShimmer {
        from { transform: translateX(-120%); }
        to { transform: translateX(360%); }
    }
</style>

<div class="common-process-modal" id="commonProcessModal" aria-hidden="true">
    <div class="common-process-card">
        <div class="common-process-badge"><span id="commonProcessBadgeIcon"><i class="fa-solid fa-cloud-arrow-down"></i></span></div>
        <div class="common-process-title" id="commonProcessTitle">Memproses</div>
        <div class="common-process-subtitle" id="commonProcessSubtitle">Mohon tunggu sebentar, sistem sedang menjalankan proses.</div>
        <div class="common-process-percent" id="commonProcessPercent">0%</div>
        <div class="common-process-progress">
            <div class="common-process-progress-bar" id="commonProcessBar"></div>
        </div>
        <div class="common-process-stage" id="commonProcessStage">Menyiapkan proses...</div>
        <div class="common-process-note" id="commonProcessNote">Jangan menutup tab atau memuat ulang halaman sampai proses selesai.</div>
    </div>
</div>

<script>
    var commonProcessTimer = null;
    var commonProcessPercent = 0;
    var commonProcessTotalStages = 1;

    function openProcessModal(options) {
        commonProcessPercent = 0;
        $('#commonProcessTitle').text(options.title || 'Memproses');
        $('#commonProcessSubtitle').text(options.subtitle || 'Mohon tunggu sebentar.');
        $('#commonProcessStage').text((options.stages || ['Menyiapkan proses...'])[0] || 'Menyiapkan proses...');
        $('#commonProcessNote').text(options.note || 'Jangan menutup tab atau memuat ulang halaman sampai proses selesai.');
        $('#commonProcessBadgeIcon').html(options.icon || '<i class="fa-solid fa-cloud-arrow-down"></i>');
        $('#commonProcessPercent').text('0%');
        $('#commonProcessBar').css('width', '0%');
        $('#commonProcessModal').addClass('show').attr('aria-hidden', 'false');

        var stages = options.stages || ['Menyiapkan proses...'];
        commonProcessTotalStages = stages.length;
        
        clearInterval(commonProcessTimer);
        commonProcessTimer = setInterval(function () {
            var limit = options.limit || 90;
            if (commonProcessPercent >= limit) {
                return;
            }

            commonProcessPercent += commonProcessPercent < 30 ? 7 : (commonProcessPercent < 65 ? 4 : 2);
            if (commonProcessPercent > limit) {
                commonProcessPercent = limit;
            }

            var stageIndex = Math.min(stages.length - 1, Math.floor((commonProcessPercent / 100) * stages.length));
            $('#commonProcessPercent').text(commonProcessPercent + '%');
            $('#commonProcessBar').css('width', commonProcessPercent + '%');
            $('#commonProcessStage').text(stages[stageIndex] || stages[stages.length - 1]);
        }, 360);
    }

    function completeProcessModal(success, message, callback) {
        clearInterval(commonProcessTimer);
        commonProcessPercent = 100;
        $('#commonProcessPercent').text('100%');
        $('#commonProcessBar').css('width', '100%');
        $('#commonProcessStage').text(success ? 'Proses selesai dan hasil sudah siap.' : 'Proses dihentikan karena terjadi kendala.');
        $('#commonProcessTitle').text(success ? 'Proses Berhasil' : 'Proses Gagal');
        $('#commonProcessSubtitle').text(message);
        $('#commonProcessBadgeIcon').html(success
            ? '<i class="fa-solid fa-circle-check"></i>'
            : '<i class="fa-solid fa-triangle-exclamation"></i>');

        setTimeout(function () {
            closeProcessModal();
            if (typeof callback === 'function') {
                callback();
            }
        }, 900);
    }

    function closeProcessModal() {
        clearInterval(commonProcessTimer);
        $('#commonProcessModal').removeClass('show').attr('aria-hidden', 'true');
    }
</script>
