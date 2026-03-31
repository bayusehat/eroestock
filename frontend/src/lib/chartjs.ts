import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Tooltip,
  Legend,
  Filler,
  type ChartOptions,
} from "chart.js";

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  LineElement,
  PointElement,
  ArcElement,
  Tooltip,
  Legend,
  Filler
);

const FALLBACK_COLORS = [
  "#2563eb",
  "#f97316",
  "#22c55e",
  "#a855f7",
  "#ef4444",
];

export function getChartColors(count = 5): string[] {
  if (typeof window === "undefined") return FALLBACK_COLORS.slice(0, count);
  const style = getComputedStyle(document.documentElement);
  return Array.from({ length: count }, (_, i) => {
    const value = style.getPropertyValue(`--chart-${i + 1}`).trim();
    return value ? `hsl(${value})` : FALLBACK_COLORS[i % FALLBACK_COLORS.length];
  });
}

export const commonBarOptions: ChartOptions<"bar"> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { grid: { display: false } },
    y: {
      ticks: {
        callback: (v) => `${Number(v) / 1000}k`,
      },
    },
  },
};

export const commonLineOptions: ChartOptions<"line"> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { grid: { display: false } },
    y: {
      ticks: {
        callback: (v) => `${Number(v) / 1000}k`,
      },
    },
  },
};

export const commonPieOptions: ChartOptions<"pie"> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "bottom" },
  },
};

export const commonDoughnutOptions: ChartOptions<"doughnut"> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: "bottom" },
  },
};
