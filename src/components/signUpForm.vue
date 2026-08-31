<template>
  <div class="signup-wrap">
    <form class="signup-card" @submit.prevent="handleSubmit">
      <div class="signup-header">
        <h1>會員註冊</h1>
      </div>

      <div class="signup-body">
        <!-- 帳號區 -->
        <div class="form-section">
          <div class="form-group">
            <label for="signUpAccount">帳號</label>
            <input
              id="signUpAccount"
              type="email"
              v-model="formData.account"
              placeholder="輸入帳號"
              required
            />
          </div>

          <div class="form-group">
            <label for="signUpPassword">密碼</label>
            <input
              id="signUpPassword"
              type="password"
              v-model="formData.password"
              placeholder="輸入密碼"
              required
            />
          </div>

          <div class="form-group">
            <label for="signUpConfirm">確認密碼</label>
            <input
              id="signUpConfirm"
              type="password"
              v-model="formData.confirmPassword"
              placeholder="確認密碼"
              required
            />
          </div>
        </div>

        <!-- 基本資料填寫 -->
        <div class="form-section">
          <h2 class="section-title">基本資料填寫</h2>

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
        </div>

        <!-- 緊急聯絡人資訊 -->
        <div class="emergency">
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
            />
          </div>

          <div class="form-group">
            <label for="signUpRepRelation">關係</label>
            <div class="select-wrap">
              <select id="signUpRepRelation" v-model="formData.repRelation">
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
            />
          </div>
        </div>

        <!-- 送出區 -->
        <div class="signup-submit">
          <label class="terms">
            <input type="checkbox" v-model="formData.agreeTerms" />
            <span>
              I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
            </span>
          </label>

          <button type="submit" class="btn-signup">
            註冊
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>

        <p class="signin-hint">
          已經有帳號？<a :href="`${baseUrl}signIn.html`">返回登入</a>
        </p>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: "signUpForm",

  data() {
    return {
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
    }
  },

  methods: {
    handleSubmit() {
      // TODO: 串接註冊 API（php/member/），含帳號唯一性、密碼一致、未滿18必填等驗證後導轉
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

/* 送出區 */
.signup-submit {
  display: flex;
  flex-direction: column;
  gap: 12px;
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

.btn-signup {
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
