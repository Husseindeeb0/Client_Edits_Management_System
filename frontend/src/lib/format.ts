/** 1234.5 -> "1,234.50"; null -> "–" */
export function formatMoney(amount: number | null): string {
  if (amount === null) {
    return '–';
  }

  return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/** An ISO date-time from the API -> the local date, e.g. "11/18/2026"; null -> "–" */
export function formatDate(isoDate: string | null): string {
  return isoDate ? new Date(isoDate).toLocaleDateString() : '–';
}
