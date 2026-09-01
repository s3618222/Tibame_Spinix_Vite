<template>
  <!-- 外圍遮罩；.self → 只有點擊遮罩本身時才關閉 -->
  <div class="reset-overlay" @click.self="closeModal">
    <div class="reset-modal">
      <button type="button" class="modal-close" @click="closeModal">×</button>

      <div class="modal-heading">
        <h2>重設密碼</h2>
        <p>為了安全，請先輸入目前的密碼再設定新密碼。</p>
      </div>

      <div class="reset-body">
        <div class="form-group">
          <label for="oldPassword">目前密碼</label>
          <div class="password-input">
            <input
              id="oldPassword"
              :type="show.old ? 'text' : 'password'"
              v-model="form.oldPassword"
              placeholder="輸入目前密碼"
            />
            <button
              type="button"
              class="password-toggle"
              @click="show.old = !show.old"
              :aria-label="show.old ? '隱藏密碼' : '顯示密碼'"
            >
              <i :class="show.old ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label for="newPassword">新密碼</label>
          <div class="password-input">
            <input
              id="newPassword"
              :type="show.new ? 'text' : 'password'"
              v-model="form.newPassword"
              placeholder="至少 6 碼"
            />
            <button
              type="button"
              class="password-toggle"
              @click="show.new = !show.new"
              :aria-label="show.new ? '隱藏密碼' : '顯示密碼'"
            >
              <i :class="show.new ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label for="confirmPassword">確認新密碼</label>
          <div class="password-input">
            <input
              id="confirmPassword"
              :type="show.confirm ? 'text' : 'password'"
              v-model="form.confirmPassword"
              placeholder="再次輸入新密碼"
            />
            <button
              type="button"
              class="password-toggle"
              @click="show.confirm = !show.confirm"
              :aria-label="show.confirm ? '隱藏密碼' : '顯示密碼'"
            >
              <i :class="show.confirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
        </div>

        <p v-if="errorMsg" class="error">{{ errorMsg }}</p>
      </div>

      <div class="modal-actions">
        <button type="button" class="btnNoFill" @click="closeModal">取消</button>
        <button type="button" class="btnFill" :disabled="submitting" @click="submit">
          {{ submitting ? "更新中…" : "確認更新" }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "ResetPasswordModal",

  emits: ["close", "success"],

  data() {
    return {
      form: {
        oldPassword: "",
        newPassword: "",
        confirmPassword: ""
      },
      show: {
        old: false,
        new: false,
        confirm: false
      },
      errorMsg: "",
      submitting: false
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
    }
  },

  methods: {
    closeModal() {
      this.$emit("close");
    },

    async submit() {
      this.errorMsg = "";

      // 客端預檢
      if (!this.form.oldPassword || !this.form.newPassword || !this.form.confirmPassword) {
        this.errorMsg = "請完整填寫密碼欄位";
        return;
      }
      if (this.form.newPassword !== this.form.confirmPassword) {
        this.errorMsg = "兩次新密碼不一致";
        return;
      }
      if (this.form.newPassword.length < 6) {
        this.errorMsg = "新密碼至少需 6 碼";
        return;
      }
      if (this.form.newPassword === this.form.oldPassword) {
        this.errorMsg = "新密碼不可與目前密碼相同";
        return;
      }

      this.submitting = true;
      try {
        const payload = new FormData();
        payload.append("oldPassword", this.form.oldPassword);
        payload.append("newPassword", this.form.newPassword);
        payload.append("confirmPassword", this.form.confirmPassword);

        const response = await fetch(`${this.phpBaseUrl}/member/member_password_update.php`, {
          method: "POST",
          credentials: "include",
          body: payload
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
          this.errorMsg = data.message || "密碼更新失敗";
          return;
        }

        this.$emit("success");
      } catch (error) {
        console.error("密碼更新失敗：", error);
        this.errorMsg = "無法連線至伺服器，請稍後再試";
      } finally {
        this.submitting = false;
      }
    }
  }
};
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .reset-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 24px;
    background-color: rgba(0, 0, 0, 0.24);
  }

  .reset-modal {
    position: relative;
    width: min(452px, 100%);
    padding: 28px;
    border: 1px solid #ffffff;
    border-radius: 12px;
    background-color: #f7f5f3;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
  }

  .modal-close {
    position: absolute;
    top: 18px;
    right: 24px;

    padding: 0;
    border: 0;
    background: transparent;

    color: #6b6b6b;
    font-size: 30px;
    line-height: 1;
    cursor: pointer;
  }

  .modal-heading {
    padding-right: 40px;

    h2 {
      margin: 0;
      color: #141c26;
      font-size: 22px;
      font-weight: 700;
    }

    p {
      margin: 12px 0 0;
      color: #64748b;
      font-size: 16px;
      line-height: 1.5;
    }
  }

  .reset-body {
    margin-top: 28px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;

    label {
      font-size: 14px;
      color: #141c26;
    }
  }

  .password-input {
    position: relative;

    input {
      width: 100%;
      padding: 12px;
      padding-right: 41px;

      border: 1px solid #b3b3b3;
      border-radius: 8px;
      background-color: #ffffff;

      color: #141c26;
      font: inherit;
      font-size: 14px;

      &::placeholder {
        color: #64748b;
      }

      &:focus {
        border-color: #fec96b;
        outline: none;
      }
    }

    .password-toggle {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);

      padding: 0;
      border: 0;
      background: transparent;

      font-size: 16px;
      color: #64748b;
      cursor: pointer;
    }
  }

  .error {
    margin: 0;
    font-size: 14px;
    color: map.get($color, error);
  }

  .modal-actions {
    margin-top: 32px;

    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 20px;
  }

  .btnFill,
  .btnNoFill {
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease;
  }

  .btnFill {
    border: 1px solid #fec96b;
    color: #141c26;
    background-color: #fec96b;

    &:hover {
      transform: translateY(-1px);
    }

    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }
  }

  .btnNoFill {
    border: 1px solid #141c26;
    color: #141c26;
    background-color: transparent;

    &:hover {
      background-color: rgba(20, 28, 38, 0.06);
    }
  }
</style>
