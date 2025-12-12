import { describe, it, expect } from 'vitest';

/**
 * Format tanggal ke format Indonesia
 */
function formatDateIndonesian(dateString) {
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  
  const date = new Date(dateString);
  const day = date.getDate();
  const month = months[date.getMonth()];
  const year = date.getFullYear();
  
  return `${day} ${month} ${year}`;
}

/**
 * Hitung selisih hari
 */
function getDaysDifference(date1, date2) {
  const d1 = new Date(date1);
  const d2 = new Date(date2);
  const diffTime = Math.abs(d2 - d1);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
}

describe('Date Utilities', () => {
  describe('formatDateIndonesian', () => {
    it('should format date in Indonesian format', () => {
      expect(formatDateIndonesian('2025-12-09')).toBe('9 Desember 2025');
      expect(formatDateIndonesian('2025-01-01')).toBe('1 Januari 2025');
      expect(formatDateIndonesian('2025-06-15')).toBe('15 Juni 2025');
    });

    it('should handle different date formats', () => {
      expect(formatDateIndonesian('2025/12/09')).toBe('9 Desember 2025');
    });
  });

  describe('getDaysDifference', () => {
    it('should calculate days difference correctly', () => {
      expect(getDaysDifference('2025-01-01', '2025-01-08')).toBe(7);
      expect(getDaysDifference('2025-01-01', '2025-01-02')).toBe(1);
    });

    it('should work regardless of date order', () => {
      expect(getDaysDifference('2025-01-08', '2025-01-01')).toBe(7);
    });

    it('should return 0 for same dates', () => {
      expect(getDaysDifference('2025-01-01', '2025-01-01')).toBe(0);
    });
  });
});
