<template>
  <section class="complaint-detail">
    <!-- 標題 -->
    <div class="complaint-detail__title">
      <RouterLink
        :to="{ name: 'backend-complaint' }"
        class="back-link"
      >
        <i class="fa-solid fa-chevron-left"></i> 返回列表
      </RouterLink>
      <h1>申訴處理詳情</h1>
    </div>

    <template v-if="appeal">
      <!-- 資訊卡 -->
      <div class="info-card">
        <!-- <div class="info-card__field">
          <p class="info-card__label">申訴編號</p>
          <p class="info-card__value">#{{ appeal.id }}</p>
        </div> -->
        <div class="info-card__field">
          <p class="info-card__label">申訴人</p>
          <p class="info-card__value">{{ appeal.complainant }}</p>
        </div>
        <div class="info-card__field">
          <p class="info-card__label">被申訴對象</p>
          <p class="info-card__value">{{ appeal.respondent }}</p>
        </div>
        <div class="info-card__field">
          <p class="info-card__label">申訴類型</p>
          <p class="info-card__value">{{ appeal.type }}</p>
        </div>
        <div class="info-card__field">
          <p class="info-card__label">狀態</p>
          <p class="info-card__value">{{ statusMeta[appeal.status].label }}</p>
        </div>
        <div class="info-card__field">
          <p class="info-card__label">建立時間</p>
          <p class="info-card__value">{{ appeal.createdAt }}</p>
        </div>
        <div class="info-card__field">
          <p class="info-card__label">上下架狀態</p>
          <p class="info-card__value">{{ showLabel(appeal.isShow) }}</p>
        </div>
      </div>

      <!-- 已結案：處理結果 + 處理備註 -->
      <div
        v-if="isClosed"
        class="result-row"
      >
        <div class="panel result-panel">
          <h2 class="panel__title">處理結果</h2>
          <div class="result-panel__body">
            <div class="result-panel__item">
              <p class="result-panel__label">處理人員</p>
              <p class="result-panel__value">{{ appeal.handler }}</p>
            </div>
            <div class="result-panel__item">
              <p class="result-panel__label">處理結果</p>
              <!-- 處理結果由狀態推導（不另存欄位）：成立→違規次數+1、不成立→駁回申訴 -->
              <p class="result-panel__value">{{ resultMeta[appeal.status] }}</p>
            </div>
          </div>
        </div>

        <div class="panel note-panel">
          <h2 class="panel__title">處理備註</h2>
          <p class="note-panel__text">{{ appeal.respondedText }}</p>
        </div>
      </div>

      <!-- 申訴內容 + 證據截圖 -->
      <div class="panel content-panel">
        <h2 class="panel__title">申訴內容</h2>
        <p class="content-panel__text">{{ appeal.content }}</p>

        <h2 class="panel__title">證據截圖</h2>
        <div
          v-if="appeal.evidence && appeal.evidence.length"
          class="evidence-list"
        >
          <div
            v-for="(img, index) in appeal.evidence"
            :key="index"
            class="evidence-item"
          >
            <img
              :src="resolveEvidenceUrl(img)"
              alt="申訴佐證截圖"
              @click="openPreview(img)"
            >
          </div>
        </div>
        <p
          v-else
          class="evidence-empty"
        >
          此申訴未提供佐證圖片
        </p>
      </div>

      <!-- 待處理：處理面板 -->
      <div
        v-if="!isClosed"
        class="panel handle-panel"
      >
        <h2 class="panel__title">審核與處置</h2>

        <!-- 處理人員 -->
        <div class="handle-field">
          <label class="handle-field__label">處理人員</label>
          <div class="select-wrap">
            <select v-model="handler">
              <option
                v-for="admin in adminOptions"
                :key="admin"
                :value="admin"
              >
                {{ admin }}
              </option>
            </select>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
        </div>

        <!-- 處置內容 -->
        <div class="handle-field">
          <label class="handle-field__label">審核內容</label>
          <div class="disposition">
            <button
              type="button"
              class="disposition__chip"
              :class="{ active: disposition === 'confirm' }"
              @click="selectDisposition('confirm')"
            >
              確認違規
            </button>
            <button
              type="button"
              class="disposition__chip disposition__chip--reject"
              :class="{ active: disposition === 'reject' }"
              @click="selectDisposition('reject')"
            >
              駁回申訴
            </button>
          </div>
        </div>

        <!-- 內容處置：僅成立時出現，管理員自選保留/下架 -->
        <div
          v-if="disposition === 'confirm'"
          class="handle-field"
        >
          <label class="handle-field__label">處置內容</label>
          <div class="disposition">
            <button
              type="button"
              class="disposition__chip"
              :class="{ active: contentAction === 'remove' }"
              @click="selectContentAction('remove')"
            >
              下架內容
            </button>
            <button
              type="button"
              class="disposition__chip disposition__chip--reject"
              :class="{ active: contentAction === 'keep' }"
              @click="selectContentAction('keep')"
            >
              保留內容
            </button>
          </div>
        </div>

        <!-- 下架原因：僅成立且選下架時出現、必填 -->
        <div
          v-if="disposition === 'confirm' && contentAction === 'remove'"
          class="handle-field"
        >
          <label class="handle-field__label">下架原因</label>
          <textarea
            v-model="removeReason"
            class="handle-field__textarea"
            placeholder="請輸入下架原因"
            required
          ></textarea>
        </div>

        <!-- 處理備註 -->
        <div class="handle-field">
          <label class="handle-field__label">審核說明</label>
          <p class="disposition-hint">
            {{ dispositionHint }}
          </p>
          <textarea
            v-model="note"
            class="handle-field__textarea"
            placeholder="請輸入處置說明"
            required
          ></textarea>

        </div>

        <button
          type="button"
          class="submit-btn"
          @click="handleSubmit"
        >
          送出處理結果
        </button>
        <p class="disposition__note">
            確認違規後，會員將累計違規次數1次 ; 違規次數累計滿3次，將被停權7天。
        </p>
      </div>
    </template>

    <!-- 查無資料 -->
    <div
      v-else
      class="complaint-detail__empty"
    >
      <p>找不到對應的申訴案件</p>
      <RouterLink
        :to="{ name: 'backend-complaint' }"
        class="back-link"
      >
        返回列表
      </RouterLink>
    </div>

    <!-- 證據截圖放大預覽燈箱 -->
    <div
      v-if="previewImage"
      class="image-preview"
      @click="closePreview"
    >
      <button
        type="button"
        class="image-preview-close"
        @click.stop="closePreview"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>
      <img
        :src="previewImage"
        alt="證據截圖預覽"
        @click.stop
      >
    </div>
  </section>
</template>

<script setup>
  import { ref, computed, onMounted } from "vue";
  import { useRoute, useRouter } from "vue-router";

  const route = useRoute();
  const router = useRouter();

  // 判斷 php 執行環境，調整網址前綴（比照 myAppeal.vue）
  const phpBaseUrl =
    location.hostname === "localhost" || location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : "/ckd101/g2/php";

  const statusMeta = {
    pending: { label: "待處理" },
    confirmed: { label: "成立" },
    rejected: { label: "不成立" }
  };

  // 已結案「處理結果」由處置/狀態推導（不另存欄位）
  const resultMeta = {
    confirmed: "違規次數+1",
    rejected: "駁回申訴"
  };

  const adminOptions = ["管理員A", "管理員B"];

  // 從後台合併清單取回資料，依路由參數（sourceType + id）找出該筆
  const appeal = ref(null);

  async function fetchAppeal() {
    try {
      const res = await fetch(`${phpBaseUrl}/complaint/complaint_manage_get.php`, {
        credentials: "include"
      });
      const data = await res.json();
      if (data.success) {
        appeal.value =
          data.appeals.find(
            (item) =>
              item.sourceType === route.params.sourceType &&
              String(item.id) === route.params.id
          ) || null;
      }
    } catch (err) {
      console.error("取得申訴詳情失敗", err);
    }
  }

  onMounted(fetchAppeal);

  const isClosed = computed(
    () => appeal.value && appeal.value.status !== "pending"
  );

  // 證據截圖圖片 URL（比照 myAppealDetail.getEvidenceImageUrl）
  function resolveEvidenceUrl(path) {
    return `${phpBaseUrl}/${path}`;
  }

  // 證據截圖放大燈箱
  const previewImage = ref(null);
  function openPreview(img) {
    previewImage.value = resolveEvidenceUrl(img);
  }
  function closePreview() {
    previewImage.value = null;
  }

  // 被申訴內容的上下架狀態（is_show：1=上架、0=下架；PDO 可能回字串）
  function showLabel(isShow) {
    if (isShow === null || isShow === undefined || isShow === "") return "—";
    return Number(isShow) === 1 ? "上架中" : "已下架";
  }

  // 處理面板表單狀態
  const handler = ref(adminOptions[0]);
  const disposition = ref("");   // 'confirm' | 'reject'
  const contentAction = ref(""); // 'keep' | 'remove'（僅成立時適用）
  const removeReason = ref("");  // 選「下架」時的下架原因
  const note = ref("");

  // 切換裁決結果時，重置「內容處置」與「下架原因」
  function selectDisposition(value) {
    disposition.value = value;
    contentAction.value = "";
    removeReason.value = "";
  }

  // 切換內容處置；非「下架」時清空下架原因
  function selectContentAction(value) {
    contentAction.value = value;
    if (value !== "remove") {
      removeReason.value = "";
    }
  }

  // 依管理員所選的審核結果，提示處置說明的撰寫對象與內容
  const dispositionHint = computed(() => {
    if (disposition.value === "confirm") {
      return "此說明將提供「被申訴的會員」查看，請說明其違規事由及相關處置內容。";
    }

    if (disposition.value === "reject") {
      return "此說明將提供「提出申訴的會員」查看，請說明本次申訴不成立的原因。";
    }

    return "請先選擇審核結果，再填寫對應的處置說明。";
  });

  async function handleSubmit() {
    if (!disposition.value) {
      window.alert("請先選擇處置內容");
      return;
    }

    // 成立時需選擇內容處置（保留/下架）
    if (disposition.value === "confirm" && !contentAction.value) {
      alert("請選擇要保留或下架內容");
      return;
    }

    // 選擇下架時，下架原因為必填
    if (contentAction.value === "remove" && !removeReason.value.trim()) {
      alert("請填寫下架原因後再送出");
      return;
    }

      // 需填寫處置說明
    if (!note.value.trim()) {
      alert("請填寫處置說明後再送出審核結果");
      return;
    }

    // 送出前二次確認（此動作不可逆：成立會累計違規、可能停權，並依選擇下架內容）
    let confirmMessage;
    if (disposition.value === "reject") {
      confirmMessage = "確定要「駁回申訴」嗎？\n內容維持上架、會員違規次數不變。";
    } else if (contentAction.value === "remove") {
      confirmMessage = "確定要「確認違規並下架內容」嗎？\n會員違規次數 +1（累計滿 3 次將停權 7 天），且該內容將被下架。";
    } else {
      confirmMessage = "確定要「確認違規並保留內容」嗎？\n會員違規次數 +1（累計滿 3 次將停權 7 天），內容維持上架。";
    }
    if (!window.confirm(confirmMessage)) {
      return;
    }

    // 送出處理結果 → 寫回該筆申訴（狀態 / 處理管理員 / 回覆時間與內容）
    // contentAction（保留/下架）、removeReason（下架原因）供後端處理；後端尚未串接前會被忽略
    try {
      const body = new URLSearchParams({
        sourceType: appeal.value.sourceType,
        id: appeal.value.id,
        disposition: disposition.value,
        contentAction: contentAction.value,
        removeReason: removeReason.value,
        note: note.value
      });

      const res = await fetch(`${phpBaseUrl}/complaint/complaint_handle_post.php`, {
        method: "POST",
        credentials: "include",
        body
      });
      const data = await res.json();

      if (data.success) {
        router.push({ name: "backend-complaint" });
      } else {
        window.alert(data.message || "送出處理結果失敗");
      }
    } catch (err) {
      console.error("送出處理結果失敗", err);
      window.alert("送出處理結果失敗");
    }
  }
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  $cardWidth: 772px;

  .complaint-detail {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .complaint-detail__title {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;

    h1 {
      font-size: map-get($fontSize, h1);
      color: map-get($color, secondary2);
    }
  }

  .back-link {
    font-size: map-get($fontSize, default);
    color: map-get($color, neutral);
    text-decoration: none;

    &:hover {
      color: map-get($color, secondary2);
    }
  }

  // 共用卡片
  .panel {
    background-color: map-get($color, white);
    border: 1px solid map-get($color, hint);
    border-radius: 8px;

    &__title {
      font-size: map-get($fontSize, h4);
      font-weight: 500;
      color: map-get($color, secondary);
    }
  }

  // 資訊卡
  .info-card {
    display: flex;
    gap: 40px;
    flex-wrap: wrap;
    padding: 16px;

    background-color: map-get($color, white);
    border: 1px solid map-get($color, hint);
    border-radius: 8px;

    &__field {
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    &__label {
      font-size: map-get($fontSize, hint);
      color: #808080;
    }

    &__value {
      font-size: map-get($fontSize, default);
      font-weight: 500;
      color: #262626;
    }
  }

  // 已結案：處理結果 + 處理備註
  .result-row {
    display: flex;
    align-items: stretch;
    gap: 16px;
    flex-wrap: wrap;
  }

  .result-panel {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px;

    &__body {
      display: flex;
      gap: 32px;
    }

    &__item {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    &__label {
      font-size: map-get($fontSize, default);
      color: map-get($color, hint);
    }

    &__value {
      font-size: map-get($fontSize, default);
      color: map-get($color, secondary);
    }
  }

  .note-panel {
    flex: 1;
    min-width: 280px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px;

    &__text {
      font-size: map-get($fontSize, default);
      color: map-get($color, secondary);
      line-height: 1.6;
    }
  }

  // 申訴內容
  .content-panel {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 32px 16px;

    &__text {
      font-size: map-get($fontSize, default);
      color: #262626;
      line-height: 1.6;
      word-break: break-word;
    }
  }

  .evidence-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .evidence-item {
    width: 120px;
    height: 90px;

    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;

    background-color: map-get($color, secondary);

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      cursor: pointer;
    }
  }

  .evidence-empty {
    font-size: map-get($fontSize, default);
    color: map-get($color, hint);
  }

  // 證據截圖放大燈箱
  .image-preview {
    position: fixed;
    inset: 0;
    z-index: 9999;

    display: flex;
    align-items: center;
    justify-content: center;
    padding: 32px;

    background-color: rgba(0, 0, 0, 0.82);

    img {
      max-width: 90vw;
      max-height: 85vh;
      object-fit: contain;
      border-radius: 8px;
    }
  }

  .image-preview-close {
    position: absolute;
    top: 24px;
    right: 28px;
    padding: 8px;

    font-size: 24px;
    color: map-get($color, white);
    background: transparent;
    border: none;
    cursor: pointer;
  }

  // 待處理：處理面板
  .handle-panel {
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 16px;
    max-width: $cardWidth;
  }

  .handle-field {
    display: flex;
    flex-direction: column;
    gap: 6px;

    &__label {
      margin-top: 4px;
      font-size: map-get($fontSize, default);
      color: map-get($color, secondary);
    }

    &__hint {
      font-size: map-get($fontSize, hint);
      color: map-get($color, hint);
    }

    &__textarea {
      width: 100%;
      height: 90px;
      padding: 8px;
      resize: vertical;

      font-size: 16px;
      color: map-get($color, secondary);
      background-color: map-get($color, white);
      border: 1px solid map-get($color, hint);
      border-radius: 6px;
      outline: none;

      &:focus {
        border-color: map-get($color, secondary2);
      }
    }

    .disposition-hint {
      font-size: map-get($fontSize, hint);
      color: map-get($color, hint);
      line-height: 1.5;
    }
  }

  .select-wrap {
    position: relative;
    width: 100%;

    select {
      width: 100%;
      height: 40px;
      padding: 0 36px 0 8px;
      appearance: none;

      font-size: 13px;
      color: map-get($color, secondary);
      background-color: map-get($color, white);
      border: 1px solid map-get($color, hint);
      border-radius: 6px;
      cursor: pointer;
    }

    i {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      pointer-events: none;
      color: map-get($color, secondary);
    }
  }

  // 處置內容 chip
  .disposition {
    display: flex;
    gap: 12px;

    &__chip {
      height: 40px;
      padding: 0 8px;

      font-size: 13px;
      font-weight: 500;
      color: map-get($color, secondary);
      background-color: map-get($color, white);
      border: 1px solid map-get($color, hint);
      border-radius: 6px;
      cursor: pointer;

      transition: border-color 0.2s, background-color 0.2s;

      &--reject {
        color: map-get($color, error);
      }

      &.active {
        border-color: map-get($color, secondary2);
        background-color: map-get($color, lightYellow);
      }
    }
  }

  .disposition__note {
    font-size: map-get($fontSize, hint);
    color: map-get($color, error);
    text-decoration: underline;
    line-height: 1.5;
  }

  // 送出按鈕
  .submit-btn {
    width: 160px;
    padding: 10px 12px;

    font-size: map-get($fontSize, default);
    color: map-get($color, secondary);
    background-color: map-get($color, primary);
    border-radius: 6px;
    cursor: pointer;
  }

  .complaint-detail__empty {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 48px 0;
    align-items: flex-start;

    color: map-get($color, hint);
  }
</style>
