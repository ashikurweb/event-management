<template>
  <FrontendLayout>
    <div class="auth-container">
      <div class="auth-wrapper">
        <!-- Left Side - Branding with Image -->
        <div class="auth-branding">
          <div class="branding-image-wrapper">
            <img 
              src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=800&h=1200&fit=crop&q=80" 
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

        <!-- Right Side - Verification Form -->
        <div class="auth-form-container">
          <div class="auth-form-wrapper">
            <div class="auth-form-header">
              <div class="verification-icon-wrapper">
                <MailOutlined class="verification-icon" />
              </div>
              <h2 class="form-title">Verify Your Email</h2>
              <p class="form-subtitle">
                We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.
              </p>
            </div>

            <div class="verification-info">
              <p class="info-text">
                If you didn't receive the email, we can send you another one.
              </p>
            </div>

            <form @submit.prevent="resendVerification" class="verification-form">
              <PrimaryButton
                html-type="submit"
                size="large"
                block
                :loading="resending"
                loading-text="Sending..."
                text="Resend Verification Email"
              />
            </form>

            <div class="auth-footer">
              <p class="footer-text">
                Already verified?
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
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import FrontendLayout from '../../Layouts/FrontendLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import { MailOutlined } from '@ant-design/icons-vue';

const resending = ref(false);

const resendVerification = () => {
  resending.value = true;
  router.post('/email/verification-notification', {}, {
    onFinish: () => {
      resending.value = false;
    },
  });
};
</script>

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

/* Dark Mode */
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

/* Branding Section */
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

.verification-icon-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 24px;
}

.verification-icon {
  font-size: 64px;
  color: #1890ff;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 1;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.8;
  }
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
  line-height: 1.6;
}

[data-theme="dark"] .form-subtitle {
  color: rgba(255, 255, 255, 0.65);
}

.verification-info {
  margin-bottom: 24px;
  padding: 16px;
  background: #f0f7ff;
  border-radius: 12px;
  border: 1px solid #b3d9ff;
}

[data-theme="dark"] .verification-info {
  background: rgba(24, 144, 255, 0.1);
  border-color: rgba(24, 144, 255, 0.3);
}

.info-text {
  font-size: 14px;
  color: var(--text-secondary, #595959);
  margin: 0;
  text-align: center;
}

[data-theme="dark"] .info-text {
  color: rgba(255, 255, 255, 0.65);
}

.verification-form {
  margin-bottom: 24px;
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

