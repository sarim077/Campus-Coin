import test from 'node:test';
import assert from 'node:assert/strict';
import { parseReceiptText } from '../../resources/js/receipt-parser.js';

test('reads a receipt total and merchant date as an expense', () => {
    assert.deepEqual(parseReceiptText(`GREEN MARKET
Receipt
Date: 09/24/2026
Subtotal 15.00
Tax 1.20
Total Rs. 16.20`), {
        type: 'expense',
        amount: '16.20',
        date: '2026-09-24',
        description: 'GREEN MARKET',
        amountFound: true,
        dateFound: true,
    });
});

test('recognizes an award document as income and extracts its award amount', () => {
    assert.deepEqual(parseReceiptText(`Campus Coding Competition
Award Notice
Congratulations, you won!
Prize amount: $1,250.00
September 26, 2026`), {
        type: 'income',
        amount: '1250.00',
        date: '2026-09-26',
        description: 'Campus Coding Competition',
        amountFound: true,
        dateFound: true,
    });
});

test('recognizes an issued check or prize payment as income', () => {
    const result = parseReceiptText('Check no. 345\nPay to the order of Alex\n$300.00');

    assert.equal(result.type, 'income');
    assert.equal(result.amount, '300.00');
});

test('recognizes a US-style receipt date when the first date component cannot be a day', () => {
    assert.equal(parseReceiptText('Market\nDate 09/24/2026\nTotal $5.00').date, '2026-09-24');
});

test('does not mistake a receipt subtotal for its total', () => {
    const result = parseReceiptText(`Corner Cafe
Subtotal 30.00
Total 25.00`);

    assert.equal(result.amount, '25.00');
});

test('leaves amounts empty when OCR text has no reliable monetary value', () => {
    const result = parseReceiptText('Award notice\nCongratulations to the winner!');

    assert.equal(result.type, 'income');
    assert.equal(result.amount, '');
    assert.equal(result.amountFound, false);
});
