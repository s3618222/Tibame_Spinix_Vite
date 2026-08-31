<template>
  <div class="signup-wrap">
    <form class="signup-card" @submit.prevent="handleSubmit">
      <div class="signup-header">
        <h1>會員註冊</h1>
      </div>

      <div class="signup-body">
        <!-- 步驟進度 -->
        <div class="steps">
          <div class="steps__item" :class="{ 'is-active': currentStep >= 1 }">
            <span class="steps__num">1</span>
            <p class="steps__label">帳號密碼</p>
          </div>
          <div class="steps__line" :class="{ 'is-active': currentStep >= 2 }"></div>
          <div class="steps__item" :class="{ 'is-active': currentStep >= 2 }">
            <span class="steps__num">2</span>
            <p class="steps__label">個人資料</p>
          </div>
        </div>

        <!-- ===== Step 1：帳號 / 密碼 ===== -->
        <div v-if="currentStep === 1" class="form-section">
          <div class="form-group">
            <label for="signUpAccount">帳號</label>
            <input
              id="signUpAccount"
              type="email"
              v-model="formData.account"
              placeholder="輸入帳號（Email）"
            />
          </div>

          <div class="form-group">
            <label for="signUpPassword">密碼</label>
            <div class="password-input">
              <input
                id="signUpPassword"
                :type="showPassword ? 'text' : 'password'"
                v-model="formData.password"
                placeholder="輸入密碼"
              />
              <button
                type="button"
                class="password-toggle"
                @click="showPassword = !showPassword"
                :aria-label="showPassword ? '隱藏密碼' : '顯示密碼'"
              >
                <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
              </button>
            </div>
          </div>

          <div class="form-group">
            <label for="signUpConfirm">確認密碼</label>
            <div class="password-input">
              <input
                id="signUpConfirm"
                :type="showConfirm ? 'text' : 'password'"
                v-model="formData.confirmPassword"
                placeholder="確認密碼"
              />
              <button
                type="button"
                class="password-toggle"
                @click="showConfirm = !showConfirm"
                :aria-label="showConfirm ? '隱藏密碼' : '顯示密碼'"
              >
                <i :class="showConfirm ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
              </button>
            </div>
          </div>

          <p v-if="stepError" class="error">{{ stepError }}</p>

          <button type="button" class="btn-signup" :disabled="checking" @click="goNext">
            {{ checking ? "檢查中…" : "下一步" }}
            <i v-if="!checking" class="fa-solid fa-arrow-right"></i>
          </button>

          <p class="signin-hint">
            已經有帳號？<a :href="`${baseUrl}signIn.html`">返回登入</a>
          </p>
        </div>

        <!-- ===== Step 2：個人資料（+ 未滿18 緊急聯絡人）===== -->
        <div v-else class="form-section">
          <div class="form-group">
            <label for="signUpName">使用者名稱</label>
            <input
              id="signUpName"
              type="text"
              v-model="formData.name"
              placeholder="輸入使用者名稱"
              required
            />
          </div>

          <div class="form-group">
            <label for="signUpGender">性別</label>
            <div class="select-wrap">
              <select id="signUpGender" v-model="formData.gender" required>
                <option value="" disabled>選擇性別</option>
                <option value="MALE">男</option>
                <option value="FEMALE">女</option>
              </select>
              <i class="fa-solid fa-chevron-down"></i>
            </div>
          </div>

          <div class="form-group">
            <label for="signUpBirth">出生年月日</label>
            <input
              id="signUpBirth"
              type="date"
              v-model="formData.birth"
              required
            />
          </div>

          <!-- 年齡提醒 -->
          <div class="callout">
            <i class="fa-solid fa-circle-info"></i>
            <p>
              Spinix 為玩家交流平台。若您未滿 18 歲，建議參與任何線下約戰、交流或交換活動時，由家長、監護人或其他成年家屬陪同，以確保活動安全。
            </p>
          </div>

          <!-- 緊急聯絡人資訊：偵測到未滿 18 歲才展開 -->
          <transition name="reveal">
            <div v-if="isMinor" class="emergency">
              <h2 class="section-title">
                緊急聯絡人資訊 <span class="section-title__note">(未滿 18 歲必填)</span>
              </h2>

              <div class="form-group">
                <label for="signUpRepName">緊急聯絡人姓名</label>
                <input
                  id="signUpRepName"
                  type="text"
                  v-model="formData.repName"
                  placeholder="輸入緊急聯絡人姓名"
                  :required="isMinor"
                />
              </div>

              <div class="form-group">
                <label for="signUpRepRelation">關係</label>
                <div class="select-wrap">
                  <select
                    id="signUpRepRelation"
                    v-model="formData.repRelation"
                    :required="isMinor"
                  >
                    <option value="" disabled>選擇與緊急聯絡人之關係</option>
                    <option value="FATHER">父親</option>
                    <option value="MOTHER">母親</option>
                    <option value="OTHER">其他</option>
                  </select>
                  <i class="fa-solid fa-chevron-down"></i>
                </div>
              </div>

              <div class="form-group">
                <label for="signUpRepPhone">緊急聯絡人電話</label>
                <input
                  id="signUpRepPhone"
                  type="tel"
                  v-model="formData.repPhone"
                  placeholder="輸入緊急聯絡人電話"
                  :required="isMinor"
                />
              </div>
            </div>
          </transition>

          <!-- 條款 -->
          <label class="terms">
            <input type="checkbox" v-model="formData.agreeTerms" />
            <span>
              I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
            </span>
          </label>

          <p v-if="stepError" class="error">{{ stepError }}</p>

          <!-- 上一步 / 註冊 -->
          <div class="step-nav">
            <button type="button" class="btn-back" @click="goBack">
              <i class="fa-solid fa-arrow-left"></i>
              上一步
            </button>
            <button type="submit" class="btn-signup">
              註冊
              <i class="fa-solid fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: "signUpForm",

  data() {
    return {
      currentStep: 1,
      stepError: "",
      checking: false,
      showPassword: false,
      showConfirm: false,
      formData: {
        account: "",
        password: "",
        confirmPassword: "",
        name: "",
        gender: "",
        birth: "",
        repName: "",
        repRelation: "",
        repPhone: "",
        agreeTerms: false
      }
    };
  },

  computed: {
    baseUrl() {
      return import.meta.env.BASE_URL;
    },

    // PHP API 路徑（比照 signInForm.vue）
    phpBaseUrl() {
      return (
        location.hostname === "localhost" ||
        location.hostname === "127.0.0.1"
      )
        ? "http://localhost:8888/Spinix/php"
        : "/ckd101/g2/php";
    },

    // 依生日換算是否未滿 18 歲（觸發緊急聯絡人區塊）
    isMinor() {
      if (!this.formData.birth) return false;

      const birth = new Date(this.formData.birth);
      if (Number.isNaN(birth.getTime())) return false;

      const today = new Date();
      let age = today.getFullYear() - birth.getFullYear();
      const monthDiff = today.getMonth() - birth.getMonth();
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
      }
      return age < 18;
    }
  },

  methods: {
    // Step 1 → Step 2：前端同步預檢（email 格式、密碼一致）+ 向後端查帳號是否已被註冊
    async goNext() {
      this.stepError = "";

      if (!this.formData.account || !this.formData.password || !this.formData.confirmPassword) {
        this.stepError = "請填寫帳號與密碼";
        return;
      }

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailPattern.test(this.formData.account)) {
        this.stepError = "帳號需為有效的 Email";
        return;
      }

      if (this.formData.password !== this.formData.confirmPassword) {
        this.stepError = "兩次輸入的密碼不一致";
        return;
      }

      // 帳號重複檢查（後端仍會在最後送出時再驗一次）
      this.checking = true;
      try {
        const res = await fetch(
          `${this.phpBaseUrl}/member/account_check_get.php?account=${encodeURIComponent(this.formData.account)}`,
          { credentials: "include" }
        );
        const data = await res.json();

        if (data.success && data.available) {
          this.currentStep = 2;
        } else if (data.success && !data.available) {
          this.stepError = "此帳號已被註冊";
        } else {
          this.stepError = data.message || "帳號檢查失敗";
        }
      } catch {
        this.stepError = "無法連線至伺服器，請稍後再試";
      } finally {
        this.checking = false;
      }
    },

    goBack() {
      this.stepError = "";
      this.currentStep = 1;
    },

    handleSubmit() {
      this.stepError = "";

      if (!this.formData.agreeTerms) {
        this.stepError = "請先同意服務條款與隱私權政策";
        return;
      }

      const payload = new FormData();
      payload.append("account", this.formData.account);
      payload.append("password", this.formData.password);
      payload.append("confirmPassword", this.formData.confirmPassword);
      payload.append("name", this.formData.name);
      payload.append("gender", this.formData.gender);
      payload.append("birth", this.formData.birth);
      payload.append("repName", this.formData.repName);
      payload.append("repRelation", this.formData.repRelation);
      payload.append("repPhone", this.formData.repPhone);

      fetch(`${this.phpBaseUrl}/member/signUp_post.php`, {
        method: "POST",
        body: payload,
        credentials: "include"
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            alert("註冊成功，請登入");
            window.location.href = `${this.baseUrl}signIn.html`;
          } else {
            this.stepError = data.message || "註冊失敗";
          }
        })
        .catch(() => {
          this.stepError = "無法連線至伺服器，請稍後再試";
        });
    }
  }
};
</script>

<style lang="scss" scoped>
@use "sass:map";
@use "@/assets/scss/var" as *;

.signup-wrap {
  flex: 1;
  width: 100%;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 32px 20px;
}

.signup-card {
  width: min(512px, 100%);
  display: flex;
  flex-direction: column;
  background-color: map.get($color, white);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.signup-header {
  padding: 24px;
  background-color: map.get($color, secondary);
  text-align: center;

  h1 {
    font-size: map.get($fontSize, h2);
    font-weight: 500;
    color: map.get($color, white);
  }
}

.signup-body {
  padding: 24px 48px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* 步驟進度 */
.steps {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;

  &__item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
  }

  &__num {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: map.get($color, gray);
    color: map.get($color, white);
    font-size: map.get($fontSize, default);
    font-weight: 500;
    transition: background-color 0.2s ease;
  }

  &__label {
    font-size: map.get($fontSize, hint);
    color: map.get($color, hint);
    transition: color 0.2s ease;
  }

  &__line {
    width: 64px;
    height: 2px;
    margin-bottom: 22px; // 對齊圓點中心
    background-color: map.get($color, gray);
    transition: background-color 0.2s ease;

    &.is-active {
      background-color: map.get($color, primary);
    }
  }

  &__item.is-active {
    .steps__num {
      background-color: map.get($color, primary);
      color: map.get($color, secondary);
    }

    .steps__label {
      color: map.get($color, secondary);
    }
  }
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.section-title {
  font-size: 20px;
  font-weight: 500;
  color: map.get($color, secondary);

  &__note {
    font-size: map.get($fontSize, default);
    font-weight: 400;
    color: map.get($color, black);
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;

  label {
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);
  }

  input {
    width: 100%;
    padding: 13px;
    border: 1px solid map.get($color, secondary);
    border-radius: 4px;
    outline: none;
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);

    &::placeholder {
      color: map.get($color, hint);
    }

    &:focus {
      border-color: map.get($color, secondary2);
    }
  }
}

/* 密碼欄位（帶眼睛切換） */
.password-input {
  position: relative;

  input {
    padding-right: 41px; // 留給眼睛 icon 的空間
  }

  .password-toggle {
    position: absolute;
    top: 50%;
    right: 13px;
    transform: translateY(-50%);
    font-size: 16px;
    color: map.get($color, neutral);
    cursor: pointer;
  }
}

/* 下拉選單（帶 chevron） */
.select-wrap {
  position: relative;

  select {
    width: 100%;
    padding: 13px;
    appearance: none;
    -webkit-appearance: none;
    border: 1px solid map.get($color, secondary);
    border-radius: 4px;
    outline: none;
    background-color: map.get($color, white);
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);
    cursor: pointer;

    &:focus {
      border-color: map.get($color, secondary2);
    }

    &:invalid {
      color: map.get($color, hint); // 尚未選擇時顯示 hint 色
    }
  }

  i {
    position: absolute;
    top: 50%;
    right: 13px;
    transform: translateY(-50%);
    pointer-events: none;
    color: map.get($color, secondary);
  }
}

/* 年齡提醒 callout */
.callout {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background-color: map.get($color, neutral);

  i {
    flex-shrink: 0;
    color: map.get($color, white);
  }

  p {
    font-size: map.get($fontSize, hint);
    line-height: 1.6;
    color: map.get($color, white);
  }
}

/* 緊急聯絡人外框 */
.emergency {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding: 25px;
  border: 1px solid map.get($color, black);
}

/* 緊急聯絡人展開過渡 */
.reveal-enter-active,
.reveal-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.reveal-enter-from,
.reveal-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.terms {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;

  input[type="checkbox"] {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
  }

  span {
    font-size: map.get($fontSize, hint);
    color: map.get($color, secondary);
  }

  a {
    color: map.get($color, secondary);
    text-decoration: underline;
  }
}

/* 上一步 / 註冊 按鈕列 */
.step-nav {
  display: flex;
  gap: 12px;
}

.btn-back {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 20px;

  border: 1px solid map.get($color, secondary);
  background-color: map.get($color, white);
  color: map.get($color, secondary);
  font-size: map.get($fontSize, h4);
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;

  &:hover {
    background-color: map.get($color, gray);
  }
}

.btn-signup {
  flex: 1;
  width: 100%;
  padding: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;

  background-color: map.get($color, primary);
  color: map.get($color, secondary);
  font-size: map.get($fontSize, h4);
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;

  &:hover {
    background-color: darken(map.get($color, primary), 8%);
  }

  &:disabled {
    opacity: 0.7;
    cursor: not-allowed;
  }
}

.error {
  text-align: center;
  font-size: map.get($fontSize, hint);
  color: map.get($color, error);
}

.signin-hint {
  text-align: center;
  font-size: map.get($fontSize, default);
  color: map.get($color, secondary);

  a {
    color: map.get($color, secondary2);

    &:hover {
      text-decoration: underline;
    }
  }
}
</style>
