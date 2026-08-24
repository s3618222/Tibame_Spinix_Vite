<template>
  <div
    class="modal-mask"
    @click.self="$emit('close')"
  >
    <div class="admin-form">
      <div class="admin-form__header admin-form__header--edit">
        <h2>編輯</h2>
      </div>

      <div class="admin-form__body">
        <!-- 管理員名稱 -->
        <div class="form-field">
          <label class="form-field__label">管理員名稱</label>
          <input
            v-model="name"
            type="text"
            class="form-field__input"
          >
        </div>

        <!-- 管理員狀態 -->
        <div class="form-field">
          <label class="form-field__label">管理員狀態</label>
          <div class="form-field__select">
            <select v-model="state">
              <option value="在職">在職</option>
              <option value="離職">離職</option>
            </select>
            <i class="fa-solid fa-chevron-down"></i>
          </div>
        </div>

        <!-- 按鈕 -->
        <button
          type="button"
          class="admin-form__submit"
          @click="handleSubmit"
        >
          確認送出
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref } from "vue";

  const props = defineProps({
    admin: {
      type: Object,
      default: () => ({})
    }
  });

  const emit = defineEmits(["close", "submit"]);

  const name = ref(props.admin.name || "");
  const state = ref(props.admin.state || "在職");

  function handleSubmit() {
    // 尚未串接 API，先 emit 給父元件
    emit("submit", {
      adminId: props.admin.id,
      name: name.value,
      state: state.value
    });
    emit("close");
  }
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  .modal-mask {
    position: fixed;
    inset: 0;
    z-index: 1000;

    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;

    background-color: rgba(20, 28, 38, 0.35);
  }

  .admin-form {
    width: 512px;
    max-width: 100%;
    max-height: 90vh;
    overflow: hidden;

    background-color: map-get($color, white);
    border-radius: 8px;
    box-shadow: 0 4px 16px 0 rgba(0, 0, 0, 0.15);
  }

  .admin-form__header {
    height: 84px;
    display: flex;
    align-items: center;
    justify-content: center;

    h2 {
      color: map-get($color, white);
      font-size: map-get($fontSize, h2);
      font-weight: 500;
    }
  }

  .admin-form__header--edit {
    background-color: map-get($color, olive);
  }

  .admin-form__body {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 48px;
  }

  .form-field {
    display: flex;
    flex-direction: column;
    gap: 4px;

    &__label {
      font-size: map-get($fontSize, default);
      font-weight: 500;
      color: map-get($color, secondary);
    }

    &__input {
      width: 100%;
      height: 52px;
      padding: 0 13px;

      font-size: 16px;
      color: map-get($color, secondary);
      background-color: map-get($color, white);
      border: 1px solid map-get($color, gray);
      border-radius: 4px;
      outline: none;

      transition: border-color 0.24s;

      &:focus {
        border-color: map-get($color, secondary2);
      }
    }

    &__select {
      position: relative;

      select {
        width: 100%;
        height: 52px;
        padding: 0 40px 0 13px;
        appearance: none;
        -webkit-appearance: none;

        font-size: 16px;
        color: map-get($color, secondary);
        background-color: map-get($color, white);
        border: 1px solid map-get($color, gray);
        border-radius: 4px;
        outline: none;
        cursor: pointer;

        transition: border-color 0.24s;

        &:focus {
          border-color: map-get($color, secondary2);
        }
      }

      i {
        position: absolute;
        right: 13px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: map-get($color, secondary);
        font-size: 14px;
      }
    }
  }

  .admin-form__submit {
    width: 100%;
    padding: 14px;

    font-size: map-get($fontSize, default);
    font-weight: 500;
    color: map-get($color, secondary);
    background-color: map-get($color, primary);
    border-radius: 4px;
    cursor: pointer;
  }
</style>
