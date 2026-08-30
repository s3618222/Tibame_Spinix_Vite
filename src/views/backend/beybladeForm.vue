<template>
  <div class="beyblade-form-page">
    <RouterLink :to="{ name: 'backend-beyblade' }" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      <span>返回零件列表</span>
    </RouterLink>

    <div class="beyblade-form-header">
      <h2 v-if="isEditMode">
        編輯零件數據
        <span class="id-badge">ID: {{ form.code }}</span>
      </h2>
      <h2 v-else>新增零件</h2>
    </div>

    <div class="beyblade-form-card">
      <!-- 基本資訊 -->
      <section class="form-section">
        <h3 class="form-section-title">基本資訊</h3>

        <div class="form-row">
          <div class="form-field form-field--name">
            <label for="partName">零件名稱 <span class="required-mark">*</span></label>
            <input
              id="partName"
              type="text"
              v-model="form.name"
              placeholder="請輸入零件名稱..."
              :class="{ '-isError': fieldErrors.name || fieldErrors.nameTooLong }"
              @input="fieldErrors.name = false"
            >
          </div>

          <div class="form-field form-field--category">
            <label for="partCategory">部件類別 <span class="required-mark">*</span></label>
            <select id="partCategory" v-model="form.category"
             :class="{ '-isError': fieldErrors.category }"
              @change="fieldErrors.category = false">
              <option value="" disabled selected hidden>請選擇類別</option>
              <option value="戰刃">戰刃</option>
              <option value="固鎖">固鎖</option>
              <option value="軸心">軸心</option>
            </select>
          </div>
        </div>
      </section>

      <!-- 零件圖片 -->
      <section class="form-section">
        <h3 class="form-section-title">零件圖片<span class="required-mark">*</span></h3>

        <div class="image-upload" :class="{ '-isError': fieldErrors.pic }" @click="triggerFileSelect">
          <input
            ref="fileInput"
            type="file"
            accept="image/png"
            class="file-input-hidden"
            @change="handleFileChange"
          >
          <div v-if="!form.pic" class="image-upload-empty">
            <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
            <p class="upload-text">點擊或拖拽上傳零件圖片 (PNG)</p>
            <p class="upload-hint">建議尺寸 512x512px，檔案大小上限 1MB</p>
          </div>

          <div v-else class="image-preview">
            <img :src="resolvedPicUrl" :alt="form.name">
            <div class="image-preview-overlay">
              <span>更換圖片</span>
            </div>
          </div>
        </div>
      </section>

      <!-- 零件數值 -->
      <section class="form-section">
        <h3 class="form-section-title">零件數值</h3>

        <div class="stat-row" v-for="stat in statFields" :key="stat.key">
          <span class="stat-label">{{ stat.label }}</span>

          <input
            type="range"
            class="stat-slider"
            min="0"
            max="100"
            v-model.number="form[stat.key]"
            :style="sliderTrackStyle(form[stat.key])"
          >

          <input
            type="number"
            class="stat-number"
            min="0"
            max="100"
            v-model.number="form[stat.key]"
            @change="clampStat(stat.key)"
          >
        </div>
      </section>

      <!-- 發布設定 -->
      <section class="form-section">
        <h3 class="form-section-title">發布設定</h3>

        <div class="publish-row">
          <span class="publish-label">{{ publishLabel }}</span>

          <div class="publish-toggle">
            <button
              v-for="opt in publishOptions"
              :key="String(opt.value)"
              type="button"
              class="publish-option"
              :class="{ 'publish-option--active': form.is_show === opt.value }"
              @click="form.is_show = opt.value"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>
      </section>
    </div>

    <!-- 底部固定操作列 -->
    <div class="beyblade-form-actions">
      <div class="actions-left">
        <button
          v-if="isEditMode"
          type="button"
          class="btn-delete"
          @click="handleDelete"
        >
          刪除零件
        </button>
      </div>

      <div class="actions-right">
        <RouterLink :to="{ name: 'backend-beyblade' }" class="btn-cancel">
          取消
        </RouterLink>
        <button type="button" class="btn-save" @click="handleSave">
          {{ isEditMode ? '儲存修改' : '儲存並建立零件' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";
export default {
  name: "BeybladeForm",

  data() {
    return {
      baseUrl: import.meta.env.BASE_URL,
      selectedFile: null, // 存放使用者實際選擇的檔案物件（File），送出表單時要用
      categoryToEnglish: {
        戰刃: "Blade",
        固鎖: "Ratchet",
        軸心: "Bit"
      },

      categoryLabelMap: {
        Blade: "戰刃",
        Ratchet: "固鎖",
        Bit: "軸心"
      },

      statFields: [
        { key: "weight", label: "重量" },
        { key: "attack", label: "攻擊" },
        { key: "defense", label: "防禦" },
        { key: "stamina", label: "持久" }
      ],

      form: {},

      fieldErrors: {
        name: false,
        nameTooLong: false, //超過100字
        category: false,
        pic: false
      }
    };
  },

  computed: {
    // 有 :id 路由參數 → edit 模式；沒有 → create 模式
    isEditMode() {
      return !!this.$route.params.id;
    },

    resolvedPicUrl() {
      if (!this.form.pic) return "";

      // blob: 開頭代表是使用者剛選的本機檔案，URL.createObjectURL() 產生的網址
      // 本身已經是完整可用的網址，不需要、也不能再加 baseUrl
      if (this.form.pic.startsWith("blob:")) {
        return this.form.pic;
      }

      // uploads/beyblade/ 開頭代表這是已經上傳到伺服器的真實圖片，
      // 要走 phpBaseUrl（伺服器路徑），不能走 baseUrl（前端 public 資料夾）
      if (this.form.pic.startsWith("uploads/beyblade/")) {
        return `${phpBaseUrl}/${this.form.pic}`;
      }

      return `${this.baseUrl}${this.form.pic}`;
    },

    publishLabel() {
      return this.isEditMode ? "上架狀態" : "是否立即上架";
    },

    // create／edit 共用同一個 form.is_show 布林值，只是顯示文字不同
    publishOptions() {
      return this.isEditMode
        ? [
            { value: true, label: "使用中" },
            { value: false, label: "已停用" }
          ]
        : [
            { value: true, label: "是" },
            { value: false, label: "否" }
          ];
    }
  },

  // /beyblade/new 與 /beyblade/:id/edit 指向同一個元件，Vue Router 在兩者間切換時
  // 會重用同一個元件實例、不會重新觸發 created()，所以改用監看 $route.params.id
  // （並加上 immediate: true）來確保「切換路由」跟「第一次進入」都會重新載入表單資料。
  watch: {
    "$route.params.id": {
      immediate: true,
      handler(id) {
        this.loadForm(id);
      }
    }
  },

  methods: {
    async loadForm(id) {
      if (!id) {
        this.form = {
          id: null,
          code: "",
          name: "",
          category: "",
          pic: "",
          attack: 50,
          defense: 50,
          stamina: 50,
          weight: 50,
          is_show: false
        };
        return;
      }

      try {
        const res = await fetch(`${phpBaseUrl}/build/getBeyblade.php?beyblade_id=${id}`, {
          credentials: "include"
        });
        const result = await res.json();

        if (result.success) {
          const item = result.data;
          this.form = {
            id: item.beyblade_id,
            code: `#P-${item.beyblade_id}`,
            name: item.name,
            category: this.categoryLabelMap[item.category] ?? item.category,
            pic: item.pic,
            attack: Number(item.attack),
            defense: Number(item.defense),
            stamina: Number(item.stamina),
            weight: Number(item.weight),
            is_show: Number(item.is_show) === 1
          };
        } else {
          alert(result.message);
          this.$router.push({ name: "backend-beyblade" });
        }
      } catch (error) {
        console.error("取得零件資料失敗", error);
        alert("取得零件資料失敗，請稍後再試");
      }
    },


    // 依目前數值計算滑桿已滑過部分的漸層背景（給 WebKit 系瀏覽器用，Firefox 用 ::-moz-range-progress 原生達成同樣效果）
    sliderTrackStyle(value) {
      const percent = Math.min(100, Math.max(0, Number(value) || 0));

      return {
        background: `linear-gradient(to right, #fec96b 0%, #fec96b ${percent}%, #e5e2df ${percent}%, #e5e2df 100%)`
      };
    },

    clampStat(key) {
      const value = Math.min(100, Math.max(0, Number(this.form[key]) || 0));
      this.form[key] = value;
    },

    triggerFileSelect() {
      this.$refs.fileInput.click();
    },

    async handleDelete() {
      const isConfirmed = confirm(`確定要刪除零件「${this.form.name}」嗎？此動作無法復原。`);
      if (!isConfirmed) return;

      const formData = new FormData();
      formData.append("beyblade_id", this.form.id);

      try {
        const res = await fetch(`${phpBaseUrl}/build/deleteBeyblade.php`, {
          method: "POST",
          body: formData,
          credentials: "include"
        });
        const result = await res.json();

        if (result.success) {
          alert(result.message);
          this.$router.push({ name: "backend-beyblade" });
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error("刪除零件失敗", error);
        alert("刪除零件失敗，請稍後再試");
      }
    },

    validateForm() {
      // 依序檢查，每個欄位各自判斷是否為空，結果分別記在 fieldErrors
      this.fieldErrors.name = this.form.name.trim() === "";
      this.fieldErrors.nameTooLong = this.form.name.trim() !== "" && this.form.name.length > 100;
      this.fieldErrors.category = !this.form.category;

      // 圖片：新增模式必填（沒有選檔案也沒有既有圖片路徑才算空/false）；
      // 編輯模式選填，因為可能維持原圖不換，不能因為沒選新檔案就判定成錯誤
      this.fieldErrors.pic = !this.isEditMode && !this.selectedFile && !this.form.pic;

      // 箭頭函式，因為this.fieldErrors每個值都是布林值，透過.some()遍歷陣列，只要有遇到是true的，就會停止執行、回傳結果
      const hasError = Object.values(this.fieldErrors).some(isError => isError);

      if (this.fieldErrors.nameTooLong) {
        alert("零件名稱不可超過 100 字");
      } else if (hasError) {
        alert("星號*為必填項目");
      }

      return !hasError;
    },

    async handleSave() {
      //validateForm的結果如果是false，代表確實有欄位沒填，handleSave後面就不執行了
      if (!this.validateForm()) return;

      const formData = new FormData();

      formData.append("name", this.form.name);
      formData.append("category", this.categoryToEnglish[this.form.category]);
      formData.append("attack", this.form.attack);
      formData.append("defense", this.form.defense);
      formData.append("stamina", this.form.stamina);
      formData.append("weight", this.form.weight);
      formData.append("is_show", this.form.is_show ? 1 : 0);

      // 只有使用者真的選了新檔案，才附上圖片；
      // 編輯模式下如果沒換圖，selectedFile 會是 null，這裡就不會 append，
      // 後端 updateBeyblade.php 收不到 $_FILES["pic"]，會自動沿用資料庫原本的路徑
      if (this.selectedFile) {
        formData.append("pic", this.selectedFile);
      }

      const apiUrl = this.isEditMode
        ? `${phpBaseUrl}/build/updateBeyblade.php`
        : `${phpBaseUrl}/build/addBeyblade.php`;

      if (this.isEditMode) {
        formData.append("beyblade_id", this.form.id);
      }

      try {
        const res = await fetch(apiUrl, {
          method: "POST",
          body: formData,
          credentials: "include"
        });
        const result = await res.json();

        if (result.success) {
          alert(result.message);
          this.$router.push({ name: "backend-beyblade" });
        } else {
          alert(result.message);
        }
      } catch (error) {
        console.error("儲存零件失敗", error);
        alert("儲存零件失敗，請稍後再試");
      }
    },

    handleFileChange(event) {
      const file = event.target.files[0];
      if (!file) return;

      this.selectedFile = file;
      this.form.pic = URL.createObjectURL(file); // 產生本機暫時預覽網址，讓使用者立刻看到選了什麼圖
      this.fieldErrors.pic = false;
    }
  }
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

// 側邊導覽列實際寬度取自 backMember.vue 的 .back-member
// { grid-template-columns: 168px minmax(0, 1fr); }，
// 讓底部固定操作列的 left 對齊主內容區，不會蓋住側邊導覽列。
// 斷點同樣對齊 backMember.vue 的 1024px：該寬度以下 sidebar 改為覆蓋式選單，
// 不再佔用版面寬度，固定列改回貼齊畫面左緣。
$sidebar-width: 168px;
$actions-bar-height: 88px;

.-isError {
  border-color: map-get($color, error) !important;
}

.required-mark {
  color: map-get($color, error);
  // margin-left: 2px;
}

.beyblade-form-page {
  width: 100%;
  // 幫可捲動內容預留跟底部固定列一樣高的空間，避免最後一個區塊被遮住
  padding-bottom: $actions-bar-height;

  display: flex;
  flex-direction: column;
  gap: 24px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;

  color: map-get($color, neutral);
  text-decoration: none;
  font-size: 14px;

  &:hover {
    color: map-get($color, secondary);
  }
}

.beyblade-form-header h2 {
  display: flex;
  align-items: center;
  gap: 12px;

  color: map-get($color, secondary2);
  font-weight: 600;
  font-size: map-get($fontSize, h1);
  margin-bottom: 28px;
}

.id-badge {
  padding: 4px 12px;
  border-radius: 999px;

  background-color: #eeeeee;
  color: map-get($color, neutral);
  font-size: 14px;
  font-weight: 400;
}

.beyblade-form-card {
  width: 100%;
  padding: 32px;

  display: flex;
  flex-direction: column;
  gap: 32px;

  background-color: map-get($color, white);
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
}

.form-section-title {
  margin-bottom: 16px;
  color: map-get($color, secondary);
  font-size: 18px;
  font-weight: 600;
}

/* 基本資訊 */
.form-row {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 8px;

  label {
    font-size: 14px;
    color: map-get($color, neutral);
  }

  input,
  select {
    padding: 10px 12px;

    border: 1px solid #ddd6c8;
    border-radius: 8px;
    outline: none;

    background-color: map-get($color, tertiary);
    color: map-get($color, secondary);
    font-size: 14px;

    transition: border-color 0.24s;

    &:focus {
      border-color: map-get($color, secondary2);
    }

    &::placeholder {
      color: map-get($color, hint);
    }
  }
}

.form-field--name {
  flex: 2 1 280px;
}

.form-field--category {
  flex: 1 1 200px;
}

/* 零件圖片 */
.file-input-hidden {
  display: none;
}
.image-upload {
  border: 2px solid transparent; // 新增這行，平常透明看不見，但邊框「存在」了
  border-radius: 12px;
  width: 220px;
  height: 220px;
  cursor: pointer;
}

.image-upload-empty {
  width: 100%;
  height: 100%;
  padding: 16px;

  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;

  border: 2px dashed map-get($color, gray);
  border-radius: 12px;

  background-color: map-get($color, tertiary);
  text-align: center;
  transition: border-color 0.24s;

  &:hover {
    border-color: map-get($color, secondary2);
  }
}

.upload-icon {
  font-size: 28px;
  color: map-get($color, neutral);
}

.upload-text {
  color: map-get($color, secondary);
  font-size: 14px;
}

.upload-hint {
  color: map-get($color, hint);
  font-size: 12px;
}

.image-preview {
  position: relative;
  width: 100%;
  height: 100%;

  overflow: hidden;
  border-radius: 12px;

  img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
  }
}

.image-preview-overlay {
  position: absolute;
  inset: 0;

  display: flex;
  align-items: center;
  justify-content: center;

  background-color: rgba(20, 28, 38, 0.5);
  color: map-get($color, white);
  font-size: 14px;

  opacity: 0;
  transition: opacity 0.24s;
}

.image-preview:hover .image-preview-overlay {
  opacity: 1;
}

/* 零件數值 */
.stat-row {
  display: flex;
  align-items: center;
  gap: 16px;

  & + .stat-row {
    margin-top: 16px;
  }
}

.stat-label {
  width: 56px;
  flex-shrink: 0;

  color: map-get($color, secondary);
  font-size: 14px;
}

// 覆蓋瀏覽器預設 range 外觀：軌道淺灰、已滑過部分橘色（primary）、滑塊白底圓形帶陰影
.stat-slider {
  flex: 1 0 0;
  height: 6px;

  appearance: none;
  -webkit-appearance: none;
  border-radius: 999px;
  outline: none;
  cursor: pointer;

  // WebKit（Chrome/Edge/Safari）：軌道底色 + 已滑過部分的橘色漸層，
  // 直接寫在 input 本身的 background（由 sliderTrackStyle() 動態產生），
  // 這裡只需處理滑塊本身
  &::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;

    border-radius: 50%;
    background-color: map-get($color, white);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    cursor: pointer;
  }

  // Firefox：原生支援 ::-moz-range-progress 表示「已滑過」區段，
  // 不需要额外用 JS 算漸層
  &::-moz-range-track {
    height: 6px;
    border-radius: 999px;
    background-color: #e5e2df;
  }

  &::-moz-range-progress {
    height: 6px;
    border-radius: 999px 0 0 999px;
    background-color: map-get($color, primary);
  }

  &::-moz-range-thumb {
    width: 18px;
    height: 18px;

    border: none;
    border-radius: 50%;
    background-color: map-get($color, white);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    cursor: pointer;
  }
}

.stat-number {
  width: 64px;
  flex-shrink: 0;
  padding: 6px 8px;

  border: 1px solid #ddd6c8;
  border-radius: 8px;
  outline: none;

  background-color: map-get($color, tertiary);
  color: map-get($color, secondary);
  font-size: 14px;
  text-align: center;

  &:focus {
    border-color: map-get($color, secondary2);
  }
}

/* 發布設定 */
.publish-row {
  display: flex;
  align-items: center;
  gap: 16px;
}

.publish-label {
  color: map-get($color, secondary);
  font-size: 14px;
}

.publish-toggle {
  display: inline-flex;
  overflow: hidden;

  border: 1px solid map-get($color, warmGray);
  border-radius: 999px;
}

.publish-option {
  padding: 8px 24px;

  border: none;
  background-color: transparent;
  color: map-get($color, neutral);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background-color: map-get($color, tertiary);
  }

  &--active {
    background-color: map-get($color, primary);
    color: map-get($color, secondary);
    font-weight: 600;
  }
}

/* 底部固定操作列 */
.beyblade-form-actions {
  position: fixed;
  left: $sidebar-width;
  right: 0;
  bottom: 0;
  z-index: 500;

  min-height: $actions-bar-height;
  padding: 20px 48px;

  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;

  background-color: map-get($color, white);
  box-shadow: 0 -4px 20px rgba(20, 28, 38, 0.08);
}

.actions-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-delete {
  padding: 10px 20px;

  border: 1px solid map-get($color, error);
  border-radius: 8px;

  background-color: transparent;
  color: map-get($color, error);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background-color: map-get($color, error);
    color: map-get($color, white);
  }
}

.btn-cancel {
  padding: 10px 16px;

  color: map-get($color, neutral);
  text-decoration: none;
  font-size: 14px;
  cursor: pointer;

  &:hover {
    color: map-get($color, secondary);
  }
}

.btn-save {
  padding: 10px 24px;

  border: none;
  border-radius: 8px;

  background-color: map-get($color, primary);
  color: map-get($color, secondary);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.24s;

  &:hover {
    background-color: darken(#fec96b, 6%);
  }
}

@media screen and (max-width: 1024px) {
  .beyblade-form-actions {
    left: 0;
    padding: 16px 24px;
  }
}

@media screen and (max-width: 768px) {
  .beyblade-form-card {
    padding: 24px;
  }

  .form-field--name,
  .form-field--category {
    flex: 1 1 100%;
  }

  .image-upload {
    width: 100%;
    max-width: 220px;
  }

  .beyblade-form-actions {
    padding: 16px;
  }

  .actions-right {
    gap: 8px;
  }
}
</style>
