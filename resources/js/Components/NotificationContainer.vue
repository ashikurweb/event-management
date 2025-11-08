<template>
  <div class="fixed top-20 right-4 z-50 space-y-3 pointer-events-none">
    <TransitionGroup
      name="notification"
      tag="div"
      class="space-y-3"
    >
      <Notify
        v-for="notification in notifications"
        :key="notification.id"
        :type="notification.type"
        :title="notification.title"
        :message="notification.message"
        :duration="notification.duration"
        :persistent="notification.persistent"
        @close="removeNotification(notification.id)"
        class="pointer-events-auto"
      />
    </TransitionGroup>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Notify from './Notify.vue'
import { useNotifications } from '../Composables/useNotifications'

const page = usePage()
const { notifications, removeNotification, checkFlashMessages } = useNotifications()

// Check for flash messages on mount and page changes
onMounted(() => {
  checkFlashMessages()
})

watch(() => page.props.flash, (newFlash, oldFlash) => {
  // Only check if flash messages actually changed
  if (newFlash && (newFlash.success || newFlash.error || newFlash.info || newFlash.warning)) {
    checkFlashMessages()
  }
}, { deep: true, immediate: false })
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%) scale(0.95);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>

