<template>
  <div class="form-label-style">
    <!-- form-section -->
    <label class="label-title">物品照片（最多 {{ MAX_PHOTOS }} 張）</label>

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

      <!-- 尚未上傳任何照片時，畫面上只會出現這一個「+ 新增」物件；滿 5 張後自動隱藏 -->
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
import { reactive, ref, onBeforeUnmount } from 'vue'

const MAX_PHOTOS = 5

const categoryOptions = ['陀螺本體', '發射器', '對戰盤', '改裝零件', '周邊商品']
const conditionOptions = ['全新', '二手－良好', '二手－有使用痕跡']

const cityDistrictMap = {
  台北市: ['中正區', '大同區', '中山區', '松山區', '大安區', '萬華區', '信義區', '士林區', '北投區', '內湖區', '南港區', '文山區'],
  新北市: ['板橋區', '三重區', '中和區', '永和區', '新莊區', '新店區', '土城區', '樹林區', '汐止區', '鶯歌區'],
  桃園市: ['桃園區', '中壢區', '平鎮區', '八德區', '楊梅區', '蘆竹區'],
  台中市: ['中區', '西區', '南區', '北區', '西屯區', '南屯區', '北屯區'],
  台南市: ['中西區', '東區', '南區', '北區', '安平區', '安南區'],
  高雄市: ['鹽埕區', '鼓山區', '左營區', '楠梓區', '三民區', '新興區'],
}

const emit = defineEmits(['back', 'submit'])

// ---- 商品照片：尚未上傳任何照片時 photos 為空陣列，畫面上只會顯示一個「+ 新增」物件 ----
const photos = ref([])
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
}

function removePhoto(id) {
  const index = photos.value.findIndex((p) => p.id === id)
  if (index === -1) return
  URL.revokeObjectURL(photos.value[index].url)
  photos.value.splice(index, 1)
}

onBeforeUnmount(() => {
  photos.value.forEach((p) => URL.revokeObjectURL(p.url))
})

// ---- 表單欄位 ----
// const initialForm = {
//   category: '',
//   name: '',
//   condition: '',
//   city: '',
//   district: '',
//   contact: '',
//   wantItem: '',
//   description: '',
// }
// const form = reactive({ ...initialForm })

// function resetAll() {
//   Object.assign(form, initialForm)
//   photos.value.forEach((p) => URL.revokeObjectURL(p.url))
//   photos.value = []
// }

// function handleSubmit() {
//   emit('submit', { photos: photos.value, form: { ...form } })
// }
</script>

