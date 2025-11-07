/**
 * Ant Design Vue Theme Configuration
 * Professional Event Management UI - Modern, Beautiful Color Palette
 * Reference: https://www.antdv.com/docs/vue/customize-theme
 */

export const antdTheme = {
  token: {
    // Primary Color - Modern Blue/Teal (Professional & Trustworthy)
    colorPrimary: '#1890ff',           // Modern Blue
    colorPrimaryHover: '#40a9ff',      // Lighter Blue
    colorPrimaryActive: '#096dd9',      // Darker Blue
    
    // Success Color - Fresh Green
    colorSuccess: '#52c41a',            // Success Green
    colorSuccessHover: '#73d13d',
    colorSuccessActive: '#389e0d',
    
    // Warning Color - Warm Orange
    colorWarning: '#faad14',            // Warning Orange
    colorWarningHover: '#ffc53d',
    colorWarningActive: '#d48806',
    
    // Error Color - Soft Red
    colorError: '#ff4d4f',              // Error Red
    colorErrorHover: '#ff7875',
    colorErrorActive: '#cf1322',
    
    // Info Color - Sky Blue
    colorInfo: '#1890ff',               // Info Blue
    colorInfoHover: '#40a9ff',
    colorInfoActive: '#096dd9',
    
    // Link Color - Primary Blue
    colorLink: '#1890ff',
    colorLinkHover: '#40a9ff',
    colorLinkActive: '#096dd9',
    
    // Text Colors
    colorText: 'rgba(0, 0, 0, 0.85)',   // Main Text
    colorTextSecondary: 'rgba(0, 0, 0, 0.65)', // Secondary Text
    colorTextTertiary: 'rgba(0, 0, 0, 0.45)', // Tertiary Text
    colorTextDisabled: 'rgba(0, 0, 0, 0.25)', // Disabled Text
    
    // Background Colors
    colorBgContainer: '#ffffff',        // Container Background
    colorBgElevated: '#fafafa',        // Elevated Background
    colorBgLayout: '#f0f2f5',          // Layout Background
    colorBgSpotlight: '#fafafa',       // Spotlight Background
    
    // Border Colors
    colorBorder: '#d9d9d9',            // Default Border
    colorBorderSecondary: '#f0f0f0',    // Secondary Border
    
    // Border Radius - Modern Rounded
    borderRadius: 8,                    // Default Border Radius
    borderRadiusLG: 12,                // Large Border Radius
    borderRadiusSM: 4,                 // Small Border Radius
    
    // Font
    fontFamily: 'Instrument Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif',
    fontSize: 14,
    fontSizeLG: 16,
    fontSizeSM: 12,
    
    // Spacing
    padding: 16,
    paddingLG: 24,
    paddingSM: 12,
    
    // Shadow - Modern Soft Shadows
    boxShadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
    boxShadowSecondary: '0 4px 12px rgba(0, 0, 0, 0.15)',
  },
  components: {
    Button: {
      borderRadius: 8,
      controlHeight: 40,
      fontWeight: 500,
      primaryShadow: '0 2px 0 rgba(0, 0, 0, 0.045)',
    },
    Input: {
      borderRadius: 8,
      controlHeight: 40,
      paddingInline: 12,
    },
    Card: {
      borderRadius: 12,
      paddingLG: 24,
    },
    Table: {
      borderRadius: 8,
    },
    Menu: {
      borderRadius: 8,
      itemBorderRadius: 6,
    },
    Select: {
      borderRadius: 8,
      controlHeight: 40,
    },
    DatePicker: {
      borderRadius: 8,
      controlHeight: 40,
    },
  },
};

