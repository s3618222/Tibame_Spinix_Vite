<template>
  <section class="appeal-detail">
    <RouterLink :to="{ name: 'member-appeal' }" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      返回列表
    </RouterLink>

    <template v-if="appeal">
      <h1>申訴表單詳情</h1>

      <div class="meta-bar">
        <!-- <div class="meta-item">
          <p class="meta-label">申訴編號</p>
          <p class="meta-value">#{{ appeal.id }}</p>
        </div> -->
        <div class="meta-item">
          <p class="meta-label">申訴人</p>
          <p class="meta-value">{{ appeal.reporterName }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">被申訴對象</p>
          <p class="meta-value">{{ appeal.targetName }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">申訴類型</p>
          <p class="meta-value">{{ appeal.type }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">建立時間</p>
          <p class="meta-value">{{ formatDateTime(appeal.createdAt) }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">案件狀態</p>
          <p class="meta-value">
            {{ statusLabel(appeal.status) }}
          </p>
        </div>
      </div>

      <section class="detail-card">
        <h2>申訴內容</h2>
        <p class="detail-text">{{ appeal.content }}</p>

        <h2>證據截圖</h2>
        <div class="evidence-grid" v-if="appeal.images && appeal.images.length">
          <div class="evidence-item" v-for="image in appeal.images" :key="image">
            <img 
              :src="getEvidenceImageUrl(image)" 
              alt="申訴佐證圖片"
              @click="openImagePreview(image)"
            >
          </div>
        </div>

        <p v-else class="evidence-empty">
          此申訴未提供佐證圖片
        </p>
      </section>

      <section class="detail-card result-card">
        <h2>審核結果</h2>
        <!-- 尚未處理時 -->
        <p
          v-if="appeal.status === 'PENDING'"
          class="detail-text"
        >
          此申訴仍在審核中，請耐心等候管理員處理。
        </p>
        <!-- 申訴成立 -->
        <p
          v-else-if="appeal.status === 'CONFIRMED'"
          class="detail-text"
        >
          經管理員審核，本次申訴內容經查證屬實，平台將依相關管理規範，針對違規情形進行後續處置。感謝您的回報與協助，一同維護良好的平台交流環境。
        </p>
        <!-- 申訴不成立時，才顯示完整回覆說明 -->
        <p
          v-else-if="appeal.status === 'REJECTED'"
          class="detail-text"
        >
          {{ appeal.result || "經管理員審核，本次申訴內容經查證後，尚無足夠資訊認定有違反平台規範之情形，因此本次申訴不成立。感謝您的回報與理解，我們仍會持續維護平台良好的交流環境。" }}
        </p>
        <p class="result-date">審核時間：{{ formatDateTime(appeal.resultDate) }}</p>
        <p class="result-hint">如果對處置有疑問請聯絡我們，或撥打客服電話：0900-000-000</p>
      </section>
    </template>

    <div v-else class="appeal-not-found">
      <p>找不到這筆申訴紀錄</p>
      <RouterLink :to="{ name: 'member-appeal' }" class="btnFill">返回列表</RouterLink>
    </div>

    <!-- 申訴佐證圖片放大預覽燈箱 -->
    <div
      v-if="previewImage"
      class="image-preview image-preview-battle"
      @click="closeImagePreview"
    >
      <button
        type="button"
        class="image-preview-close"
        @click.stop="closeImagePreview"
      >
        <i class="fa-solid fa-xmark"></i>
      </button>

      <img
        :src="previewImage"
        alt="申訴佐證圖片預覽"
        @click.stop
      >
    </div>

  </section>
</template>

<script>
import { da } from 'element-plus/es/locales.mjs';

// import myAppealData from "@/data/myAppealData.js";

export default {
  name: "MyAppealDetail",

  data() {
    return {
      appeal: null,
      previewImage: null //佐證圖片預覽
    };
  },

  computed: {
    phpBaseUrl() {
      return (
        location.hostname === "localhost" ||
        location.hostname === "127.0.0.1"
          ? "http://localhost:8888/Spinix/php"
          : "/ckd101/g2/php"
      );
    },

    //透過網址路由，取得申訴類型與對應申訴編號資訊
    appealType() {
      return this.$route.params.type;
    },

    appealId() {
      return Number(this.$route.params.id);
    }
  },

  mounted() {
    this.fetchAppealDetail();
  },

  methods: {
    //串接約戰申訴、論壇申訴或交換申訴
    async fetchAppealDetail() {

      if (this.appealType === "battle") { 
        try {
          const response = await fetch(`${this.phpBaseUrl}/battle/battle_appeal_detail_get.php?appeal_id=${this.appealId}`,
            {
              credentials: "include"
            }
          );

          const data = await response.json();

          if (!response.ok || !data.success) {
            throw new Error(data.message || "取得約戰申訴詳情失敗");
          }

          console.log("約戰申訴詳情：", data.appeal);

          this.appeal = data.appeal;

        } catch (error) {

          console.error("取得約戰申訴詳情失敗：", error);
        }

        return;
      }

      if (this.appealType === "forum") {
        try {
          const response = await fetch(`${this.phpBaseUrl}/forum/forum_appeal_detail_get.php?appeal_id=${this.appealId}`,
            {
              credentials: "include"
            }
          );

          const data = await response.json();

          if (!response.ok || !data.success) {
            throw new Error(data.message || "取得論壇申訴詳情失敗");
          }
          this.appeal = data.appeal;

        } catch (error) {
        }

        return;
      }

      if (this.appealType === "exchange") {
        try{
          const response = await fetch(`${this.phpBaseUrl}/exchange/get_appeal_exc_detail.php?appeal_id=${this.appealId}`,{
            credentials:"include"
          });

          const data = await response.json();
          if(!response.ok || !data.success){
            throw new Error(data.message ||"取得申訴詳情失敗");
          }
          this.appeal = data.appeal;
          console.log("申訴詳情",data.appeal);
        }catch (error) {
          console.error("取得申訴詳情失敗：", error);
        }
        return
      }

    },

    formatDateTime(dateTime) { //日期格式化，不顯示秒數
      if (!dateTime) return "";

      return dateTime.replaceAll("-", "/").slice(0, 16);
    },

    getEvidenceImageUrl(imagePath) {
      return `${this.phpBaseUrl}/${imagePath}`;
    },

    openImagePreview(imagePath) { //開啟佐證圖的放大預覽燈箱
      this.previewImage = this.getEvidenceImageUrl(imagePath);
    },

    closeImagePreview() {
      this.previewImage = null;
    },

    statusLabel(status) {
      return status === "PENDING"
        ? "待處理"
        : "已結案";
    }

  }

};
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .appeal-detail {
    width: 100%;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .back-link {
    align-self: flex-start;
    display: flex;
    align-items: center;
    gap: 8px;

    font-size: map.get($fontSize, hint);
    color: map.get($color, neutral);
    text-decoration: none;
  }

  h1 {
    font-size: map.get($fontSize, h1);
    font-weight: 500;
    color: map.get($color, secondary2);
  }

  .meta-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 32px;

    padding: 16px;
    border: 1px solid map.get($color, hint);
    border-radius: 8px;
    background-color: map.get($color, white);
  }

  .meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .meta-label {
    font-size: map.get($fontSize, hint);
    color: map.get($color, secondary2);
  }

  .meta-value {
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);
  }

  .detail-card {
    width: 100%;
    padding: 32px 16px;
    border: 1px solid map.get($color, gray);
    border-radius: 8px;
    background-color: map.get($color, white);

    display: flex;
    flex-direction: column;
    gap: 16px;

    h2 {
      font-size: map.get($fontSize, h4);
      font-weight: 500;
      color: map.get($color, black);
    }
  }

  .detail-text {
    font-size: map.get($fontSize, default);
    color: map.get($color, black);
    line-height: 1.6;
  }

  .evidence-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .evidence-item {
    width: 120px;
    height: 90px;
    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    background-color: map.get($color, secondary);
    color: map.get($color, neutral);
    font-size: 12px;
  }

  .evidence-item img {
    width: 100%;
    height: 100%;

    object-fit: cover;
    display: block;
    cursor: pointer;

    transition: transform 0.24s ease;
  }

  .evidence-item:hover img {
    transform: scale(1.05);
  }

  // 圖片預覽燈箱
  .image-preview {
    position: fixed;
    inset: 0;
    z-index: 9999;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 32px;

    background-color: rgba(0, 0, 0, 0.82);
  }

  .image-preview img {
    max-width: 90vw;
    max-height: 85vh;

    object-fit: contain;
    border-radius: 8px;
  }

  .image-preview-close {
    position: absolute;
    top: 24px;
    right: 28px;

    padding: 8px;

    border: none;
    background: transparent;

    color: #ffffff;
    font-size: 30px;

    cursor: pointer;
  }

  .evidence-empty {
    padding: 16px;

    border: 1px dashed map.get($color, gray);
    border-radius: 6px;

    font-size: map.get($fontSize, hint);
    color: map.get($color, neutral);

    text-align: center;
  }

  .result-date {
    font-size: map.get($fontSize, hint);
    color: map.get($color, black);
  }

  .result-hint {
    padding-top: 4px;
    border-top: 1px solid map.get($color, neutral);

    font-size: 10px;
    color: map.get($color, neutral);
  }

  .appeal-not-found {
    padding: 60px 0;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;

    color: map.get($color, neutral);
  }

  // ====================== RWD調整 ============================

  @media screen and (max-width: 900px) {
    .meta-bar {
      gap: 16px;
    }
  }

  @media screen and (max-width: 576px) {
    .meta-bar {
      flex-direction: column;
      gap: 12px;
    }

    .detail-card {
      padding: 20px 16px;
    }
  }
</style>
