import { describe, it, expect } from 'vitest';

/**
 * Utility function untuk menjumlahkan dua angka
 */
function add(a, b) {
  return a + b;
}

/**
 * Utility function untuk mengurangi dua angka
 */
function subtract(a, b) {
  return a - b;
}

describe('Math Utilities', () => {
  it('should add two numbers correctly', () => {
    expect(add(2, 3)).toBe(5);
    expect(add(-1, 1)).toBe(0);
    expect(add(0, 0)).toBe(0);
  });

  it('should subtract two numbers correctly', () => {
    expect(subtract(5, 3)).toBe(2);
    expect(subtract(10, 5)).toBe(5);
    expect(subtract(0, 0)).toBe(0);
  });

  it('should handle floating point numbers', () => {
    expect(add(1.5, 2.5)).toBeCloseTo(4);
    expect(subtract(5.5, 2.5)).toBeCloseTo(3);
  });
});
