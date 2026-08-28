<template>
  <div class="back-auth">
    <!-- 後台風格頂列：僅 logo + 標題，無公開導覽 -->
    <header class="back-auth-header">
      <img class="logo" src="/spinix_logo.png" alt="Spinix Logo" />
      <span class="title">後台管理</span>
    </header>

    <main class="back-auth-main">
      <form class="back-signin-card" @submit.prevent="handleSubmit">
        <div class="back-signin-header">
          <h1>SPINIX</h1>
          <p class="subtitle">管理員後台登入</p>
          <p class="hint">請輸入管理員帳號以及密碼</p>
        </div>

        <div class="back-signin-body">
          <div class="form-group">
            <label for="backSignInAccount">帳號</label>
            <input
              id="backSignInAccount"
              type="text"
              v-model="formData.account"
              placeholder="輸入帳號"
              required
            />
          </div>

          <div class="form-group">
            <label for="backSignInPassword">密碼</label>
            <input
              id="backSignInPassword"
              type="password"
              v-model="formData.password"
              placeholder="輸入密碼"
              required
            />
          </div>

          <button type="submit" class="btn-login">
            登入
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>
      </form>
    </main>

    <!-- 後台風格版權列 -->
    <footer class="back-auth-footer">
      <p>© 2026 Spinix . All rights reserved.</p>
    </footer>
  </div>
</template>

<script>
export default {
  name: "backSignInForm",

  data() {
    return {
      formData: {
        account: "",
        password: ""
      }
    };
  },

  methods: {
    handleSubmit() {
      // TODO: 串接後台管理員登入 API（php/member/），比對 admin 資料表後導轉至 backMember.html
    }
  }
};
</script>

<style lang="scss" scoped>
@use "sass:map";
@use "@/assets/scss/var" as *;

.back-auth {
  flex: 1;
  display: flex;
  flex-direction: column;
}

/* 頂列 */
.back-auth-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 8px 24px;
  background-color: map.get($color, secondary);

  .logo {
    height: 56px;
    width: auto;
  }

  .title {
    font-size: map.get($fontSize, h4);
    font-weight: 500;
    color: map.get($color, white);
  }
}

/* 主區：置中卡片 */
.back-auth-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.back-signin-card {
  width: min(455px, 100%);
  display: flex;
  flex-direction: column;
  gap: 32px;
  padding: 48px;
  background-color: map.get($color, white);
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.back-signin-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 7px;
  text-align: center;

  h1 {
    font-size: map.get($fontSize, h1);
    font-weight: 500;
    color: map.get($color, secondary);
  }

  .subtitle {
    font-size: map.get($fontSize, h4);
    font-weight: 500;
    color: map.get($color, secondary);
  }

  .hint {
    font-size: map.get($fontSize, default);
    color: map.get($color, hint);
  }
}

.back-signin-body {
  display: flex;
  flex-direction: column;
  gap: 24px;
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

.btn-login {
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

/* 版權列 */
.back-auth-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 56px;
  padding: 0 16px;
  background-color: map.get($color, secondary);

  p {
    font-size: map.get($fontSize, hint);
    color: map.get($color, white);
  }
}
</style>
