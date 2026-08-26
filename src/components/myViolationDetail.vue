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
          <p class="meta-label">被申訴人</p>
          <p class="meta-value">{{ violation.respondentName }}</p>
        </div>

        <div class="meta-item">
          <p class="meta-label">違規類型</p>
          <p class="meta-value">{{ violation.type }}</p>
        </div>

        <div class="meta-item">
          <p class="meta-label">處理時間</p>
          <p class="meta-value">{{ formatDateTime(violation.respondedAt) }}</p>
        </div>
      </div>

      <section class="detail-card result-card">
        <h2>違規處分說明</h2>
        <p class="detail-text">{{ violation.respondedText }}</p>
        <p class="result-hint">如果對處置有疑問請聯絡我們 (contact@spinix.com.tw)，或撥打客服電話：0900-000-000</p>
      </section>
    </template>

    <div v-else class="violation-not-found">
      <p>找不到這筆違規紀錄</p>
      <RouterLink :to="{ name: 'member-violation' }" class="btnFill">返回列表</RouterLink>
    </div>
  </section>
</template>

<script>
// import myViolationData from "@/data/myViolationData.js";

export default {
  name: "MyViolationDetail",

  data () {
    return {
      violation: null //儲存後端回傳的單筆違規詳情
    }
  },

  computed: {
    phpBaseUrl() { //判斷目前php的執行環境，調整網址前綴
      return (
        location.hostname === "localhost" ||
        location.hostname === "127.0.0.1"
          ? "http://localhost:8888/Spinix/php"
          : "/ckd101/g2/php"
      );
    },
  },

  methods: {
    async fetchViolationDetail () { //串接違規詳情API
      try {
        //從vue router的網址參數取得違規類型與該筆申訴ID資料
        const type = this.$route.params.type;
        const id = this.$route.params.id;

        const response = await fetch(`${this.phpBaseUrl}/member/my_violation_detail_get.php?type=${type}&id=${id}`, {
          credentials: "include"
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          console.error(data.message);
          return;

        }
          
        //將傳回資料存入violation變數
        this.violation = data.violation;

      } catch (error) {
        console.error("取得違規詳情失敗：", error);
      }
    },

    formatDateTime(dateTime) { //日期格式化
      if (!dateTime) return "-";

      return new Date(dateTime).toLocaleString("zh-TW", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false
      });
    }
  },

  mounted() {
    this.fetchViolationDetail();
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

  // .evidence-grid {
  //   display: flex;
  //   flex-wrap: wrap;
  //   gap: 8px;
  // }

  // .evidence-item {
  //   width: 120px;
  //   height: 90px;
  //   flex-shrink: 0;

  //   display: flex;
  //   align-items: center;
  //   justify-content: center;

  //   background-color: map.get($color, secondary);
  //   color: map.get($color, neutral);
  //   font-size: 12px;
  // }

  .result-date {
    font-size: map.get($fontSize, hint);
    color: map.get($color, black);
  }

  .result-hint {
    margin-top: 8px;
    padding-top: 4px;
    border-top: 1px solid map.get($color, neutral);

    font-size: 12px;
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
