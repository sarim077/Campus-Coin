import { createWorker } from 'tesseract.js';
import { parseReceiptText } from './receipt-parser.js';

document.addEventListener('DOMContentLoaded', () => {
    const scanner = document.querySelector('[data-receipt-scanner]');
    if (!scanner) return;

    const fileInput = scanner.querySelector('#receiptImage');
    const scanButton = scanner.querySelector('#scanReceiptButton');
    const progress = scanner.querySelector('#receiptScanProgress');
    const result = scanner.querySelector('#receiptScanResult');
    const transactionForm = document.querySelector('form[action$="/transactions"]');
    const typeInputs = [...document.querySelectorAll('input[name="type"]')];
    const descriptionInput = document.querySelector('#createDescription');
    const amountInput = transactionForm?.querySelector('input[name="amount"]');
    const dateInput = transactionForm?.querySelector('input[name="date"]');
    const categorySelect = transactionForm?.querySelector('#categorySelect');
    const suggestionInput = transactionForm?.querySelector('#aiSuggestedCategoryId');

    if (
        !(fileInput instanceof HTMLInputElement)
        || !(scanButton instanceof HTMLButtonElement)
        || !(progress instanceof HTMLElement)
        || !(result instanceof HTMLElement)
        || !transactionForm
        || !(descriptionInput instanceof HTMLInputElement)
        || !(amountInput instanceof HTMLInputElement)
        || !(dateInput instanceof HTMLInputElement)
        || !(categorySelect instanceof HTMLSelectElement)
        || !(suggestionInput instanceof HTMLInputElement)
    ) {
        return;
    }

    const clearResult = () => {
        result.classList.add('d-none');
        result.textContent = '';
    };

    fileInput.addEventListener('change', () => {
        clearResult();
        progress.textContent = '';
        const file = fileInput.files?.[0];
        scanButton.disabled = !file;
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 10 * 1024 * 1024) {
            fileInput.value = '';
            scanButton.disabled = true;
            progress.textContent = 'Choose a JPEG, PNG, or WebP image no larger than 10 MB.';
        }
    });

    scanButton.addEventListener('click', async () => {
        const file = fileInput.files?.[0];
        if (!file) return;

        scanButton.disabled = true;
        progress.textContent = 'Preparing the document on this device…';
        clearResult();

        let worker;
        try {
            const bitmap = await createImageBitmap(file);
            if (bitmap.width * bitmap.height > 24_000_000) {
                bitmap.close();
                throw new Error('The selected image has too many pixels to scan safely.');
            }
            const scale = Math.min(1, 2200 / Math.max(bitmap.width, bitmap.height));
            const canvas = document.createElement('canvas');
            canvas.width = Math.max(1, Math.round(bitmap.width * scale));
            canvas.height = Math.max(1, Math.round(bitmap.height * scale));
            canvas.getContext('2d', { willReadFrequently: true }).drawImage(bitmap, 0, 0, canvas.width, canvas.height);
            bitmap.close();

            const ocrAssetBase = import.meta.env.DEV
                ? new URL(/* @vite-ignore */ '/ocr-assets/', import.meta.url).href
                : new URL(/* @vite-ignore */ './ocr/', import.meta.url).href;
            worker = await createWorker('eng', 1, {
                workerPath: new URL('worker.min.js', ocrAssetBase).href,
                corePath: ocrAssetBase,
                langPath: ocrAssetBase.replace(/\/$/, ''),
                gzip: true,
                logger: (message) => {
                    if (message.status === 'recognizing text') {
                        progress.textContent = `Reading document… ${Math.round(message.progress * 100)}%`;
                    } else if (message.status) {
                        progress.textContent = `${message.status.charAt(0).toUpperCase()}${message.status.slice(1)}…`;
                    }
                },
            });

            const { data } = await worker.recognize(canvas);
            const fields = parseReceiptText(data.text);

            const selectedType = typeInputs.find((input) => input.checked);
            const nextType = typeInputs.find((input) => input.value === fields.type);
            if (selectedType && nextType && selectedType !== nextType) {
                nextType.checked = true;
                nextType.dispatchEvent(new Event('change', { bubbles: true }));
            }

            if (fields.description) {
                descriptionInput.value = fields.description;
                descriptionInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (fields.amountFound) amountInput.value = fields.amount;
            if (fields.dateFound) dateInput.value = fields.date;
            suggestionInput.value = '';

            try {
                const suggestedCategoryId = await suggestCategory(fields.description, fields.type);
                if (suggestedCategoryId) {
                    categorySelect.value = String(suggestedCategoryId);
                    suggestionInput.value = String(suggestedCategoryId);
                }
            } catch (error) {
                console.error('Receipt category suggestion failed.', error);
            }

            result.classList.remove('d-none');
            result.textContent = fields.amountFound
                ? 'Details were filled from the scan. Check the transaction type, amount, date, and category before saving.'
                : 'The document was read, but no clear amount was found. Enter the amount and review the other fields before saving.';
            progress.textContent = fields.description ? 'Document scan complete.' : 'Scan complete. Add a description before saving.';
        } catch (error) {
            console.error('Receipt scanning failed.', error);
            progress.textContent = 'Could not read this image. Try a sharper, well-lit photo, or enter the transaction manually.';
        } finally {
            if (worker) await worker.terminate();
            scanButton.disabled = false;
        }
    });

    async function suggestCategory(description, type) {
        if (description.length < 3) return null;

        const response = await fetch(scanner.dataset.categorySuggestionUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ description, type }),
        });

        if (!response.ok) {
            throw new Error('Category suggestion request failed.');
        }

        const suggestion = await response.json();
        const matchingOption = [...categorySelect.options].find((option) => (
            option.value === String(suggestion.suggested_category_id)
            && option.dataset.type === type
        ));

        return matchingOption ? suggestion.suggested_category_id : null;
    }
});
