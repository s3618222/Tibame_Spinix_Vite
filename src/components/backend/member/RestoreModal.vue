<template>
  <div
    class="modal-mask"
    @click.self="$emit('close')"
  >
    <div class="action-form">
      <div class="action-form__header action-form__header--restore">
        <h2>恢復權限</h2>
      </div>

      <div class="action-form__body">
        <!-- 選擇要恢復的權限 -->
        <div class="form-block">
          <p class="form-block__title">選擇要恢復的權限</p>

          <div class="suspension-list">
            <p
              v-if="restrictedScopes.length === 0"
              class="empty-hint"
            >
              此會員目前沒有受限的權限
            </p>

            <label
              v-for="scope in restrictedScopes"
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
                status="restricted"
                :until="scope.until"
              />
            </label>
          </div>
        </div>

        <!-- 按鈕 -->
        <div class="action-form__buttons">
          <button
            type="button"
            class="btn btn--primary"
            @click="handleSubmit"
          >
            確認恢復
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
  import { ref, computed } from "vue";
  import MemberStatusPill from "./MemberStatusPill.vue";

  const props = defineProps({
    member: {
      type: Object,
      default: () => ({})
    }
  });

  const emit = defineEmits(["close", "submit"]);

  // 只列出目前「受限」的權限供恢復
  const restrictedScopes = computed(() => {
    const all = [
      {
        key: "battle",
        label: "約戰權限",
        status: props.member.battleStatus,
        until: props.member.battleUntil || ""
      },
      {
        key: "forum",
        label: "論壇權限",
        status: props.member.forumStatus,
        until: props.member.forumUntil || ""
      },
      {
        key: "market",
        label: "交換權限",
        status: props.member.marketStatus,
        until: props.member.marketUntil || ""
      }
    ];
    return all.filter((scope) => scope.status === "restricted");
  });

  const selectedScopes = ref([]);

  function handleSubmit() {
    // 尚未串接 API，先 emit 給父元件
    emit("submit", {
      memberId: props.member.id,
      scopes: selectedScopes.value
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
    align-items: center;
    justify-content: center;

    h2 {
      color: map-get($color, white);
      font-size: map-get($fontSize, h2);
      font-weight: 500;
    }
  }

  .action-form__header--restore {
    background-color: map-get($color, olive);
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
  }

  .suspension-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .empty-hint {
    font-size: map-get($fontSize, default);
    color: map-get($color, hint);
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
