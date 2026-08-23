<template>
  <div class="matching-content">
    <div class="matching-message">
      <i class="fa-regular fa-clock"></i>

      <div class="matching-text">
        <p class="matching-title">目前尚未收到參加申請</p>
        <p class="matching-desc">
          邀約仍在配對中，請等待其他玩家申請加入。
        </p>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    name: "BattleMatchingContent",

    props: {
      battle: {
        type: Object,
        required: true
      }
    }
  };
</script>

<style lang="scss" scoped>
  .matching-content {
    width: 100%;
    display: flex;
    justify-content: center;
  }

  .matching-message {
    width: fit-content;
    max-width: 100%;
    padding: 12px 14px;

    display: flex;
    align-items: center;
    gap: 12px;

    border-radius: 10px;
    background-color: #fff7e5;

    color: #575555;

    i {
      flex-shrink: 0;
      color: #d89a2b;
      font-size: 20px;
    }
  }

  .matching-text {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .matching-title {
    margin: 0;

    color: #141c26;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.5;
  }

  .matching-desc {
    margin: 0;

    color: #64748b;
    font-size: 15px;
    line-height: 1.5;
  }

  // ===================== RWD調整 ==========================
  @media screen and (max-width: 900px) {
    .matching-content {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }

    .matching-message {
      width: 100%;
      align-items: flex-start;
    }
  }
</style>