<template>
  <section class="complaint-page">
    <h1 class="page-title">提出申訴</h1>

    <div class="complaint-layout">
      <form class="complaint-card" @submit.prevent="handleSubmit">
        <div class="form-row">
          <div class="form-group form-group--type">
            <label for="complaintType">申訴類型</label>
            <div class="select-wrap">
              <select id="complaintType" v-model="formData.type">
                <option v-for="option in typeOptions" :key="option" :value="option">
                  {{ option }}
                </option>
              </select>
              <i class="fa-solid fa-chevron-down"></i>
            </div>
          </div>

          <div class="form-group form-group--grow">
            <label for="complaintTarget">被申訴對象（暱稱／ID）</label>
            <input
              id="complaintTarget"
              type="text"
              v-model="formData.target"
              required
            />
          </div>
        </div>

        <div class="form-group">
          <label for="complaintTitle">標題</label>
          <input
            id="complaintTitle"
            type="text"
            v-model="formData.title"
            required
          />
        </div>

        <div class="form-group">
          <label for="complaintDescription">申訴說明</label>
          <textarea
            id="complaintDescription"
            v-model="formData.description"
            required
          ></textarea>
        </div>

        <p class="upload-label">上傳證據截圖（最多 5 張）</p>
        <div class="upload-grid">
          <div class="upload-thumb" v-for="(image, index) in images" :key="image.url">
            <img :src="image.url" alt="申訴證據截圖預覽" />
            <button
              type="button"
              class="upload-remove"
              @click="removeImage(index)"
              aria-label="移除截圖"
            >
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <button
            v-if="images.length < maxImages"
            type="button"
            class="upload-add"
            @click="triggerUpload"
          >
            <i class="fa-solid fa-plus"></i>
            <span>新增</span>
          </button>

          <input
            ref="fileInput"
            type="file"
            accept="image/*"
            multiple
            hidden
            @change="onFileChange"
          />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-submit">送出申訴</button>
          <button type="button" class="btn-cancel" @click="handleCancel">取消</button>
        </div>
      </form>

      <aside class="complaint-notice">
        <h2>申訴須知</h2>
        <p>・ 請提供完整交易或互動證據</p>
        <p>・ 惡意濫用申訴將影響帳號信譽</p>
        <p>・ 一般案件將於 3 個工作天內處理</p>
        <p>・ 可於「申訴進度查詢」追蹤結果</p>
      </aside>
    </div>
  </section>
</template>

<script>
const MAX_IMAGES = 5;

export default {
  name: "complaintForm",

  data() {
    return {
      typeOptions: ["二手交換區", "論壇內容", "約戰配對"],
      formData: {
        type: "二手交換區",
        target: "",
        title: "",
        description: ""
      },
      images: [],
      maxImages: MAX_IMAGES
    };
  },

  beforeUnmount() {
    this.images.forEach(image => URL.revokeObjectURL(image.url));
  },

  methods: {
    triggerUpload() {
      this.$refs.fileInput.click();
    },

    onFileChange(event) {
      const remaining = this.maxImages - this.images.length;
      const files = Array.from(event.target.files).slice(0, remaining);

      files.forEach(file => {
        this.images.push({ file, url: URL.createObjectURL(file) });
      });

      event.target.value = "";
    },

    removeImage(index) {
      URL.revokeObjectURL(this.images[index].url);
      this.images.splice(index, 1);
    },

    handleSubmit() {
      alert("申訴已送出！");
      window.location.href = `${import.meta.env.BASE_URL}index.html`;
    },

    handleCancel() {
      window.history.back();
    }
  }
};
</script>

<style lang="scss" scoped>
@use "sass:map";
@use "@/assets/scss/var" as *;

.complaint-page {
  width: min(1392px, 100%);
  margin-inline: auto;
  padding: 24px;

  display: flex;
  flex-direction: column;
  gap: 20px;
}

.page-title {
  font-size: map.get($fontSize, h2);
  font-weight: 500;
  color: map.get($color, secondary2);
}

.complaint-layout {
  display: flex;
  flex-direction: column;
  gap: 24px;
  width: 100%;

  @media (min-width: 992px) {
    flex-direction: row;
    align-items: flex-start;
  }
}

.complaint-card {
  flex: 1 0 0;
  min-width: 0;

  display: flex;
  flex-direction: column;
  gap: 16px;

  padding: 20px;
  background-color: map.get($color, white);
  border: 1px solid #ccc;
  border-radius: 8px;
}

.form-row {
  display: flex;
  flex-direction: column;
  gap: 16px;

  @media (min-width: 576px) {
    flex-direction: row;
    align-items: flex-start;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;

  &--type {
    width: 100%;

    @media (min-width: 576px) {
      width: 200px;
      flex-shrink: 0;
    }
  }

  &--grow {
    flex: 1 0 0;
    min-width: 0;
  }

  label {
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);
  }

  input,
  textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid map.get($color, gray);
    border-radius: 6px;
    outline: none;
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);

    &:focus {
      border-color: map.get($color, secondary2);
    }
  }

  textarea {
    height: 120px;
    resize: vertical;
  }
}

.select-wrap {
  position: relative;
  width: 100%;

  select {
    width: 100%;
    height: 40px;
    padding: 8px 32px 8px 8px;
    appearance: none;
    border: 1px solid map.get($color, gray);
    border-radius: 6px;
    outline: none;
    background-color: map.get($color, white);
    font-size: map.get($fontSize, hint);
    color: map.get($color, secondary);
    cursor: pointer;

    &:focus {
      border-color: map.get($color, secondary2);
    }
  }

  i {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    font-size: 12px;
    color: map.get($color, secondary);
    pointer-events: none;
  }
}

.upload-label {
  font-size: map.get($fontSize, default);
  color: map.get($color, black);
}

.upload-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
}

.upload-thumb {
  position: relative;
  width: 120px;
  height: 90px;
  border: 1px solid #ccc;
  border-radius: 4px;
  overflow: hidden;

  img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .upload-remove {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;
    background-color: rgba(20, 28, 38, 0.65);
    color: map.get($color, white);
    font-size: 11px;

    opacity: 0;
    transition: opacity 0.2s ease;
  }

  &:hover .upload-remove {
    opacity: 1;
  }
}

.upload-add {
  width: 120px;
  height: 90px;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;

  background-color: #e0e0e0;
  border: 1px solid #ccc;
  border-radius: 4px;
  color: #808080;
  font-size: 12px;

  cursor: pointer;
  transition: background-color 0.2s ease;

  &:hover {
    background-color: darken(#e0e0e0, 5%);
  }
}

.form-actions {
  display: flex;
  justify-content: left;
  gap: 12px;
}

.btn-submit,
.btn-cancel {
  padding: 12px 32px;
  border-radius: 12px;
  font-size: map.get($fontSize, h4);
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-submit {
  background-color: map.get($color, primary);
  color: map.get($color, secondary);
  border: 1px solid map.get($color, primary);

  &:hover {
    background-color: darken(map.get($color, primary), 8%);
  }
}

.btn-cancel {
  background-color: transparent;
  color: map.get($color, secondary);
  border: 1px solid map.get($color, secondary);

  &:hover {
    background-color: rgba(20, 28, 38, 0.06);
  }
}

.complaint-notice {
  width: 100%;
  padding: 20px;

  display: flex;
  flex-direction: column;
  gap: 10px;

  background-color: map.get($color, neutral);
  border-radius: 8px;

  @media (min-width: 992px) {
    width: 360px;
    flex-shrink: 0;
  }

  h2 {
    font-size: map.get($fontSize, h4);
    font-weight: 500;
    color: map.get($color, secondary2);
  }

  p {
    font-size: map.get($fontSize, default);
    color: map.get($color, white);
  }
}
</style>
