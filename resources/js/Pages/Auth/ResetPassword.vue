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

        <!-- Right Side - Reset Password Form -->
        <div class="auth-form-container">
          <div class="auth-form-wrapper">
            <div class="auth-form-header">
              <h2 class="form-title">Reset Password</h2>
              <p class="form-subtitle">Enter the OTP sent to your email and your new password</p>
            </div>

            <!-- OTP Timer -->
            <div v-if="showTimer && remainingTime > 0" class="otp-timer">
              <ClockCircleOutlined />
              <span>OTP expires in: {{ formatTime(remainingTime) }}</span>
            </div>

            <div v-else-if="showTimer && remainingTime <= 0" class="otp-expired">
              <ExclamationCircleOutlined />
              <span>OTP has expired. Please request a new one.</span>
            </div>

            <!-- Reset Password Form -->
            <a-form
              :model="form"
              :rules="rules"
              @finish="handleSubmit"
              class="reset-password-form"
            >
              <a-form-item name="email">
                <a-input
                  v-model:value="form.email"
                  size="large"
                  placeholder="Email address"
                  :disabled="!!props.email"
                >
                  <template #prefix>
                    <MailOutlined />
                  </template>
                </a-input>
              </a-form-item>

              <a-form-item name="otp">
                <a-input
                  v-model:value="form.otp"
                  size="large"
                  placeholder="Enter 6-digit OTP"
                  maxlength="6"
                  @input="handleOtpInput"
                >
                  <template #prefix>
                    <SafetyOutlined />
                  </template>
                </a-input>
                <div class="otp-hint">
                  <a-button 
                    type="link" 
                    size="small" 
                    @click="resendOtp"
                    :loading="resending"
                    :disabled="remainingTime > 0"
                    class="resend-link"
                  >
                    Resend OTP
                  </a-button>
                </div>
              </a-form-item>

              <a-form-item name="password">
                <a-input-password
                  v-model:value="form.password"
                  size="large"
                  placeholder="New password"
                >
                  <template #prefix>
                    <LockOutlined />
                  </template>
                </a-input-password>
              </a-form-item>

              <a-form-item name="password_confirmation">
                <a-input-password
                  v-model:value="form.password_confirmation"
                  size="large"
                  placeholder="Confirm new password"
                >
                  <template #prefix>
                    <LockOutlined />
                  </template>
                </a-input-password>
              </a-form-item>

              <a-form-item>
                <PrimaryButton
                  html-type="submit"
                  size="large"
                  block
                  :loading="loading"
                  loading-text="Resetting password..."
                  text="Reset Password"
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
import { ref, reactive, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import FrontendLayout from '../../Layouts/FrontendLayout.vue';
import PrimaryButton from '../../Components/PrimaryButton.vue';
import {
  MailOutlined,
  LockOutlined,
  SafetyOutlined,
  ClockCircleOutlined,
  ExclamationCircleOutlined,
} from '@ant-design/icons-vue';
import axios from 'axios';

const props = defineProps({
  email: {
    type: String,
    default: '',
  },
});

const loading = ref(false);
const resending = ref(false);
const remainingTime = ref(0);
const showTimer = ref(false);
let timerInterval = null;

const form = reactive({
  email: props.email || '',
  otp: '',
  password: '',
  password_confirmation: '',
});

const rules = {
  email: [
    { required: true, message: 'Please enter your email', trigger: 'blur' },
    { type: 'email', message: 'Please enter a valid email', trigger: 'blur' },
  ],
  otp: [
    { required: true, message: 'Please enter the OTP', trigger: 'blur' },
    { pattern: /^[0-9]{6}$/, message: 'OTP must be 6 digits', trigger: 'blur' },
  ],
  password: [
    { required: true, message: 'Please enter your new password', trigger: 'blur' },
    { min: 8, message: 'Password must be at least 8 characters', trigger: 'blur' },
  ],
  password_confirmation: [
    { required: true, message: 'Please confirm your password', trigger: 'blur' },
    {
      validator: (rule, value) => {
        if (value !== form.password) {
          return Promise.reject('Password confirmation does not match');
        }
        return Promise.resolve();
      },
      trigger: 'blur',
    },
  ],
};

const handleOtpInput = (e) => {
  // Only allow numbers
  form.otp = e.target.value.replace(/\D/g, '').slice(0, 6);
};

const formatTime = (seconds) => {
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const fetchRemainingTime = async () => {
  if (!form.email) return;
  
  try {
    const response = await axios.get('/api/otp/remaining-time', {
      params: { email: form.email },
    });
    
    if (response.data.remaining !== null) {
      remainingTime.value = response.data.remaining;
      showTimer.value = true;
      
      if (remainingTime.value > 0 && !timerInterval) {
        startTimer();
      }
    } else {
      showTimer.value = false;
    }
  } catch (error) {
    console.error('Failed to fetch remaining time:', error);
  }
};

const startTimer = () => {
  if (timerInterval) clearInterval(timerInterval);
  
  timerInterval = setInterval(() => {
    if (remainingTime.value > 0) {
      remainingTime.value--;
    } else {
      clearInterval(timerInterval);
      timerInterval = null;
      showTimer.value = true;
    }
  }, 1000);
};

const resendOtp = async () => {
  if (!form.email) {
    return;
  }

  resending.value = true;
  
  try {
    await router.post('/forgot-password', { email: form.email }, {
      onSuccess: () => {
        remainingTime.value = 150; // 2.5 minutes = 150 seconds
        showTimer.value = true;
        startTimer();
      },
      onFinish: () => {
        resending.value = false;
      },
    });
  } catch (error) {
    resending.value = false;
  }
};

const handleSubmit = (values) => {
  loading.value = true;
  router.post('/reset-password', values, {
    onFinish: () => {
      loading.value = false;
    },
  });
};

onMounted(() => {
  if (props.email) {
    fetchRemainingTime();
  }
});

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval);
  }
});

watch(() => form.email, () => {
  if (form.email) {
    fetchRemainingTime();
  }
});
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

.reset-password-form {
  margin-top: 24px;
}

.otp-timer {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: #e6f7ff;
  border: 1px solid #91d5ff;
  border-radius: 8px;
  margin-bottom: 24px;
  color: #1890ff;
  font-size: 14px;
  font-weight: 500;
}

[data-theme="dark"] .otp-timer {
  background: rgba(24, 144, 255, 0.1);
  border-color: rgba(24, 144, 255, 0.3);
  color: #40a9ff;
}

.otp-expired {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  background: #fff2e8;
  border: 1px solid #ffbb96;
  border-radius: 8px;
  margin-bottom: 24px;
  color: #fa8c16;
  font-size: 14px;
  font-weight: 500;
}

[data-theme="dark"] .otp-expired {
  background: rgba(250, 140, 22, 0.1);
  border-color: rgba(250, 140, 22, 0.3);
  color: #ffa940;
}

.otp-hint {
  margin-top: 8px;
  text-align: right;
}

.resend-link {
  padding: 0 !important;
  height: auto !important;
  font-size: 13px !important;
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

