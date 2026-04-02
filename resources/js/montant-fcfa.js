function stripMontantFcfa(str) {
    if (str == null || typeof str !== 'string') {
        return '';
    }

    let s = str.toLowerCase().replace(/fcfa/g, '').replace(/\s/g, '').trim();
    if (s === '') {
        return '';
    }

    // Gère les variantes "46 500,00", "46,500.00", "46500.00"
    if (s.includes(',') && s.includes('.')) {
        if (s.lastIndexOf(',') > s.lastIndexOf('.')) {
            // Format FR: 46.500,00
            s = s.replace(/\./g, '').replace(',', '.');
        } else {
            // Format EN: 46,500.00
            s = s.replace(/,/g, '');
        }
    } else if (s.includes(',')) {
        const parts = s.split(',');
        if (parts.length === 2 && parts[1].length <= 2) {
            // 46500,00 -> 46500.00
            s = parts[0] + '.' + parts[1];
        } else {
            // 46,500 -> 46500
            s = s.replace(/,/g, '');
        }
    } else if ((s.match(/\./g) || []).length > 1) {
        // Plusieurs points: on garde le dernier comme séparateur décimal
        const parts = s.split('.');
        const decimals = parts.pop();
        s = parts.join('') + '.' + decimals;
    }

    s = s.replace(/[^0-9.]/g, '');
    const n = Number.parseFloat(s);
    if (!Number.isFinite(n)) {
        return '';
    }

    return String(Math.round(n));
}

function formatMontantFcfa(str) {
    const raw = stripMontantFcfa(str);
    if (raw === '') {
        return '';
    }
    const n = parseInt(raw, 10);
    if (!Number.isFinite(n)) {
        return '';
    }
    const intFmt = String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    return `${intFmt} fcfa`;
}

function bindMontantsFcfa(root = document) {
    root.addEventListener(
        'focusin',
        (e) => {
            const el = e.target;
            if (!(el instanceof HTMLInputElement) || !el.classList.contains('js-montant-fcfa')) {
                return;
            }
            const raw = stripMontantFcfa(el.value);
            el.value = raw === '' ? '' : raw;
        },
        true
    );
    root.addEventListener(
        'focusout',
        (e) => {
            const el = e.target;
            if (!(el instanceof HTMLInputElement) || !el.classList.contains('js-montant-fcfa')) {
                return;
            }
            const formatted = formatMontantFcfa(el.value);
            el.value = formatted;
        },
        true
    );

    root.addEventListener(
        'submit',
        (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }
            form.querySelectorAll('input.js-montant-fcfa').forEach((input) => {
                if (!(input instanceof HTMLInputElement)) {
                    return;
                }
                input.value = stripMontantFcfa(input.value);
            });
        },
        true
    );
}

function initMontantsFcfa() {
    if (typeof document === 'undefined') {
        return;
    }
    if (window.__adventisteMontantFcfaBound) {
        return;
    }
    window.__adventisteMontantFcfaBound = true;
    bindMontantsFcfa(document);

    document.querySelectorAll('input.js-montant-fcfa').forEach((el) => {
        if (!(el instanceof HTMLInputElement) || el.value.trim() === '') {
            return;
        }
        el.value = formatMontantFcfa(el.value);
    });
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMontantsFcfa, { once: true });
    } else {
        initMontantsFcfa();
    }
}
