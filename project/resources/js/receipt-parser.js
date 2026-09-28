const amountPattern = /(?:rs\.?|inr|usd|pkr|eur|gbp|[$€£₹])?\s*((?:\d{1,3}(?:,\d{3})+|\d+)(?:\.\d{1,2})?)/gi;
const incomePattern = /\b(prize|award|awarded|won|winning|winner|competition|contest|scholarship|grant|reward|cash prize|cheque received|check received|payment received|payout|refund|reimbursement|cashback|bonus)\b/i;
const amountPriorityPattern = /\b(grand total|total amount|amount due|total|amount|paid|prize|award|won|winning|cheque|check|payout|received)\b/i;
const amountExcludePattern = /\b(sub\s?total|tax|vat|discount|change|cash tendered|balance due|check no\.?|cheque no\.?|receipt no\.?|invoice no\.?|transaction id)\b/i;
const datePattern = /\b(?:\d{4}[/-]\d{1,2}[/-]\d{1,2}|\d{1,2}[/-]\d{1,2}[/-]\d{2,4})\b/;

function amountFromLine(line) {
    const withoutDates = line.replace(datePattern, ' ');
    const matches = [...withoutDates.matchAll(amountPattern)];

    return matches
        .map((match) => ({
            value: Number.parseFloat(match[1].replaceAll(',', '')),
            hasDecimal: match[1].includes('.'),
            hasCurrency: /(?:rs\.?|inr|usd|pkr|eur|gbp|[$€£₹])/i.test(match[0]),
        }))
        .filter((match) => Number.isFinite(match.value) && match.value > 0 && match.value <= 1_000_000_000)
        .filter((match) => match.hasDecimal || match.hasCurrency || amountPriorityPattern.test(line));
}

function normalizeDate(text) {
    const isoDate = text.match(/\b(\d{4})[/-](\d{1,2})[/-](\d{1,2})\b/);
    if (isoDate) {
        return makeDate(isoDate[1], isoDate[2], isoDate[3]);
    }

    const dayFirstDate = text.match(/\b(\d{1,2})[/-](\d{1,2})[/-](\d{2,4})\b/);
    if (dayFirstDate) {
        const year = dayFirstDate[3].length === 2 ? `20${dayFirstDate[3]}` : dayFirstDate[3];
        return makeDate(year, dayFirstDate[2], dayFirstDate[1])
            ?? makeDate(year, dayFirstDate[1], dayFirstDate[2]);
    }

    const writtenDate = text.match(/\b(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\s+(\d{1,2}),?\s+(\d{4})\b/i);
    if (writtenDate) {
        const month = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec']
            .findIndex((name) => writtenDate[1].toLowerCase().startsWith(name)) + 1;
        return makeDate(writtenDate[3], month, writtenDate[2]);
    }

    return null;
}

function makeDate(year, month, day) {
    const date = new Date(Date.UTC(Number(year), Number(month) - 1, Number(day)));
    if (date.getUTCFullYear() !== Number(year) || date.getUTCMonth() !== Number(month) - 1 || date.getUTCDate() !== Number(day)) {
        return null;
    }

    return date.toISOString().slice(0, 10);
}

function findDescription(lines) {
    const ignored = /\b(receipt|tax invoice|invoice|duplicate|customer copy|merchant copy|terminal|cashier|thank you|www\.|http|tel:|phone|address|receipt no|check no|cheque no|transaction id)\b/i;

    return lines.find((line) => {
        const text = line.trim();
        return text.length >= 3
            && text.length <= 100
            && !ignored.test(text)
            && !datePattern.test(text)
            && !/^[\d\s.,$€£₹/-]+$/.test(text)
            && amountFromLine(text).length === 0;
    })?.trim().slice(0, 255) ?? '';
}

export function parseReceiptText(text) {
    const lines = text.split(/\r?\n/).map((line) => line.trim()).filter(Boolean);
    const candidates = lines.flatMap((line, lineIndex) => {
        if (amountExcludePattern.test(line)) {
            return [];
        }

        return amountFromLine(line).map((amount) => ({
            amount: amount.value.toFixed(2),
            priority: amountPriorityPattern.test(line) ? 2 : 1,
            lineIndex,
        }));
    }).sort((first, second) => second.priority - first.priority || second.lineIndex - first.lineIndex);
    const date = normalizeDate(text);

    const checkForIncome = /\b(pay to the order|issued to|recipient|check no\.?|cheque no\.?)\b/i.test(text);

    return {
        type: incomePattern.test(text) || checkForIncome ? 'income' : 'expense',
        amount: candidates[0]?.amount ?? '',
        date: date ?? new Date().toISOString().slice(0, 10),
        description: findDescription(lines),
        amountFound: candidates.length > 0,
        dateFound: date !== null,
    };
}
