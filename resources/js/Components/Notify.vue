<template>
  <div class="w-full max-w-[280px] pointer-events-auto">
    <Transition
      enter-active-class="transition-all duration-600 ease-out"
      enter-from-class="opacity-0 translate-x-8"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition-all duration-400 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 translate-x-8"
    >
      <div
        v-if="visible"
        class="relative overflow-hidden rounded-3xl backdrop-blur-2xl border-2 shadow-3xl w-full transition-all duration-500 group notification-card"
          :class="[
            type === 'success' 
              ? 'bg-gradient-to-br from-slate-900/95 via-emerald-900/90 to-slate-900/95 border-emerald-400/60 shadow-emerald-400/40 hover:shadow-emerald-400/70' 
              : type === 'error'
              ? 'bg-gradient-to-br from-slate-900/95 via-red-900/90 to-slate-900/95 border-red-400/60 shadow-red-400/40 hover:shadow-red-400/70'
              : 'bg-gradient-to-br from-slate-900/95 via-blue-900/90 to-slate-900/95 border-blue-400/60 shadow-blue-400/40 hover:shadow-blue-400/70'
          ]"
        >
          <!-- Animated mesh background -->
          <div class="absolute inset-0 opacity-20">
            <div 
              class="absolute inset-0 mesh-bg animate-mesh-flow"
              :class="[
                type === 'success' ? 'mesh-success' :
                type === 'error' ? 'mesh-error' :
                'mesh-processing'
              ]"
            ></div>
          </div>
          
          <!-- Multiple floating particles -->
          <div class="absolute inset-0 overflow-hidden">
            <div 
              v-for="i in 6" 
              :key="i"
              class="absolute w-2 h-2 rounded-full animate-float"
              :class="[
                type === 'success' ? 'bg-emerald-400/30' :
                type === 'error' ? 'bg-red-400/30' :
                'bg-blue-400/30'
              ]"
              :style="{
                left: Math.random() * 100 + '%',
                top: Math.random() * 100 + '%',
                animationDelay: (i * 0.5) + 's',
                animationDuration: (3 + Math.random() * 2) + 's'
              }"
            ></div>
          </div>

          <!-- Main glow effect -->
          <div 
            class="absolute -inset-2 rounded-3xl blur-xl opacity-30 animate-glow-pulse"
            :class="[
              type === 'success' ? 'bg-emerald-400' :
              type === 'error' ? 'bg-red-400' :
              'bg-blue-400'
            ]"
          ></div>

          <!-- Content -->
          <div class="relative z-20 flex items-center gap-3 py-3 px-4">
            <!-- Enhanced Icon Container -->
            <div class="flex-shrink-0 relative icon-container">
              <!-- Processing Animation -->
              <div
                v-if="type === 'processing'"
                class="relative w-14 h-14 flex items-center justify-center"
              >
                <!-- Triple spinning rings -->
                <div class="absolute w-14 h-14 border-2 border-blue-300/20 rounded-full"></div>
                <div class="absolute w-12 h-12 border-3 border-transparent border-t-blue-400 border-r-blue-300 rounded-full animate-spin-fast"></div>
                <div class="absolute w-8 h-8 border-2 border-transparent border-t-blue-500 border-l-blue-400 rounded-full animate-spin-reverse"></div>
                <!-- Pulsing core with particles -->
                <div class="relative w-4 h-4 flex items-center justify-center">
                  <div class="absolute w-4 h-4 bg-blue-500 rounded-full animate-ping"></div>
                  <div class="absolute w-2 h-2 bg-blue-400 rounded-full animate-pulse-fast"></div>
                </div>
              </div>
              
              <!-- Success Icon with Modern Spinner Animation -->
              <div
                v-else-if="type === 'success'"
                class="w-10 h-10 bg-gradient-to-br from-emerald-400 via-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-2xl success-icon relative overflow-hidden"
              >
                <div class="absolute inset-0 bg-white/20 rounded-xl animate-shimmer"></div>
                <!-- Modern Spinner Animation -->
                <div class="relative w-full h-full flex items-center justify-center">
                  <!-- Spinner rings (show first, then fade out) -->
                  <div class="absolute inset-0 flex items-center justify-center animate-spinner-to-check">
                    <!-- Outer rotating ring -->
                    <div class="absolute w-8 h-8 border-2 border-transparent border-t-white border-r-white/60 rounded-full animate-spin-fast"></div>
                    <!-- Middle rotating ring (reverse) -->
                    <div class="absolute w-6 h-6 border-2 border-transparent border-b-white/80 border-l-white/40 rounded-full animate-spin-reverse"></div>
                    <!-- Inner pulsing dot -->
                    <div class="absolute w-2 h-2 bg-white rounded-full animate-pulse-fast shadow-lg"></div>
                  </div>
                  <!-- Success check mark (fade in after spinner) -->
                  <svg class="w-5 h-5 text-white animate-check-appear relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path 
                      stroke-linecap="round" 
                      stroke-linejoin="round" 
                      stroke-width="3" 
                      d="M5 13l4 4L19 7"
                      class="check-draw-path"
                    ></path>
                  </svg>
                </div>
                <!-- Success particles -->
                <div class="absolute inset-0 success-particles">
                  <div v-for="i in 4" :key="i" class="particle" :style="{ animationDelay: (i * 0.2) + 's' }"></div>
                </div>
              </div>
              
              <!-- Error Icon -->
              <div
                v-else-if="type === 'error'"
                class="w-14 h-14 bg-gradient-to-br from-red-400 via-pink-500 to-red-600 rounded-3xl flex items-center justify-center shadow-2xl transform group-hover:rotate-12 group-hover:scale-110 transition-all duration-500 error-icon"
              >
                <div class="absolute inset-0 bg-white/20 rounded-3xl animate-shimmer"></div>
                <svg class="w-8 h-8 text-white animate-error-shake relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <!-- Error waves -->
                <div class="absolute inset-0 error-waves">
                  <div class="wave wave-1"></div>
                  <div class="wave wave-2"></div>
                  <div class="wave wave-3"></div>
                </div>
              </div>
            </div>

            <!-- Enhanced Text Content -->
            <div class="flex-1 min-w-0">
              <h4
                class="font-bold text-sm leading-tight mb-0.5 animate-text-reveal text-white drop-shadow-lg whitespace-nowrap"
              >
                {{ title }}
              </h4>
              <p
                v-if="message"
                class="text-xs leading-tight animate-text-reveal-delay text-white drop-shadow whitespace-nowrap overflow-hidden text-ellipsis"
              >
                {{ message }}
              </p>
            </div>
          </div>

          <!-- Dynamic Progress Bar -->
          <div
            v-if="type === 'processing'"
            class="absolute bottom-0 left-0 w-full h-2 overflow-hidden"
          >
            <div class="h-full bg-gradient-to-r from-transparent via-blue-400 to-transparent animate-progress-wave"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-blue-500 animate-progress-flow opacity-60"></div>
          </div>

          <!-- Border glow animation -->
          <div 
            class="absolute inset-0 rounded-3xl border-2 border-transparent animate-border-glow"
            :class="[
              type === 'success' ? 'border-glow-success' :
              type === 'error' ? 'border-glow-error' :
              'border-glow-processing'
            ]"
          ></div>
        </div>
      </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue'

const props = defineProps({
  type: {
    type: String,
    default: 'success',
    validator: (value) => ['success', 'error', 'processing'].includes(value)
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: ''
  },
  duration: {
    type: Number,
    default: 5000
  },
  persistent: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close'])

const visible = ref(false)
let timeoutId = null

const show = () => {
  visible.value = true
  
  if (props.type !== 'processing' && !props.persistent && props.duration > 0) {
    clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      close()
    }, props.duration)
  }
}

const close = () => {
  visible.value = false
  clearTimeout(timeoutId)
  
  setTimeout(() => {
    emit('close')
  }, 500)
}

watch(() => props.type, (newType, oldType) => {
  if (oldType === 'processing' && newType !== 'processing') {
    clearTimeout(timeoutId)
    if (!props.persistent && props.duration > 0) {
      timeoutId = setTimeout(() => {
        close()
      }, props.duration)
    }
  }
})

onMounted(() => {
  nextTick(() => {
    show()
  })
})

defineExpose({
  show,
  close
})
</script>

<style scoped>
/* Enhanced Custom Animations */
/* Entrance animations with physics */
@keyframes draw-check {
  0% { 
    stroke-dasharray: 0 30; 
    transform: scale(0.8);
  }
  70% { 
    stroke-dasharray: 30 0; 
    transform: scale(1.1);
  }
  100% { 
    stroke-dasharray: 30 0; 
    transform: scale(1);
  }
}

@keyframes error-shake {
  0%, 100% { transform: translateX(0) rotate(0deg); }
  10% { transform: translateX(-3px) rotate(-2deg); }
  20% { transform: translateX(3px) rotate(2deg); }
  30% { transform: translateX(-3px) rotate(-1deg); }
  40% { transform: translateX(3px) rotate(1deg); }
  50% { transform: translateX(-2px) rotate(-0.5deg); }
  60% { transform: translateX(2px) rotate(0.5deg); }
  70% { transform: translateX(-1px) rotate(0deg); }
  80% { transform: translateX(1px) rotate(0deg); }
  90% { transform: translateX(0) rotate(0deg); }
}

/* Floating particles */
@keyframes float {
  0%, 100% { 
    transform: translateY(0) rotate(0deg); 
    opacity: 0.3;
  }
  25% { 
    transform: translateY(-20px) rotate(90deg); 
    opacity: 0.6;
  }
  50% { 
    transform: translateY(-10px) rotate(180deg); 
    opacity: 0.8;
  }
  75% { 
    transform: translateY(-30px) rotate(270deg); 
    opacity: 0.4;
  }
}

/* Text reveal animations */
@keyframes text-reveal {
  0% { 
    opacity: 0; 
    transform: translateY(-20px) rotateX(-90deg); 
  }
  100% { 
    opacity: 1; 
    transform: translateY(0) rotateX(0deg); 
  }
}

@keyframes text-reveal-delay {
  0%, 30% { 
    opacity: 0; 
    transform: translateY(-15px) rotateX(-90deg); 
  }
  100% { 
    opacity: 1; 
    transform: translateY(0) rotateX(0deg); 
  }
}

/* Letter bounce effect */
@keyframes letter-bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}

/* Enhanced spinning animations */
@keyframes spin-fast {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes spin-reverse {
  from { transform: rotate(360deg); }
  to { transform: rotate(0deg); }
}

@keyframes pulse-fast {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.2); opacity: 0.7; }
}

/* Progress animations */
@keyframes progress-wave {
  0% { transform: translateX(-100%) skewX(-15deg); }
  100% { transform: translateX(200%) skewX(-15deg); }
}

@keyframes progress-flow {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

/* Mesh background */
@keyframes mesh-flow {
  0%, 100% { 
    background-position: 0% 0%; 
    filter: hue-rotate(0deg);
  }
  25% { 
    background-position: 100% 0%; 
    filter: hue-rotate(90deg);
  }
  50% { 
    background-position: 100% 100%; 
    filter: hue-rotate(180deg);
  }
  75% { 
    background-position: 0% 100%; 
    filter: hue-rotate(270deg);
  }
}

/* Glow effects */
@keyframes glow-pulse {
  0%, 100% { 
    opacity: 0.2; 
    transform: scale(1); 
  }
  50% { 
    opacity: 0.4; 
    transform: scale(1.05); 
  }
}

/* Shimmer effect */
@keyframes shimmer {
  0% { transform: translateX(-100%) rotate(45deg); }
  100% { transform: translateX(200%) rotate(45deg); }
}

/* Border glow */
@keyframes border-glow {
  0%, 100% { 
    box-shadow: 0 0 20px currentColor; 
    opacity: 0.3;
  }
  50% { 
    box-shadow: 0 0 40px currentColor; 
    opacity: 0.6;
  }
}

/* Success particles */
@keyframes success-particle {
  0% { 
    transform: scale(0) rotate(0deg); 
    opacity: 0; 
  }
  20% { 
    opacity: 1; 
  }
  100% { 
    transform: scale(1) rotate(360deg) translateY(-30px); 
    opacity: 0; 
  }
}

/* Error waves */
@keyframes error-wave {
  0% { 
    transform: scale(0); 
    opacity: 0.8; 
  }
  100% { 
    transform: scale(2); 
    opacity: 0; 
  }
}

/* Apply animations */
.animate-draw-check {
  animation: draw-check 1s ease-out 0.3s both;
}

.check-path {
  stroke-dasharray: 30;
  stroke-dashoffset: 30;
  animation: draw-check-path 1s ease-out 0.3s both;
}

@keyframes draw-check-path {
  0% { 
    stroke-dashoffset: 30;
  }
  100% { 
    stroke-dashoffset: 0;
  }
}

.animate-error-shake {
  animation: error-shake 0.8s ease-out;
}

.animate-text-reveal {
  animation: text-reveal 0.8s ease-out;
}

.animate-text-reveal-delay {
  animation: text-reveal-delay 1s ease-out;
}

.animate-letter-bounce {
  animation: letter-bounce 2s ease-in-out infinite;
}

.animate-spin-fast {
  animation: spin-fast 1s linear infinite;
}

.animate-spin-reverse {
  animation: spin-reverse 1.5s linear infinite;
}

.animate-pulse-fast {
  animation: pulse-fast 1s ease-in-out infinite;
}

/* Spinner to check animation */
@keyframes spinner-to-check {
  0%, 60% {
    opacity: 1;
    transform: scale(1);
  }
  70%, 100% {
    opacity: 0;
    transform: scale(0.8);
  }
}

@keyframes check-appear {
  0%, 60% {
    opacity: 0;
    transform: scale(0.5) rotate(-45deg);
  }
  70%, 100% {
    opacity: 1;
    transform: scale(1) rotate(0deg);
  }
}

@keyframes check-draw {
  0% {
    stroke-dasharray: 0 20;
    stroke-dashoffset: 0;
  }
  100% {
    stroke-dasharray: 20 0;
    stroke-dashoffset: 0;
  }
}

.animate-spinner-to-check {
  animation: spinner-to-check 1.5s ease-out forwards;
}

.animate-check-appear {
  animation: check-appear 1.5s ease-out forwards;
}

.check-draw-path {
  stroke-dasharray: 20;
  stroke-dashoffset: 20;
  animation: check-draw 0.6s ease-out 0.9s forwards;
}

.animate-float {
  animation: float linear infinite;
}

.animate-mesh-flow {
  animation: mesh-flow 8s ease-in-out infinite;
}

.animate-glow-pulse {
  animation: glow-pulse 2s ease-in-out infinite;
}

.animate-shimmer {
  animation: shimmer 2s ease-in-out infinite;
}

.animate-progress-wave {
  animation: progress-wave 1.5s ease-in-out infinite;
}

.animate-progress-flow {
  animation: progress-flow 2s linear infinite;
}

/* Mesh backgrounds */
.mesh-bg {
  background-image: 
    radial-gradient(circle at 25% 25%, currentColor 2px, transparent 2px),
    radial-gradient(circle at 75% 75%, currentColor 2px, transparent 2px);
  background-size: 50px 50px;
  background-position: 0 0, 25px 25px;
}

.mesh-success { color: #10b981; }
.mesh-error { color: #ef4444; }
.mesh-processing { color: #3b82f6; }

/* Enhanced 3D shadow */
.shadow-3xl {
  box-shadow: 
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(255, 255, 255, 0.05),
    inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

/* Success particles */
.success-particles .particle {
  position: absolute;
  width: 6px;
  height: 6px;
  background: #fbbf24;
  border-radius: 50%;
  animation: success-particle 2s ease-out infinite;
}

.success-particles .particle:nth-child(1) { top: 10%; left: 20%; }
.success-particles .particle:nth-child(2) { top: 20%; right: 15%; animation-delay: 0.2s; }
.success-particles .particle:nth-child(3) { bottom: 15%; left: 15%; animation-delay: 0.4s; }
.success-particles .particle:nth-child(4) { bottom: 10%; right: 20%; animation-delay: 0.6s; }

/* Error waves */
.error-waves .wave {
  position: absolute;
  inset: 0;
  border: 2px solid #f87171;
  border-radius: 50%;
  animation: error-wave 1.5s ease-out infinite;
}

.error-waves .wave-2 { animation-delay: 0.3s; }
.error-waves .wave-3 { animation-delay: 0.6s; }

/* Border glow effects */
.border-glow-success { color: #10b981; }
.border-glow-error { color: #ef4444; }
.border-glow-processing { color: #3b82f6; }

.animate-border-glow {
  animation: border-glow 2s ease-in-out infinite;
}

/* Enhanced backdrop blur */
.backdrop-blur-2xl {
  backdrop-filter: blur(40px) saturate(200%) brightness(1.1);
  -webkit-backdrop-filter: blur(40px) saturate(200%) brightness(1.1);
}

/* Notification card hover effects - removed transform */
.notification-card:hover {
  filter: brightness(1.05);
}

/* Icon container effects */
.icon-container:hover {
  filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
}

.success-icon:hover {
  box-shadow: 0 0 30px rgba(16, 185, 129, 0.6);
}

.error-icon:hover {
  box-shadow: 0 0 30px rgba(239, 68, 68, 0.6);
}

/* Advanced border effects */
.border-3 {
  border-width: 3px;
}
</style>

