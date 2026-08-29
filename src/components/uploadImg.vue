<template>
  <div class="form-label-style">
    <label class="label-title">物品照片（請至少上傳1張，最多 {{ MAX_PHOTOS }} 張）</label>

    <div class="photo-grid">
      <div v-for="(photo, index) in photos" :key="photo.id" class="photo-tile">
        <img :src="photo.url" :alt="`商品照片 ${index + 1}`" class="photo-tile__img" />
        <button
          type="button"
          class="photo-tile__remove"
          :aria-label="`刪除第 ${index + 1} 張照片`"
          @click="removePhoto(photo.id)"
        >
          <svg viewBox="0 0 24 24" width="12" height="12" fill="none">
            <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>
          </svg>
        </button>
      </div>

      <button
        v-if="photos.length < MAX_PHOTOS"
        type="button"
        class="photo-tile photo-tile--add"
        @click="triggerUpload"
      >
        <span class="photo-tile__plus">+ 新增</span>
      </button>
    </div>

    <input
      ref="fileInput"
      type="file"
      accept="image/*"
      multiple
      class="visually-hidden"
      @change="handleFiles"
    />
  </div>
</template>

<script setup>
import { ref, onBeforeUnmount } from 'vue'

const MAX_PHOTOS = 5

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const photos = ref(
  props.modelValue.map((url) => ({
    id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    url,
    file: null
  }))
)

const fileInput = ref(null)

function triggerUpload() {
  fileInput.value?.click()
}

function handleFiles(event) {
  const files = Array.from(event.target.files || [])
  const remainingSlots = MAX_PHOTOS - photos.value.length

  files.slice(0, remainingSlots).forEach((file) => {
    photos.value.push({
      id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
      url: URL.createObjectURL(file),
      file,
    })
  })

  event.target.value = ''
  emitUpdate()
}

function removePhoto(id) {
  const index = photos.value.findIndex((p) => p.id === id)
  if (index === -1) return
  URL.revokeObjectURL(photos.value[index].url)
  photos.value.splice(index, 1)
  emitUpdate()
}

function emitUpdate() {
  emit('update:modelValue', photos.value.map(p => p.url))
}

defineExpose({
  getFiles: () => photos.value.map(p => p.file)
});

</script>

<style lang="scss" scoped>
@use '@/assets/scss/_var' as *;

.photo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 8px;
  margin-top: 8px;
}

.photo-tile {
  position: relative;
  aspect-ratio: 1 / 1;
  border-radius: 2px;
  overflow: hidden;
  border: 1px solid #ddd;
  background-color: #fff;

  &__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  &__remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: .2s;
    &:hover {
      background: map-get($color, error);
    }
  }

  &--add {
    background: none;
    border: 1px dashed map-get($color, hint);
    color: map-get($color, hint);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all .3s;
    &:hover {
      border-color: map-get($color, primary);
      color: map-get($color, primary);
    }
  }
}

.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
}
</style>