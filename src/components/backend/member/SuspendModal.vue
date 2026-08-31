<template>
  <div
    class="modal-mask"
    @click.self="$emit('close')"
  >
    <div class="action-form">
      <div class="action-form__header action-form__header--suspend">
        <h2>停權處置</h2>
      </div>

      <div class="action-form__body">
        <!-- 停權範圍 -->
        <div class="form-block">
          <p class="form-block__title">停權範圍</p>

          <div class="suspension-list">
            <label
              v-for="scope in scopes"
              :key="scope.key"
              class="suspension"
            >
              <span class="suspension__left">
                <input
                  v-model="selectedScopes"
                  type="checkbox"
                  :value="scope.key"
                >
                <span class="suspension__label">{{ scope.label }}</span>
              </span>

              <MemberStatusPill
                :status="scope.status"
                :until="scope.until"
              />
            </label>
          </div>
        </div>

        <!-- 停權處置原因 -->
        <div class="form-block">
          <p class="form-block__title">停權處置原因</p>
          <p class="form-block__hint">※ 此停權原因將以系統通知形式發送給該會員</p>
          <textarea
            v-model="reason"
            maxlength="50"
            class="form-block__textarea"
            placeholder="輸入違規詳情（50 字內）"
          ></textarea>
          <span
            class="form-block__count"
            :class="{ 'form-block__count--max': reason.length >= 50 }"
          >{{ reason.length }} / 50</span>
        </div>

        <!-- 停權處分 -->
        <div class="form-block">
          <p class="form-block__title">停權處分</p>
          <div class="form-block__select">
            <select v-model="duration">
              <option value="7">7天</option>
              <option value="30">30天</option>
              <option value="90">90天</option>
              <option value="permanent">永久停權</option>
            </select>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
        </div>

        <!-- 按鈕 -->
        <div class="action-form__buttons">
          <button
            type="button"
            class="btn btn--primary"
            @click="handleSubmit"
          >
            確認送出
          </button>
          <button
            type="button"
            class="btn btn--outline"
            @click="$emit('close')"
          >
            取消
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref } from "vue";
  import MemberStatusPill from "./MemberStatusPill.vue";

  const props = defineProps({
    member: {
      type: Object,
      default: () => ({})
    }
  });

  const emit = defineEmits(["close", "submit"]);

  // 三種權限範圍與其目前狀態（讀自傳入的 member 假資料）
  const scopes = [
    {
      key: "battle",
      label: "約戰權限",
      status: props.member.battleStatus || "normal",
      until: props.member.battleUntil || ""
    },
    {
      key: "forum",
      label: "論壇權限",
      status: props.member.forumStatus || "normal",
      until: props.member.forumUntil || ""
    },
    {
      key: "market",
      label: "交換權限",
      status: props.member.marketStatus || "normal",
      until: props.member.marketUntil || ""
    }
  ];

  const selectedScopes = ref([]);
  const reason = ref("");
  const duration = ref("30");

  function handleSubmit() {
    // 尚未串接 API，先 emit 給父元件
    emit("submit", {
      memberId: props.member.id,
      scopes: selectedScopes.value,
      reason: reason.value,
      duration: duration.value
    });
    emit("close");
  }
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  .modal-mask {
    position: fixed;
    inset: 0;
    z-index: 1200;

    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;

    background-color: rgba(20, 28, 38, 0.35);
  }

  .action-form {
    width: 640px;
    max-width: 100%;
    max-height: 90vh;
    overflow-y: auto;

    display: flex;
    flex-direction: column;
    gap: 32px;
    padding-bottom: 32px;

    background-color: map-get($color, white);
    border-radius: 8px;
    box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.15);
  }

  .action-form__header {
    height: 131px;
    display: flex;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;

    h2 {
      color: map-get($color, white);
      font-size: map-get($fontSize, h2);
      font-weight: 500;
    }
  }

  .action-form__header--suspend {
    background-color: map-get($color, error);
  }

  .action-form__body {
    display: flex;
    flex-direction: column;
    gap: 32px;
    padding: 0 32px;
  }

  .form-block {
    display: flex;
    flex-direction: column;
    gap: 12px;

    &__title {
      font-size: map-get($fontSize, h4);
      font-weight: 500;
      color: map-get($color, secondary);
    }

    &__hint {
      font-size: map-get($fontSize, hint);
      color: map-get($color, neutral);
    }

    &__count {
      align-self: flex-end;
      font-size: map-get($fontSize, hint);
      color: map-get($color, hint);

      &--max {
        color: map-get($color, error);
      }
    }

    &__textarea {
      width: 100%;
      height: 160px;
      padding: 16px;
      resize: vertical;

      font-size: 16px;
      color: map-get($color, secondary);
      border: 1px solid map-get($color, secondary);

      &::placeholder {
        color: rgba(0, 0, 0, 0.45);
      }
    }

    &__select {
      position: relative;

      select {
        width: 100%;
        height: 52px;
        padding: 0 16px;
        appearance: none;

        font-size: 16px;
        color: map-get($color, secondary);
        background-color: map-get($color, white);
        border: 1px solid map-get($color, secondary);
      }

      i {
        position: absolute;
        top: 50%;
        right: 16px;
        transform: translateY(-50%);
        pointer-events: none;
        color: map-get($color, secondary);
      }
    }
  }

  .suspension-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .suspension {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;

    border: 1px solid map-get($color, hint);
    border-radius: 8px;
    cursor: pointer;

    &__left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    &__label {
      font-size: map-get($fontSize, default);
      color: map-get($color, secondary);
    }

    input[type="checkbox"] {
      width: 16px;
      height: 16px;
    }
  }

  .action-form__buttons {
    display: flex;
    gap: 16px;
  }

  .btn {
    flex: 1;
    padding: 16px;

    font-size: map-get($fontSize, h4);
    font-weight: 500;
    color: map-get($color, secondary);
    cursor: pointer;

    &--primary {
      background-color: map-get($color, primary);
    }

    &--outline {
      background-color: map-get($color, white);
      border: 1px solid map-get($color, secondary);
    }
  }
</style>
