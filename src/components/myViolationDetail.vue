<template>
  <section class="violation-detail">
    <RouterLink :to="{ name: 'member-violation' }" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      返回列表
    </RouterLink>

    <template v-if="violation">
      <h1>違規紀錄詳情</h1>

      <div class="meta-bar">
        <div class="meta-item">
          <p class="meta-label">申訴編號</p>
          <p class="meta-value">#{{ violation.id }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">被申訴對象</p>
          <p class="meta-value">{{ violation.target }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">申訴類型</p>
          <p class="meta-value">{{ violation.type }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">處份記錄</p>
          <p class="meta-value">{{ violation.punishment }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">建立時間</p>
          <p class="meta-value">{{ violation.reportedAt }}</p>
        </div>
      </div>

      <section class="detail-card">
        <h2>{{ violation.type }}</h2>
        <p class="detail-text">{{ violation.content }}</p>

        <h2>證據截圖</h2>
        <div class="evidence-grid">
          <div class="evidence-item" v-for="n in violation.images" :key="n">IMG</div>
        </div>
      </section>

      <section class="detail-card result-card">
        <h2>處份結果</h2>
        <p class="detail-text">{{ violation.result }}</p>
        <p class="result-date">回復時間：{{ violation.resultDate }}</p>
        <p class="result-hint">如果對處置有疑問請聯絡我們，或撥打客服電話：0900-000-000</p>
      </section>
    </template>

    <div v-else class="violation-not-found">
      <p>找不到這筆違規紀錄</p>
      <RouterLink :to="{ name: 'member-violation' }" class="btnFill">返回列表</RouterLink>
    </div>
  </section>
</template>

<script>
import myViolationData from "@/data/myViolationData.js";

export default {
  name: "MyViolationDetail",

  computed: {
    violation() {
      return myViolationData.find((item) => item.id === this.$route.params.id) || null;
    }
  }
};
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .violation-detail {
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

  .violation-not-found {
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
