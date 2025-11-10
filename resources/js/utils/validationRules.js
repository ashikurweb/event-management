/**
 * Common validation rules for forms
 * Reusable validation rules for Ant Design Vue forms
 */

/**
 * Email validation rules
 */
export const emailRules = [
  { required: true, message: 'Please enter your email', trigger: 'blur' },
  { type: 'email', message: 'Please enter a valid email', trigger: 'blur' },
];

/**
 * Password validation rules
 */
export const passwordRules = [
  { required: true, message: 'Please enter your password', trigger: 'blur' },
  { min: 8, message: 'Password must be at least 8 characters', trigger: 'blur' },
];

/**
 * Strong password validation rules (with pattern)
 */
export const strongPasswordRules = [
  { required: true, message: 'Please enter your password', trigger: 'blur' },
  { min: 8, message: 'Password must be at least 8 characters', trigger: 'blur' },
  { 
    pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/, 
    message: 'Password must contain uppercase, lowercase, and number', 
    trigger: 'blur' 
  },
];

/**
 * First name validation rules
 */
export const firstNameRules = [
  { required: true, message: 'Please enter your first name', trigger: 'blur' },
  { min: 2, message: 'First name must be at least 2 characters', trigger: 'blur' },
];

/**
 * Last name validation rules
 */
export const lastNameRules = [
  { required: true, message: 'Please enter your last name', trigger: 'blur' },
  { min: 2, message: 'Last name must be at least 2 characters', trigger: 'blur' },
];

/**
 * Password confirmation validator factory
 * @param {Object} form - The form reactive object
 * @returns {Function} Validator function
 */
export const createPasswordConfirmationValidator = (form) => {
  return (rule, value) => {
    if (!value) {
      return Promise.reject('Please confirm your password');
    }
    if (value !== form.password) {
      return Promise.reject('Passwords do not match');
    }
    return Promise.resolve();
  };
};

/**
 * Password confirmation rules factory
 * @param {Object} form - The form reactive object
 * @returns {Array} Validation rules
 */
export const passwordConfirmationRules = (form) => [
  { required: true, validator: createPasswordConfirmationValidator(form), trigger: 'blur' },
];

/**
 * Terms agreement validation rules
 */
export const termsRules = [
  { 
    validator: (rule, value) => 
      value ? Promise.resolve() : Promise.reject('You must agree to the terms'), 
    trigger: 'change' 
  },
];

/**
 * Login form validation rules
 */
export const loginRules = {
  email: emailRules,
  password: passwordRules,
};

/**
 * Register form validation rules factory
 * @param {Object} form - The form reactive object
 * @returns {Object} Validation rules
 */
export const registerRules = (form) => ({
  first_name: firstNameRules,
  last_name: lastNameRules,
  email: emailRules,
  password: strongPasswordRules,
  password_confirmation: passwordConfirmationRules(form),
  terms: termsRules,
});

/**
 * Mail mailer validation rules
 */
export const mailMailerRules = [
  { required: true, message: 'Please enter mail mailer', trigger: 'blur' },
];

/**
 * Mail host validation rules
 */
export const mailHostRules = [
  { required: true, message: 'Please enter mail host', trigger: 'blur' },
];

/**
 * Mail port validation rules
 */
export const mailPortRules = [
  { required: true, message: 'Please enter mail port', trigger: 'blur' },
];

/**
 * Mail username validation rules
 */
export const mailUsernameRules = [
  { required: true, message: 'Please enter mail username', trigger: 'blur' },
];

/**
 * Mail password validation rules
 */
export const mailPasswordRules = [
  { required: true, message: 'Please enter mail password', trigger: 'blur' },
];

/**
 * Mail encryption validation rules
 */
export const mailEncryptionRules = [
  { required: true, message: 'Please enter mail encryption', trigger: 'blur' },
];

/**
 * Mail from address validation rules
 */
export const mailFromAddressRules = [
  { required: true, message: 'Please enter mail from address', trigger: 'blur' },
];

/**
 * Mail configuration form validation rules
 */
export const mailRules = {
  mail_mailer: mailMailerRules,
  mail_host: mailHostRules,
  mail_port: mailPortRules,
  mail_username: mailUsernameRules,
  mail_password: mailPasswordRules,
  mail_encryption: mailEncryptionRules,
  mail_from_address: mailFromAddressRules,
};

