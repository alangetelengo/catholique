{{-- Même logique que GED layouts/app : confirmation avant suppression (flashAlert) --}}
<div id="flashAlertModal" class="fixed inset-0 z-[9999] items-center justify-center p-4 bg-black/45 hidden" style="animation: gedFadeIn 0.2s ease;">
    <div class="flash-modal-panel bg-white dark:bg-slate-800 rounded-2xl p-8 sm:p-10 max-w-[440px] w-full text-center shadow-2xl border border-slate-200/80 dark:border-slate-600/80" style="animation: gedSlideUp 0.25s ease;" onclick="event.stopPropagation()">
        <p id="flashAlertDocTitle" class="hidden mb-2 text-left text-sm font-semibold text-slate-800 dark:text-slate-100 leading-snug break-words"></p>
        <div class="text-5xl mb-3" id="flashAlertIcon" aria-hidden="true">⚠️</div>
        <h3 id="flashAlertTitle" class="text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">Confirmation</h3>
        <p id="flashAlertMessage" class="text-slate-600 dark:text-slate-400 text-[0.95rem] leading-relaxed mb-7 whitespace-pre-line"></p>
        <div id="flashAlertCustomSlot" class="hidden mb-6 text-left relative max-w-full"></div>
        <div id="flashAlertInputContainer" class="hidden mb-6 text-left"></div>
        <div id="flashAlertActions" class="flex flex-wrap gap-3 justify-center">
            <button type="button" id="flashAlertCancelBtn" onclick="flashAlertCancel()" class="min-h-[38px] px-6 py-2.5 rounded-lg border-[1.5px] border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-600 dark:text-slate-200 font-semibold text-sm cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors">
                Annuler
            </button>
            <button type="button" id="flashAlertConfirmBtn" class="min-h-[38px] px-6 py-2.5 rounded-lg border-[1.5px] border-transparent bg-red-500 text-white font-semibold text-sm cursor-pointer hover:bg-red-600 transition-colors">
                Confirmer
            </button>
        </div>
    </div>
</div>
<style>
@keyframes gedFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes gedSlideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
#flashAlertModal.flex { display: flex; }
</style>
<script>
(function () {
    var _form = null;
    var _cb   = null;
    var _inputOpt = null;
    var _onConfirm = null;
    function flashAlertRestoreCustomSlot(poolId) {
        var slot = document.getElementById('flashAlertCustomSlot');
        var pool = document.getElementById(poolId || 'envoi-validation-flash-pool');
        if (!slot || !pool) return;
        while (slot.firstChild) pool.appendChild(slot.firstChild);
        slot.classList.add('hidden');
        slot.style.display = '';
    }
    window.flashAlert = function (message, formOrCb, options) {
        options = options || {};
        flashAlertRestoreCustomSlot(options.customBodyPoolId);
        var docTitleEl = document.getElementById('flashAlertDocTitle');
        var msgEl = document.getElementById('flashAlertMessage');
        var titleEl = document.getElementById('flashAlertTitle');
        if (options.documentTitle) {
            docTitleEl.textContent = '« ' + String(options.documentTitle) + ' »';
            docTitleEl.classList.remove('hidden');
        } else {
            docTitleEl.textContent = '';
            docTitleEl.classList.add('hidden');
        }
        msgEl.textContent = message;
        if (String(message || '').trim() === '') {
            msgEl.classList.add('hidden');
            msgEl.style.marginBottom = '0';
            titleEl.style.marginBottom = '0.75rem';
        } else {
            msgEl.classList.remove('hidden');
            msgEl.style.marginBottom = '1.75rem';
            titleEl.style.marginBottom = '0.5rem';
        }
        titleEl.textContent   = options.title || 'Confirmation';
        document.getElementById('flashAlertIcon').textContent    = options.icon  || '⚠️';
        var btn = document.getElementById('flashAlertConfirmBtn');
        btn.textContent      = options.confirmText || (options.noCancel ? 'OK' : 'Confirmer');
        btn.style.background = options.danger === false ? '#00b464'
                             : options.noCancel         ? '#3b82f6'
                             : '#ef4444';
        var cancelBtn = document.getElementById('flashAlertCancelBtn');
        cancelBtn.style.display = options.noCancel ? 'none' : '';
        _form = (formOrCb instanceof HTMLElement) ? formOrCb : null;
        _cb   = (typeof formOrCb === 'function')  ? formOrCb : null;
        _inputOpt = options.input || null;
        _onConfirm = typeof options.onConfirm === 'function' ? options.onConfirm : null;
        var inpContainer = document.getElementById('flashAlertInputContainer');
        inpContainer.classList.add('hidden');
        inpContainer.innerHTML = '';
        var customSlot = document.getElementById('flashAlertCustomSlot');
        if (options.customBodyId) {
            var pool = document.getElementById(options.customBodyPoolId || 'envoi-validation-flash-pool');
            var el = document.getElementById(options.customBodyId);
            if (customSlot && pool && el && pool.contains(el)) {
                customSlot.appendChild(el);
                customSlot.classList.remove('hidden');
                customSlot.style.display = 'block';
            }
        }
        if (_inputOpt) {
            var label = document.createElement('label');
            label.setAttribute('for', 'flashAlertInput');
            label.className = 'block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2';
            label.textContent = _inputOpt.label || 'Motif';
            var ta = document.createElement('textarea');
            ta.id = 'flashAlertInput';
            ta.name = _inputOpt.name || 'commentaire';
            ta.placeholder = _inputOpt.placeholder || '';
            ta.required = !!_inputOpt.required;
            ta.className = 'w-full min-h-[80px] px-3 py-2.5 border-[1.5px] border-slate-200 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 rounded-lg text-sm resize-y';
            inpContainer.appendChild(label);
            inpContainer.appendChild(ta);
            inpContainer.classList.remove('hidden');
        }
        var modal = document.getElementById('flashAlertModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };
    window.flashAlertCancel = function () {
        flashAlertRestoreCustomSlot();
        var modal = document.getElementById('flashAlertModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        _form = null; _cb = null; _inputOpt = null; _onConfirm = null;
    };
    function doConfirm() {
        var inp = document.getElementById('flashAlertInput');
        if (_inputOpt && inp) {
            var val = (inp.value || '').trim();
            if (_inputOpt.required && !val) {
                inp.style.borderColor = '#ef4444';
                inp.focus();
                return;
            }
            if (_form && val) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = _inputOpt.name || 'commentaire';
                hidden.value = val;
                _form.appendChild(hidden);
            }
        }
        if (_form && _onConfirm) {
            try {
                if (_onConfirm(_form) === false) return;
            } catch (e) {}
        }
        var modal = document.getElementById('flashAlertModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        flashAlertRestoreCustomSlot();
        if (_cb) { _cb(); }
        else if (_form) { _form.submit(); }
        _form = null; _cb = null; _inputOpt = null; _onConfirm = null;
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('flashAlertConfirmBtn').addEventListener('click', doConfirm);
        document.getElementById('flashAlertModal').addEventListener('click', function (e) {
            if (e.target === this) flashAlertCancel();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            var m = document.getElementById('flashAlertModal');
            if (!m || m.classList.contains('hidden')) return;
            flashAlertCancel();
        });
    });
})();
</script>
{{-- Pool vide pour flashAlert customBody (compatibilité GED) --}}
<div id="envoi-validation-flash-pool" class="hidden" aria-hidden="true"></div>
