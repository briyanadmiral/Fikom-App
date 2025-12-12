import { describe, it, expect, beforeEach } from 'vitest';

/**
 * Utility untuk validasi email
 */
function validateEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}

/**
 * Utility untuk validasi nomor HP
 */
function validatePhoneNumber(phone) {
  const phoneRegex = /^(\+62|0)[0-9]{9,12}$/;
  return phoneRegex.test(phone.replace(/\s/g, ''));
}

/**
 * Utility untuk capitalize string
 */
function capitalize(str) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

describe('String Validation Utilities', () => {
  describe('validateEmail', () => {
    it('should validate correct email addresses', () => {
      expect(validateEmail('user@example.com')).toBe(true);
      expect(validateEmail('test.name@domain.co.id')).toBe(true);
      expect(validateEmail('admin@surat-siega.app')).toBe(true);
    });

    it('should reject invalid email addresses', () => {
      expect(validateEmail('invalid.email')).toBe(false);
      expect(validateEmail('user@')).toBe(false);
      expect(validateEmail('@example.com')).toBe(false);
      expect(validateEmail('user @example.com')).toBe(false);
    });
  });

  describe('validatePhoneNumber', () => {
    it('should validate correct phone numbers with 62', () => {
      expect(validatePhoneNumber('+62812345678')).toBe(true);
      expect(validatePhoneNumber('+628123456789')).toBe(true);
    });

    it('should validate correct phone numbers with 0', () => {
      expect(validatePhoneNumber('081234567890')).toBe(true);
      expect(validatePhoneNumber('0812 3456 7890')).toBe(true);
    });

    it('should reject invalid phone numbers', () => {
      expect(validatePhoneNumber('081')).toBe(false);
      expect(validatePhoneNumber('12345')).toBe(false);
      expect(validatePhoneNumber('abc123')).toBe(false);
    });
  });

  describe('capitalize', () => {
    it('should capitalize first letter', () => {
      expect(capitalize('hello')).toBe('Hello');
      expect(capitalize('WORLD')).toBe('World');
      expect(capitalize('jAvA')).toBe('Java');
    });

    it('should handle empty and special cases', () => {
      expect(capitalize('')).toBe('');
      expect(capitalize(' ')).toBe(' ');
      expect(capitalize('a')).toBe('A');
    });
  });
});
