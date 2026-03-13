import { apiClient } from "@/lib/api";

export async function downloadPdf(url: string, filename: string): Promise<void> {
  const res = await apiClient.get(url, { responseType: "blob" });
  const blob = new Blob([res.data as Blob], { type: "application/pdf" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  link.click();
  URL.revokeObjectURL(link.href);
}
