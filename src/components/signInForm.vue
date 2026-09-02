<template>
  <div class="signin-wrap">
    <form class="signin-card" @submit.prevent="handleSubmit">
      <div class="signin-header">
        <h1>會員登入</h1>
      </div>

      <div class="signin-body">
        <div class="form-group">
          <label for="signInAccount">帳號</label>
          <input
            id="signInAccount"
            type="text"
            v-model="formData.account"
            placeholder="輸入帳號"
            required
          />
        </div>

        <div class="form-group">
          <label for="signInPassword">密碼</label>
          <div class="password-input">
            <input
              id="signInPassword"
              :type="showPassword ? 'text' : 'password'"
              v-model="formData.password"
              placeholder="請輸入密碼"
              required
            />
            <button
              type="button"
              class="password-toggle"
              @click="togglePassword"
              :aria-label="showPassword ? '隱藏密碼' : '顯示密碼'"
            >
              <i :class="showPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-login">
          登入
          <i class="fa-solid fa-arrow-right"></i>
        </button>

        <p class="forgot-password">忘記密碼</p>

        <p class="signup-hint">
          還沒有帳號？<a :href="`${baseUrl}signUp.html`">立即註冊</a>
        </p>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  name: "signInForm",

  data() {
    return {
      formData: {
        account: "",
        password: ""
      },
      showPassword: false
    };
  },

  computed: {
    baseUrl() {
      return import.meta.env.BASE_URL;
    },

    // PHP API 路徑
    phpBaseUrl() {
      return (
        location.hostname === "localhost" ||
        location.hostname === "127.0.0.1"
      )
        ? "http://localhost:8888/Spinix/php"
        : "/ckd101/g2/php";
    }
  },

  methods: {
    togglePassword() {
      this.showPassword = !this.showPassword;
    },

    handleSubmit() {
      const formData = new FormData();
      formData.append("account", this.formData.account);
      formData.append("password", this.formData.password);

      console.log([...formData.entries()]);

      fetch(`${this.phpBaseUrl}/member/signIn_post.php`, {
        method: "POST",
        body: formData,
        credentials: "include"
      }).then(res => res.json()).then(data => {
        if(data.success) {
          alert(data.message); //登入成功
          window.location.href = `${this.baseUrl}homepage.html`;
        } else {
          alert(data.message); //帳號或密碼錯誤
        }
      });
    }
  }
};
</script>

<style lang="scss" scoped>
@use "sass:map";
@use "@/assets/scss/var" as *;

.signin-wrap {
  flex: 1;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.signin-card {
  width: min(512px, 100%);
  display: flex;
  flex-direction: column;
  background-color: map.get($color, white);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.signin-header {
  padding: 24px;
  background-color: map.get($color, secondary);
  text-align: center;

  h1 {
    font-size: map.get($fontSize, h2);
    font-weight: 500;
    color: map.get($color, white);
  }
}

.signin-body {
  padding: 48px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;

  label {
    font-size: map.get($fontSize, default);
    color: map.get($color, black);
  }

  input {
    width: 100%;
    padding: 15px 13px;
    border: 1px solid map.get($color, neutral);
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

.password-input {
  position: relative;

  input {
    padding-right: 41px;
  }

  .password-toggle {
    position: absolute;
    top: 50%;
    right: 13px;
    transform: translateY(-50%);
    font-size: 16px;
    color: map.get($color, neutral);
  }
}

.password-strength {
  height: 4px;
  margin-top: 4px;
  border-radius: 2px;
  background-color: map.get($color, gray);
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

.forgot-password {
  text-align: center;
  font-size: map.get($fontSize, hint);
  color: map.get($color, hint);
}

.signup-hint {
  text-align: center;
  font-size: map.get($fontSize, default);
  color: map.get($color, black);

  a {
    color: map.get($color, secondary2);

    &:hover {
      text-decoration: underline;
    }
  }
}
</style>
