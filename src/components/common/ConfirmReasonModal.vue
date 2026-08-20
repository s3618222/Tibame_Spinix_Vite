<template>
  <div v-if="visible" class="confirm-reason-overlay" @click.self="handleCancel">
    <div class="confirm-reason-modal">
      <h3 class="confirm-reason-title">{{ title }}</h3>

      <div class="confirm-reason-field">
        <label for="confirmReasonText">
          <span class="required">*</span>
          說明原因
        </label>
        <textarea
          id="confirmReasonText"
          v-model.trim="reasonText"
          placeholder="請輸入原因說明..."
          @input="errorMsg = ''"
        ></textarea>
        <p v-if="errorMsg" class="confirm-reason-error">{{ errorMsg }}</p>
      </div>

      <div class="confirm-reason-actions">
        <button type="button" class="btn-cancel" @click="handleCancel">取消</button>
        <button type="button" class="btn-confirm" @click="handleConfirm">確認下架</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ConfirmReasonModal",

  props: {
    visible: {
      type: Boolean,
      default: false
    },
    title: {
      type: String,
      default: "請說明原因"
    }
  },

  emits: ["cancel", "confirm"],

  data() {
    return {
      reasonText: "",
      errorMsg: ""
    };
  },

  watch: {
    // 每次重新打開燈箱時，清空上一次殘留的輸入內容與錯誤訊息
    visible(newValue) {
      if (newValue) {
        this.reasonText = "";
        this.errorMsg = "";
      }
    }
  },

  methods: {
    handleCancel() {
      this.$emit("cancel");
    },

    handleConfirm() {
      if (!this.reasonText.trim()) {
        this.errorMsg = "請先輸入原因說明";
        return;
      }
      this.$emit("confirm", this.reasonText.trim());
    }
  }
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.confirm-reason-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 24px;
  background-color: rgba(20, 28, 38, 0.58);
}

.confirm-reason-modal {
  width: min(440px, 100%);
  padding: 28px;

  border-radius: 16px;
  background-color: map-get($color, white);
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
}

.confirm-reason-title {
  margin: 0 0 20px;
  color: map-get($color, secondary);
  font-size: map-get($fontSize, h4);
  font-weight: 700;
}

.confirm-reason-field {
  display: flex;
  flex-direction: column;
  gap: 8px;

  label {
    color: map-get($color, secondary);
    font-size: 14px;
    font-weight: 600;

    .required {
      color: map-get($color, error);
      margin-right: 2px;
    }
  }

  textarea {
    width: 100%;
    min-height: 120px;
    padding: 12px;
    resize: vertical;

    border: 1px solid map-get($color, warmGray);
    border-radius: 8px;
    outline: none;

    font: inherit;
    font-size: 14px;
    line-height: 1.5;
    color: map-get($color, secondary);

    &::placeholder {
      color: map-get($color, hint);
    }

    &:focus {
      border-color: map-get($color, error);
    }
  }
}

.confirm-reason-error {
  margin: 0;
  color: map-get($color, error);
  font-size: 13px;
}

.confirm-reason-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;

  button {
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
  }
}

.btn-cancel {
  border: 1px solid map-get($color, neutral);
  background-color: map-get($color, white);
  color: map-get($color, neutral);

  &:hover {
    background-color: map-get($color, tertiary);
  }
}

.btn-confirm {
  border: 1px solid map-get($color, error);
  background-color: map-get($color, error);
  color: map-get($color, white);

  &:hover {
    background-color: darken(#e63946, 8%);
  }
}
</style>
