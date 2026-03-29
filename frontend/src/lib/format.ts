export function formatCurrency(value: number, currency = "IDR"): string {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
}

/** Format a number for display in currency/price inputs (thousand separators, decimals) */
export function formatNumberForInput(value: number, decimals = 2): string {
  if (Number.isNaN(value) || value === null || value === undefined) return "";
  const num = Number(value);
  if (num === 0) return "";
  return new Intl.NumberFormat("id-ID", {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(num);
}

/** Format for live display while typing - thousand separators, optional decimals (1.234,5) */
export function formatNumberForInputLive(value: number, maxDecimals = 2): string {
  if (Number.isNaN(value) || value === null || value === undefined) return "";
  const num = Number(value);
  if (num === 0) return "";
  return new Intl.NumberFormat("id-ID", {
    minimumFractionDigits: 0,
    maximumFractionDigits: maxDecimals,
  }).format(num);
}

/** Parse a formatted number string back to a number. Handles id-ID format (1.234.567,89) - comma=decimal, period=thousands. */
export function parseFormattedNumber(value: string): number {
  if (!value || typeof value !== "string") return 0;
  const trimmed = value.trim();
  if (!trimmed) return 0;
  const commaIdx = trimmed.indexOf(",");
  let cleaned: string;
  if (commaIdx >= 0) {
    const intPart = trimmed.slice(0, commaIdx).replace(/\./g, "").replace(/[^\d]/g, "") || "0";
    const decPart = trimmed.slice(commaIdx + 1).replace(/[^\d]/g, "").slice(0, 2);
    cleaned = decPart ? `${intPart}.${decPart}` : intPart;
  } else {
    cleaned = trimmed.replace(/\./g, "").replace(/[^\d]/g, "") || "0";
  }
  const parsed = parseFloat(cleaned);
  return Number.isNaN(parsed) ? 0 : parsed;
}

export function formatDate(date: string): string {
  return new Date(date).toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
  });
}

export function formatDateTime(date: string): string {
  return new Date(date).toLocaleDateString("id-ID", {
    day: "2-digit",
    month: "short",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}
