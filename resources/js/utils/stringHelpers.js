/**
 * String Helper Functions
 * Global utility functions for string manipulation
 */

/**
 * Generate URL-friendly slug from text
 * @param {string} text - Text to convert to slug
 * @returns {string} URL-friendly slug
 */
export const generateSlug = (text) => {
  if (!text) return '';
  
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '') // Remove special characters
    .replace(/[\s_-]+/g, '-') // Replace spaces and underscores with hyphens
    .replace(/^-+|-+$/g, ''); // Remove leading/trailing hyphens
};

/**
 * Check if slug is unique (placeholder for API call)
 * @param {string} slug - Slug to check
 * @param {string} excludeId - ID to exclude from check (for updates)
 * @returns {Promise<boolean>} True if slug is unique
 */
export const checkSlugUnique = async (slug, excludeId = null) => {
  // This would make an API call to check uniqueness
  // For now, return true (backend will handle validation)
  return true;
};

/**
 * Generate unique slug with counter if needed
 * @param {string} text - Base text to convert
 * @param {Function} checkFunction - Function to check uniqueness
 * @param {string} excludeId - ID to exclude from check
 * @returns {Promise<string>} Unique slug
 */
export const generateUniqueSlug = async (text, checkFunction, excludeId = null) => {
  let slug = generateSlug(text);
  let counter = 1;
  
  while (!(await checkFunction(slug, excludeId))) {
    slug = `${generateSlug(text)}-${counter}`;
    counter++;
  }
  
  return slug;
};

/**
 * Capitalize first letter of each word
 * @param {string} text - Text to capitalize
 * @returns {string} Capitalized text
 */
export const capitalizeWords = (text) => {
  if (!text) return '';
  
  return text
    .toString()
    .toLowerCase()
    .split(' ')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

/**
 * Truncate text to specified length
 * @param {string} text - Text to truncate
 * @param {number} length - Maximum length
 * @param {string} suffix - Suffix to add (default: '...')
 * @returns {string} Truncated text
 */
export const truncateText = (text, length, suffix = '...') => {
  if (!text) return '';
  
  if (text.length <= length) return text;
  
  return text.substring(0, length - suffix.length) + suffix;
};
