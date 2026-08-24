<template>
  <div
    class="modal-mask"
    @click.self="$emit('close')"
  >
    <div class="admin-form">
      <div class="admin-form__header admin-form__header--reset">
        <h2>重設密碼</h2>
      </div>

      <div class="admin-form__body">
        <!-- 密碼 -->
        <div class="form-field">
          <label class="form-field__label">密碼</label>
          <div class="form-field__password">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              class="form-field__input"
              placeholder="輸入密碼"
            >
            <button
              type="button"
              class="form-field__eye"
              @click="showPassword = !showPassword"
            >
              <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- 再次輸入密碼 -->
        <div class="form-field">
          <label class="form-field__label">再次輸入密碼</label>
          <div class="form-field__password">
            <input
              v-model="confirmPassword"
              :type="showConfirm ? 'text' : 'password'"
              class="form-field__input"
              placeholder="再次輸入密碼"
            >
            <button
              type="button"
              class="form-field__eye"
              @click="showConfirm = !showConfirm"
            >
              <i :class="showConfirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
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

  const password = ref("");
  const confirmPassword = ref("");
  const showPassword = ref(false);
  const showConfirm = ref(false);

  function handleSubmit() {
    // 尚未串接 API，先 emit 給父元件
    emit("submit", {
      adminId: props.admin.id,
      password: password.value,
      confirmPassword: confirmPassword.value
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

  .admin-form__header--reset {
    background-color: map-get($color, error);
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

      &::placeholder {
        color: map-get($color, hint);
      }
    }

    &__password {
      position: relative;

      .form-field__input {
        padding-right: 44px;
      }
    }

    &__eye {
      position: absolute;
      right: 13px;
      top: 50%;
      transform: translateY(-50%);

      color: map-get($color, neutral);
      cursor: pointer;
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
