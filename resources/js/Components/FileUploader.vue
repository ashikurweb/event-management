<template>
  <div class="file-uploader">
    <!-- Drop Zone -->
    <div
      ref="dropZoneRef"
      class="file-drop-zone"
      :class="{
        'is-dragging': isDragging,
        'has-files': files.length > 0,
        'is-disabled': disabled
      }"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
      @click="triggerFileInput"
    >
      <input
        ref="fileInputRef"
        type="file"
        :multiple="multiple"
        :accept="acceptString"
        :disabled="disabled"
        class="file-input-hidden"
        @change="handleFileSelect"
      />

      <div v-if="files.length === 0" class="drop-zone-content">
        <div class="drop-zone-icon">
          <CloudUploadOutlined />
        </div>
        <div class="drop-zone-text">
          <p class="drop-zone-title">
            {{ dragText || 'Drag & drop files here' }}
          </p>
          <p class="drop-zone-subtitle">
            {{ subtitle || `or click to browse` }}
          </p>
          <p class="drop-zone-formats" v-if="showFormats">
            Supported: {{ formatText }}
          </p>
        </div>
      </div>

      <div v-else class="files-preview">
        <div
          v-for="(file, index) in files"
          :key="file.id || index"
          class="file-item"
          :class="getFileTypeClass(file.type)"
        >
          <div class="file-preview">
            <!-- Image Preview -->
            <img
              v-if="isImage(file.type)"
              :src="file.preview"
              :alt="file.name"
              class="file-preview-image"
            />
            <!-- Video Preview -->
            <div v-else-if="isVideo(file.type)" class="file-preview-video">
              <PlayCircleOutlined />
            </div>
            <!-- Audio Preview -->
            <div v-else-if="isAudio(file.type)" class="file-preview-audio">
              <SoundOutlined />
            </div>
            <!-- Document Preview -->
            <div v-else class="file-preview-document">
              <FileTextOutlined v-if="isDocument(file.type)" />
              <FilePdfOutlined v-else-if="file.type === 'application/pdf'" />
              <FileExcelOutlined v-else-if="isExcel(file.type)" />
              <FileWordOutlined v-else-if="isWord(file.type)" />
              <FileOutlined v-else />
            </div>
          </div>

          <div class="file-info">
            <div class="file-name" :title="file.name">
              {{ truncateFileName(file.name) }}
            </div>
            <div class="file-meta">
              <span class="file-size">{{ formatFileSize(file.size) }}</span>
              <span class="file-type">{{ getFileExtension(file.name) }}</span>
            </div>
            <div v-if="file.progress !== undefined" class="file-progress">
              <a-progress
                :percent="file.progress"
                :status="file.progress === 100 ? 'success' : 'active'"
                :show-info="false"
                size="small"
              />
            </div>
          </div>

          <div class="file-actions">
            <a-button
              type="text"
              size="small"
              :icon="h(EyeOutlined)"
              @click.stop="previewFile(file)"
              v-if="canPreview(file.type)"
            />
            <a-button
              type="text"
              size="small"
              danger
              :icon="h(DeleteOutlined)"
              @click.stop="removeFile(index)"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- File List (Alternative View) -->
    <div v-if="showList && files.length > 0" class="file-list">
      <div
        v-for="(file, index) in files"
        :key="file.id || index"
        class="file-list-item"
      >
        <div class="file-list-icon">
          <FileOutlined v-if="!isImage(file.type) && !isVideo(file.type) && !isAudio(file.type)" />
          <FileImageOutlined v-else-if="isImage(file.type)" />
          <VideoCameraOutlined v-else-if="isVideo(file.type)" />
          <SoundOutlined v-else-if="isAudio(file.type)" />
        </div>
        <div class="file-list-info">
          <div class="file-list-name">{{ file.name }}</div>
          <div class="file-list-size">{{ formatFileSize(file.size) }}</div>
        </div>
        <a-button
          type="text"
          size="small"
          danger
          :icon="h(DeleteOutlined)"
          @click="removeFile(index)"
        />
      </div>
    </div>

    <!-- Preview Modal -->
    <a-modal
      v-model:open="previewVisible"
      :title="previewFileData?.name"
      :footer="null"
      :width="previewWidth"
      centered
    >
      <div class="file-preview-modal">
        <img
          v-if="previewFileData && isImage(previewFileData.type)"
          :src="previewFileData.preview"
          :alt="previewFileData.name"
          class="preview-image"
        />
        <video
          v-else-if="previewFileData && isVideo(previewFileData.type)"
          :src="previewFileData.preview"
          controls
          class="preview-video"
        />
        <audio
          v-else-if="previewFileData && isAudio(previewFileData.type)"
          :src="previewFileData.preview"
          controls
          class="preview-audio"
        />
        <div v-else class="preview-unsupported">
          <FileOutlined />
          <p>Preview not available for this file type</p>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, computed, watch, h } from 'vue';
import {
  CloudUploadOutlined,
  DeleteOutlined,
  EyeOutlined,
  FileOutlined,
  FileTextOutlined,
  FilePdfOutlined,
  FileExcelOutlined,
  FileWordOutlined,
  FileImageOutlined,
  VideoCameraOutlined,
  SoundOutlined,
  PlayCircleOutlined,
} from '@ant-design/icons-vue';

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  accept: {
    type: Array,
    default: () => [
      'image/*',
      'video/*',
      'audio/*',
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ],
  },
  multiple: {
    type: Boolean,
    default: true,
  },
  maxSize: {
    type: Number,
    default: 50 * 1024 * 1024, // 50MB
  },
  maxFiles: {
    type: Number,
    default: null,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  dragText: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  showFormats: {
    type: Boolean,
    default: true,
  },
  showList: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'change', 'error']);

const dropZoneRef = ref(null);
const fileInputRef = ref(null);
const isDragging = ref(false);
const files = ref([]);
const previewVisible = ref(false);
const previewFileData = ref(null);

// File type checks
const isImage = (type) => type?.startsWith('image/');
const isVideo = (type) => type?.startsWith('video/');
const isAudio = (type) => type?.startsWith('audio/');
const isDocument = (type) =>
  type?.includes('document') ||
  type === 'application/pdf' ||
  type?.includes('text/');
const isExcel = (type) =>
  type?.includes('spreadsheet') ||
  type === 'application/vnd.ms-excel' ||
  type === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
const isWord = (type) =>
  type?.includes('wordprocessing') ||
  type === 'application/msword' ||
  type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';

const canPreview = (type) => isImage(type) || isVideo(type) || isAudio(type);

const getFileTypeClass = (type) => {
  if (isImage(type)) return 'file-type-image';
  if (isVideo(type)) return 'file-type-video';
  if (isAudio(type)) return 'file-type-audio';
  return 'file-type-document';
};

const getFileExtension = (filename) => {
  return filename.split('.').pop()?.toUpperCase() || 'FILE';
};

const truncateFileName = (name, maxLength = 25) => {
  if (name.length <= maxLength) return name;
  return name.substring(0, maxLength - 3) + '...';
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
};

const acceptString = computed(() => {
  return props.accept.join(',');
});

const formatText = computed(() => {
  const formats = [];
  if (props.accept.some((a) => a.includes('image'))) formats.push('Images');
  if (props.accept.some((a) => a.includes('video'))) formats.push('Videos');
  if (props.accept.some((a) => a.includes('audio'))) formats.push('Audio');
  if (props.accept.some((a) => a.includes('pdf'))) formats.push('PDF');
  if (props.accept.some((a) => a.includes('word') || a.includes('document')))
    formats.push('Word');
  if (props.accept.some((a) => a.includes('excel') || a.includes('spreadsheet')))
    formats.push('Excel');
  return formats.join(', ');
});

const previewWidth = computed(() => {
  if (!previewFileData.value) return 800;
  if (isImage(previewFileData.value.type)) return 800;
  if (isVideo(previewFileData.value.type)) return 900;
  return 600;
});

// Watch for external changes
watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal && Array.isArray(newVal)) {
      files.value = [...newVal];
    }
  },
  { immediate: true }
);

// Watch files and emit changes
watch(
  files,
  (newFiles) => {
    emit('update:modelValue', newFiles);
    emit('change', newFiles);
  },
  { deep: true }
);

const validateFile = (file) => {
  // Check file size
  if (file.size > props.maxSize) {
    emit('error', {
      type: 'size',
      message: `File "${file.name}" exceeds maximum size of ${formatFileSize(props.maxSize)}`,
      file,
    });
    return false;
  }

  // Check file type
  const accepted = props.accept.some((acceptType) => {
    if (acceptType.endsWith('/*')) {
      const baseType = acceptType.split('/')[0];
      return file.type.startsWith(baseType + '/');
    }
    return file.type === acceptType || acceptType === '*/*';
  });

  if (!accepted) {
    emit('error', {
      type: 'type',
      message: `File type "${file.type}" is not allowed`,
      file,
    });
    return false;
  }

  return true;
};

const createFileObject = (file) => {
  const fileObj = {
    id: Date.now() + Math.random(),
    name: file.name,
    size: file.size,
    type: file.type,
    file: file, // Original file object
    progress: 0,
  };

  // Create preview for images
  if (isImage(file.type)) {
    const reader = new FileReader();
    reader.onload = (e) => {
      fileObj.preview = e.target.result;
    };
    reader.readAsDataURL(file);
  } else if (isVideo(file.type) || isAudio(file.type)) {
    fileObj.preview = URL.createObjectURL(file);
  }

  return fileObj;
};

const addFiles = (fileList) => {
  const newFiles = Array.from(fileList)
    .filter((file) => validateFile(file))
    .map((file) => createFileObject(file));

  // Check max files limit
  if (props.maxFiles && files.value.length + newFiles.length > props.maxFiles) {
    emit('error', {
      type: 'limit',
      message: `Maximum ${props.maxFiles} files allowed`,
    });
    return;
  }

  if (props.multiple) {
    files.value.push(...newFiles);
  } else {
    files.value = newFiles.slice(0, 1);
  }
};

const handleFileSelect = (event) => {
  const fileList = event.target.files;
  if (fileList.length > 0) {
    addFiles(fileList);
  }
  // Reset input
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const handleDragOver = (event) => {
  if (props.disabled) return;
  event.preventDefault();
  isDragging.value = true;
};

const handleDragLeave = (event) => {
  if (props.disabled) return;
  event.preventDefault();
  isDragging.value = false;
};

const handleDrop = (event) => {
  if (props.disabled) return;
  event.preventDefault();
  isDragging.value = false;

  const fileList = event.dataTransfer.files;
  if (fileList.length > 0) {
    addFiles(fileList);
  }
};

const triggerFileInput = () => {
  if (props.disabled) return;
  if (fileInputRef.value) {
    fileInputRef.value.click();
  }
};

const removeFile = (index) => {
  const file = files.value[index];
  if (file.preview && (file.preview.startsWith('blob:') || file.preview.startsWith('data:'))) {
    // Clean up object URLs
    if (file.preview.startsWith('blob:')) {
      URL.revokeObjectURL(file.preview);
    }
  }
  files.value.splice(index, 1);
};

const previewFile = (file) => {
  previewFileData.value = file;
  previewVisible.value = true;
};

// Cleanup on unmount
import { onUnmounted } from 'vue';
onUnmounted(() => {
  files.value.forEach((file) => {
    if (file.preview && file.preview.startsWith('blob:')) {
      URL.revokeObjectURL(file.preview);
    }
  });
});
</script>

<style scoped>
.file-uploader {
  width: 100%;
}

.file-drop-zone {
  position: relative;
  border: 2px dashed var(--border-color, #d9d9d9);
  border-radius: 8px;
  padding: 32px;
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: var(--bg-secondary, #fafafa);
  min-height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.file-drop-zone:hover:not(.is-disabled) {
  border-color: var(--color-primary, #1890ff);
  background: var(--bg-hover, #f0f7ff);
}

.file-drop-zone.is-dragging {
  border-color: var(--color-primary, #1890ff);
  background: var(--bg-hover, #e6f7ff);
  transform: scale(1.02);
}

.file-drop-zone.is-disabled {
  cursor: not-allowed;
  opacity: 0.6;
  background: var(--bg-disabled, #f5f5f5);
}

[data-theme="dark"] .file-drop-zone {
  background: var(--bg-secondary, #1f1f1f);
  border-color: var(--border-color, #434343);
}

[data-theme="dark"] .file-drop-zone:hover:not(.is-disabled) {
  background: rgba(24, 144, 255, 0.1);
  border-color: var(--color-primary, #40a9ff);
}

[data-theme="dark"] .file-drop-zone.is-dragging {
  background: rgba(24, 144, 255, 0.15);
  border-color: var(--color-primary, #40a9ff);
}

.file-input-hidden {
  position: absolute;
  width: 0;
  height: 0;
  opacity: 0;
  pointer-events: none;
}

.drop-zone-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.drop-zone-icon {
  font-size: 64px;
  color: var(--text-tertiary, #8c8c8c);
  transition: color 0.3s ease;
}

.file-drop-zone:hover:not(.is-disabled) .drop-zone-icon {
  color: var(--color-primary, #1890ff);
}

[data-theme="dark"] .drop-zone-icon {
  color: rgba(255, 255, 255, 0.45);
}

.drop-zone-text {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.drop-zone-title {
  font-size: 16px;
  font-weight: 500;
  color: var(--text-primary, #262626);
  margin: 0;
}

[data-theme="dark"] .drop-zone-title {
  color: rgba(255, 255, 255, 0.85);
}

.drop-zone-subtitle {
  font-size: 14px;
  color: var(--text-secondary, #8c8c8c);
  margin: 0;
}

[data-theme="dark"] .drop-zone-subtitle {
  color: rgba(255, 255, 255, 0.65);
}

.drop-zone-formats {
  font-size: 12px;
  color: var(--text-tertiary, #bfbfbf);
  margin: 0;
}

[data-theme="dark"] .drop-zone-formats {
  color: rgba(255, 255, 255, 0.45);
}

.file-drop-zone.has-files {
  padding: 16px;
  min-height: auto;
}

.files-preview {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 16px;
  width: 100%;
}

.file-item {
  position: relative;
  border: 1px solid var(--border-color, #d9d9d9);
  border-radius: 8px;
  padding: 12px;
  background: var(--bg-primary, #fff);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.file-item:hover {
  border-color: var(--color-primary, #1890ff);
  box-shadow: 0 2px 8px rgba(24, 144, 255, 0.15);
}

[data-theme="dark"] .file-item {
  background: var(--bg-primary, #1f1f1f);
  border-color: var(--border-color, #434343);
}

.file-preview {
  width: 100%;
  height: 120px;
  border-radius: 6px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-secondary, #f5f5f5);
}

[data-theme="dark"] .file-preview {
  background: var(--bg-secondary, #262626);
}

.file-preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.file-preview-video,
.file-preview-audio,
.file-preview-document {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: var(--text-tertiary, #8c8c8c);
}

[data-theme="dark"] .file-preview-video,
[data-theme="dark"] .file-preview-audio,
[data-theme="dark"] .file-preview-document {
  color: rgba(255, 255, 255, 0.45);
}

.file-info {
  flex: 1;
  min-width: 0;
}

.file-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary, #262626);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-bottom: 4px;
}

[data-theme="dark"] .file-name {
  color: rgba(255, 255, 255, 0.85);
}

.file-meta {
  display: flex;
  gap: 8px;
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

[data-theme="dark"] .file-meta {
  color: rgba(255, 255, 255, 0.65);
}

.file-progress {
  margin-top: 8px;
}

.file-actions {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
}

.file-list {
  margin-top: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.file-list-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border: 1px solid var(--border-color, #d9d9d9);
  border-radius: 6px;
  background: var(--bg-primary, #fff);
}

[data-theme="dark"] .file-list-item {
  background: var(--bg-primary, #1f1f1f);
  border-color: var(--border-color, #434343);
}

.file-list-icon {
  font-size: 24px;
  color: var(--text-tertiary, #8c8c8c);
}

.file-list-info {
  flex: 1;
  min-width: 0;
}

.file-list-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-primary, #262626);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

[data-theme="dark"] .file-list-name {
  color: rgba(255, 255, 255, 0.85);
}

.file-list-size {
  font-size: 12px;
  color: var(--text-secondary, #8c8c8c);
}

.file-preview-modal {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 400px;
}

.preview-image {
  max-width: 100%;
  max-height: 70vh;
  object-fit: contain;
}

.preview-video {
  max-width: 100%;
  max-height: 70vh;
}

.preview-audio {
  width: 100%;
}

.preview-unsupported {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  padding: 48px;
  color: var(--text-tertiary, #8c8c8c);
}

.preview-unsupported :deep(.anticon) {
  font-size: 64px;
}

/* Responsive */
@media (max-width: 768px) {
  .file-drop-zone {
    padding: 24px 16px;
    min-height: 150px;
  }

  .drop-zone-icon {
    font-size: 48px;
  }

  .files-preview {
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
  }

  .file-preview {
    height: 100px;
  }
}
</style>

