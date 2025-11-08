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

        <!-- Right Side - Forgot Password Form -->
        <div class="auth-form-container">
          <div class="auth-form-wrapper">
            <div class="auth-form-header">
              <h2 class="form-title">Forgot Password?</h2>
              <p class="form-subtitle">Enter your email address and we'll send you an OTP to reset your password</p>
            </div>

            <!-- Forgot Password Form -->
            <a-form
              :model="form"
              :rules="rules"
              @finish="handleSubmit"
              class="forgot-password-form"
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

              <a-form-item>
                <PrimaryButton
                  html-type="submit"
                  size="large"
                  block
                  :loading="loading"
                  loading-text="Sending OTP..."
                  text="Send OTP"
                />
              </a-form-item>
            </a-form>

            <div class="auth-footer">
              <p class="footer-text">
                Remember your password?
                <Link href="/login" class="auth-link">Sign in</Link>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </FrontendLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import FrontendLayout from '../../Layouts/FrontendLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { MailOutlined } from '@ant-design/icons-vue';

const loading = ref(false);

const form = reactive({
  email: '',
});

const rules = {
  email: [
    { required: true, message: 'Please enter your email', trigger: 'blur' },
    { type: 'email', message: 'Please enter a valid email', trigger: 'blur' },
  ],
};

const handleSubmit = (values) => {
  loading.value = true;
  router.post('/forgot-password', values, {
    onFinish: () => {
      loading.value = false;
    },
    onSuccess: () => {
      // Redirect to reset password page with email
      router.visit(`/reset-password?email=${encodeURIComponent(values.email)}`);
    },
  });
};
</script>

<style scoped>
/* Reuse styles from Login.vue */
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

.forgot-password-form {
  margin-top: 24px;
}

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
}
</style>

