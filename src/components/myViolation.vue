<template>
  <section class="my-violation">
    <div class="violation-header">
      <h1>違規紀錄</h1>
      <p>依據網站的安全準則，針對違規的會員，給與懲處。</p>
    </div>

    <template v-if="violationRecords.length">
      <div class="violation-table-wrap">
        <div class="violation-table">
          <div class="table-row table-head">
            <p>申訴編號</p>
            <p>類型</p>
            <p>處分紀錄</p>
            <p>檢舉時間</p>
            <p></p>
          </div>

          <div class="table-row" v-for="violation in violationRecords" :key="violation.id">
            <p>#{{ violation.id }}</p>
            <p>{{ violation.type }}</p>
            <p>
              <span class="punishment-badge">{{ violation.punishment }}</span>
            </p>
            <p>{{ violation.reportedAt }}</p>
            <RouterLink
              :to="{ name: 'member-violation-detail', params: { id: violation.id } }"
              class="detail-btn"
            >
              查看詳情
            </RouterLink>
          </div>
        </div>
      </div>

      <div class="violation-notice">
        <p class="notice-title">提醒</p>
        <p class="notice-text">
          為保護雙方隱私，申訴人資訊將部分遮蔽；惡意檢舉經查證屬實者，將對申訴人帳號進行相對應處置。
        </p>
      </div>
    </template>

    <div v-else class="violation-empty">
      <i class="fa-solid fa-shield-halved"></i>
      <p>目前沒有違規紀錄</p>
    </div>
  </section>
</template>

<script>
import myViolationData from "@/data/myViolationData.js";

export default {
  name: "MyViolation",

  data() {
    return {
      violationRecords: myViolationData //違規紀錄假資料
    };
  }
};
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .my-violation {
    width: 100%;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .violation-header {
    display: flex;
    flex-direction: column;
    gap: 12px;

    h1 {
      font-size: map.get($fontSize, h1);
      font-weight: 500;
      color: map.get($color, secondary2);
    }

    p {
      font-size: map.get($fontSize, default);
      color: map.get($color, secondary);
    }
  }

  .violation-table-wrap {
    width: 100%;
    overflow-x: auto;
  }

  .violation-table {
    width: 100%;
    min-width: 700px;
    border: 1px solid map.get($color, secondary);
    border-radius: 8px;
    overflow: hidden;
  }

  .table-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;

    border-bottom: 1px solid map.get($color, gray);

    &:last-child {
      border-bottom: 0;
    }

    > p {
      flex: 1 0 0;
      min-width: 0;
      font-size: map.get($fontSize, hint);
      color: map.get($color, secondary);
    }
  }

  .table-head {
    background-color: map.get($color, neutral);
    border-bottom: 0;

    > p {
      color: map.get($color, white);
      font-weight: 600;
      font-size: 12px;
    }
  }

  .punishment-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;

    background-color: map.get($color, lightGreen);
    color: map.get($color, secondary);
  }

  .detail-btn {
    flex: 1 0 0;
    min-width: 0;

    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 12px;
    border-radius: 6px;

    background-color: map.get($color, primary);
    color: map.get($color, secondary);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
  }

  .violation-notice {
    width: 100%;
    padding: 16px;
    border: 1px solid map.get($color, neutral);
    border-radius: 8px;
    background-color: map.get($color, white);

    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .notice-title {
    font-size: 13px;
    font-weight: 600;
    color: map.get($color, secondary2);
  }

  .notice-text {
    font-size: 12px;
    color: map.get($color, secondary);
  }

  .violation-empty {
    width: 100%;
    min-height: 200px;
    padding: 40px 24px;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;

    border: 1px dashed rgba(242, 155, 0, 0.35);
    border-radius: 20px;
    background-color: #fffaf2;

    text-align: center;

    i {
      color: map.get($color, secondary2);
      font-size: 34px;
    }

    p {
      color: map.get($color, neutral);
      font-size: map.get($fontSize, default);
    }
  }
</style>
