<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import FrontendLayout from '../../Layouts/FrontendLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import {
  MailOutlined,
  LockOutlined,
  GoogleOutlined,
  FacebookOutlined,
  GithubOutlined,
} from '@ant-design/icons-vue';
import { loginRules } from '../../utils/validationRules';

const loading = ref(false);

const form = reactive({
  email: '',
  password: '',
  remember: false,
});

const rules = loginRules;

const handleLogin = (values) => {
  loading.value = true;
  router.post('/login', values, {
    onFinish: () => {
      loading.value = false;
    },
  });
};

const handleSocialLogin = (provider) => {
  window.location.href = `/auth/${provider}/redirect`;
};
</script>

<template>
  <FrontendLayout>
    <div class="auth-container">
      <div class="auth-wrapper">
        <!-- Left Side - Branding with Image -->
        <div class="auth-branding">
          <div class="branding-image-wrapper">
            <img 
              src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&h=1200&fit=crop&q=80" 
              alt="EventHub"
              class="branding-image"
            />
            <div class="branding-overlay">
              <div class="brand-logo-overlay">
                <div class="logo-icon-overlay">E</div>
                <h1 class="brand-title-overlay">EventHub</h1>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="auth-form-container">
          <div class="auth-form-wrapper">
            <div class="auth-form-header">
              <h2 class="form-title">Welcome Back</h2>
              <p class="form-subtitle">Sign in to your account to continue</p>
            </div>

            <!-- Social Login Buttons -->
            <div class="social-login-section">
              <a-button
                block
                size="large"
                class="social-btn google-btn"
                @click="handleSocialLogin('google')"
              >
                <template #icon><GoogleOutlined /></template>
                Continue with Google
              </a-button>
              <a-button
                block
                size="large"
                class="social-btn facebook-btn"
                @click="handleSocialLogin('facebook')"
              >
                <template #icon><FacebookOutlined /></template>
                Continue with Facebook
              </a-button>
              <a-button
                block
                size="large"
                class="social-btn github-btn"
                @click="handleSocialLogin('github')"
              >
                <template #icon><GithubOutlined /></template>
                Continue with GitHub
              </a-button>
            </div>

            <div class="divider">
              <span class="divider-text">Or continue with email</span>
            </div>

            <!-- Login Form -->
            <a-form
              :model="form"
              :rules="rules"
              @finish="handleLogin"
              class="login-form"
            >
              <a-form-item name="email">
                <div class="modern-input-wrapper">
                  <MailOutlined class="input-icon" />
                  <a-input
                    v-model:value="form.email"
                    size="large"
                    placeholder="Email address"
                    class="modern-input"
                    :bordered="false"
                  />
                </div>
              </a-form-item>

              <a-form-item name="password">
                <div class="modern-input-wrapper">
                  <LockOutlined class="input-icon" />
                  <a-input-password
                    v-model:value="form.password"
                    size="large"
                    placeholder="Password"
                    class="modern-input"
                    :bordered="false"
                  />
                </div>
              </a-form-item>

              <div class="form-options">
                <a-checkbox v-model:checked="form.remember">
                  Remember me
                </a-checkbox>
                <a href="/forgot-password" class="forgot-link">Forgot password?</a>
              </div>

              <a-form-item>
                <PrimaryButton
                  html-type="submit"
                  size="large"
                  block
                  :loading="loading"
                  loading-text="Signing in..."
                  text="Sign In"
                />
              </a-form-item>
            </a-form>

            <div class="auth-footer">
              <p class="footer-text">
                Don't have an account?
                <Link href="/register" class="auth-link">Sign up</Link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FrontendLayout>
</template>

<style scoped>
.auth-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  position: relative;
  overflow: hidden;
  background: #ffffff;
}

/* Light Mode - White Noise Pattern */
.auth-container::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  background: #ffffff;
  background-image: 
    radial-gradient(circle at 1px 1px, rgba(139, 92, 246, 0.15) 1px, transparent 0),
    radial-gradient(circle at 1px 1px, rgba(59, 130, 246, 0.12) 1px, transparent 0),
    radial-gradient(circle at 1px 1px, rgba(236, 72, 153, 0.1) 1px, transparent 0);
  background-size: 20px 20px, 30px 30px, 25px 25px;
  background-position: 0 0, 10px 10px, 15px 5px;
  pointer-events: none;
}

/* Dark Mode - Black Background with Colored Noise Pattern */
[data-theme="dark"] .auth-container {
  background: #000000;
}

[data-theme="dark"] .auth-container::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  background: #000000;
  background-image: 
    radial-gradient(circle at 1px 1px, rgba(139, 92, 246, 0.2) 1px, transparent 0),
    radial-gradient(circle at 1px 1px, rgba(59, 130, 246, 0.18) 1px, transparent 0),
    radial-gradient(circle at 1px 1px, rgba(236, 72, 153, 0.15) 1px, transparent 0);
  background-size: 20px 20px, 30px 30px, 25px 25px;
  background-position: 0 0, 10px 10px, 15px 5px;
  pointer-events: none;
}

.auth-wrapper {
  max-width: 1200px;
  width: 100%;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  background: white;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  position: relative;
  z-index: 1;
}

[data-theme="dark"] .auth-wrapper {
  background: #1a1a1a;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Branding Section with Image */
.auth-branding {
  position: relative;
  overflow: hidden;
  background: #000;
}

.branding-image-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: 100%;
}

.branding-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

.branding-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(
    135deg,
    rgba(102, 126, 234, 0.85) 0%,
    rgba(118, 75, 162, 0.85) 100%
  );
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 50px;
}

.brand-logo-overlay {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  text-align: center;
}

.logo-icon-overlay {
  width: 100px;
  height: 100px;
  background: rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(20px);
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  font-weight: bold;
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.brand-title-overlay {
  font-size: 42px;
  font-weight: 800;
  margin: 0;
  color: white;
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  letter-spacing: -0.5px;
}

/* Form Section */
.auth-form-container {
  padding: 60px 50px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ffffff;
  transition: background-color 0.2s;
}

[data-theme="dark"] .auth-form-container {
  background: #0f0f0f;
  border-left: 1px solid rgba(255, 255, 255, 0.05);
}

.auth-form-wrapper {
  width: 100%;
  max-width: 420px;
}

.auth-form-header {
  text-align: center;
  margin-bottom: 32px;
}

.form-title {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px 0;
  color: var(--text-primary, #262626);
  transition: color 0.2s;
}

[data-theme="dark"] .form-title {
  color: rgba(255, 255, 255, 0.85);
}

.form-subtitle {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  margin: 0;
  transition: color 0.2s;
}

[data-theme="dark"] .form-subtitle {
  color: rgba(255, 255, 255, 0.65);
}

/* Social Login */
.social-login-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 24px;
}

.social-btn {
  height: 48px;
  border-radius: 8px;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s;
}

.google-btn {
  background: #ffffff !important;
  border: 1px solid #e0e0e0 !important;
  color: #262626 !important;
}

.google-btn:hover {
  background: #f5f5f5 !important;
  border-color: #d0d0d0 !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.facebook-btn {
  background: #1877f2 !important;
  border: none !important;
  color: white !important;
}

.facebook-btn:hover {
  background: #166fe5 !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(24, 119, 242, 0.3);
}

.github-btn {
  background: #24292e !important;
  border: none !important;
  color: white !important;
}

.github-btn:hover {
  background: #1a1e22 !important;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(36, 41, 46, 0.3);
}

[data-theme="dark"] .google-btn {
  background: #262626 !important;
  border-color: #434343 !important;
  color: rgba(255, 255, 255, 0.85) !important;
}

[data-theme="dark"] .google-btn:hover {
  background: #303030 !important;
  border-color: #595959 !important;
}

/* Divider */
.divider {
  position: relative;
  text-align: center;
  margin: 24px 0;
}

.divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: var(--border-color, #e0e0e0);
  transition: background-color 0.2s;
}

[data-theme="dark"] .divider::before {
  background: #434343;
}

.divider-text {
  position: relative;
  background: var(--bg-primary, #ffffff);
  padding: 0 16px;
  color: var(--text-tertiary, #8c8c8c);
  font-size: 13px;
  transition: background-color 0.2s, color 0.2s;
}

[data-theme="dark"] .divider-text {
  background: #1f1f1f;
  color: rgba(255, 255, 255, 0.45);
}

/* Form */
.login-form {
  margin-top: 24px;
}

/* Modern Input Design */
.modern-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: var(--input-bg, #ffffff);
  border: 1.5px solid #e0e0e0;
  border-radius: 12px;
  padding: 0 20px;
  transition: all 0.3s ease;
  min-height: 56px;
}

[data-theme="dark"] .modern-input-wrapper {
  background: #1a1a1a;
  border-color: #434343;
}

.modern-input-wrapper:hover {
  border-color: var(--color-primary, #1890ff);
  background: var(--input-bg-hover, #fafafa);
  box-shadow: 0 0 0 1px rgba(24, 144, 255, 0.1);
}

[data-theme="dark"] .modern-input-wrapper:hover {
  background: #1f1f1f;
  border-color: #595959;
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.05);
}

.modern-input-wrapper:focus-within {
  border-color: var(--color-primary, #1890ff);
  background: var(--input-bg-focus, #ffffff);
  box-shadow: 0 0 0 3px rgba(24, 144, 255, 0.1), 0 0 0 1px var(--color-primary, #1890ff);
}

[data-theme="dark"] .modern-input-wrapper:focus-within {
  background: #1f1f1f;
  border-color: #40a9ff;
  box-shadow: 0 0 0 3px rgba(64, 169, 255, 0.15), 0 0 0 1px #40a9ff;
}

.input-icon {
  font-size: 18px;
  color: var(--text-tertiary, #8c8c8c);
  margin-right: 12px;
  transition: color 0.3s;
  flex-shrink: 0;
}

[data-theme="dark"] .input-icon {
  color: rgba(255, 255, 255, 0.45);
}

.modern-input-wrapper:focus-within .input-icon {
  color: var(--color-primary, #1890ff);
}

[data-theme="dark"] .modern-input-wrapper:focus-within .input-icon {
  color: #40a9ff;
}

.modern-input {
  flex: 1;
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  padding: 0 !important;
  height: auto !important;
  font-size: 15px;
}

.modern-input :deep(.ant-input) {
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  padding: 12px 0 !important;
  height: auto !important;
  min-height: 24px !important;
  font-size: 15px;
  color: var(--text-primary, #262626);
  line-height: 1.5;
}

[data-theme="dark"] .modern-input :deep(.ant-input) {
  color: rgba(255, 255, 255, 0.85) !important;
}

.modern-input :deep(.ant-input::placeholder) {
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .modern-input :deep(.ant-input::placeholder) {
  color: rgba(255, 255, 255, 0.25) !important;
}

.modern-input :deep(.ant-input-password) {
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  padding: 0 !important;
}

.modern-input :deep(.ant-input-password .ant-input) {
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  padding: 12px 0 !important;
  height: auto !important;
  min-height: 24px !important;
  font-size: 15px;
  color: var(--text-primary, #262626);
  line-height: 1.5;
}

[data-theme="dark"] .modern-input :deep(.ant-input-password .ant-input) {
  color: rgba(255, 255, 255, 0.85) !important;
}

.modern-input :deep(.ant-input-password-icon) {
  color: var(--text-tertiary, #8c8c8c);
  font-size: 16px;
}

[data-theme="dark"] .modern-input :deep(.ant-input-password-icon) {
  color: rgba(255, 255, 255, 0.45) !important;
}

.modern-input :deep(.ant-input-password-icon:hover) {
  color: var(--color-primary, #1890ff);
}

[data-theme="dark"] .modern-input :deep(.ant-input-password-icon:hover) {
  color: #40a9ff !important;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.form-options :deep(.ant-checkbox-wrapper) {
  color: var(--text-primary, #262626);
  transition: color 0.2s;
}

[data-theme="dark"] .form-options :deep(.ant-checkbox-wrapper) {
  color: rgba(255, 255, 255, 0.85) !important;
}

.form-options :deep(.ant-checkbox-wrapper span) {
  color: var(--text-primary, #262626);
  transition: color 0.2s;
}

[data-theme="dark"] .form-options :deep(.ant-checkbox-wrapper span) {
  color: rgba(255, 255, 255, 0.85) !important;
}

.forgot-link {
  color: var(--color-primary, #1890ff);
  font-size: 14px;
  text-decoration: none;
  transition: color 0.2s;
}

[data-theme="dark"] .forgot-link {
  color: #40a9ff !important;
}

.forgot-link:hover {
  color: var(--color-primary-hover, #40a9ff);
}

[data-theme="dark"] .forgot-link:hover {
  color: #69c0ff !important;
}

.submit-btn {
  height: 48px;
  border-radius: 8px;
  font-weight: 600;
  font-size: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  transition: all 0.3s;
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
}

.auth-footer {
  text-align: center;
  margin-top: 24px;
}

.footer-text {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  margin: 0;
  transition: color 0.2s;
}

[data-theme="dark"] .footer-text {
  color: rgba(255, 255, 255, 0.65);
}

.auth-link {
  color: var(--color-primary, #1890ff);
  font-weight: 600;
  text-decoration: none;
  transition: color 0.2s;
}

.auth-link:hover {
  color: var(--color-primary-hover, #40a9ff);
}

/* Responsive */
@media (max-width: 968px) {
  .auth-wrapper {
    grid-template-columns: 1fr;
  }

  .auth-branding {
    display: none;
  }

  .auth-form-container {
    padding: 40px 30px;
  }
}

@media (max-width: 480px) {
  .auth-container {
    padding: 20px 10px;
  }

  .auth-form-container {
    padding: 30px 20px;
  }

  .form-title {
    font-size: 24px;
  }

  .social-btn {
    height: 44px;
    font-size: 14px;
  }

  .auth-input {
    height: 44px;
  }

  .submit-btn {
    height: 44px;
  }
}
</style>

