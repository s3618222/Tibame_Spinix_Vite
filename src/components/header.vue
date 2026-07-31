<template>
  <header
  :class="{
    solid: solid,
    scrolled: isScrolled
  }"
>
    <div class="header-main">
      <a href="index.html" class="logo">
        <img src="/spinix_logo.png" alt="Spinix Logo">
      </a>

      <nav>
        <ul>
          <li>
            <a href="index.html">首頁</a>
          </li>

          <li>
            <a href="battle.html">對戰配對</a>
          </li>

          <li>
            <a href="build.html">陀螺配置</a>
          </li>

          <li>
            <a href="forum.html">Spinix論壇</a>
          </li>

          <li>
            <a href="market.html">交換專區</a>
          </li>
        </ul>
      </nav>
    </div>

    <div class="header-btns">
      <a class="signUp-btn" href="signUp.html">註冊</a>
      <a class="signIn-btn" href="signIn.html">登入</a>
    </div>
  </header>
</template>

<script>
export default {
  name: "Header",

  props: {
    solid: {
      type: Boolean,
      default: false
    }
  },

  data() {
    return {
      isScrolled: false
    };
  },

  methods: {
    changeHeader() {
      // 固定實心的頁面不需要切換 scrolled
      if (this.solid) {
        this.isScrolled = false;
        return;
      }

      this.isScrolled = window.scrollY > 40;
    }
  },

  mounted() {
    this.changeHeader();

    if (!this.solid) {
      window.addEventListener("scroll", this.changeHeader);
    }
  },

  beforeUnmount() {
    window.removeEventListener("scroll", this.changeHeader);
  }
};
</script>

<style lang="scss" scoped>
  /* header高度88px，置頂後脫離文件流，
  須設定body {padding-top: 88px} 
  撐回原本高度 */

  //如果 Header 要全程實心，請在使用元件時傳入 solid：<Header solid />
  header {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1000;

      width: 100%;
      height: 88px;
      padding-inline: 20px;

      background-color: transparent;
      border-bottom: 1px solid transparent;
      display: flex;
      justify-content: space-between;
      align-items: center;

      transition:
          background-color 0.32s ease,
          border-color 0.32s ease,
          box-shadow 0.32s ease;
  }

  header.solid {
      background-color: #141C26;
  }

  header.scrolled {
      background-color: #141C26;
      border-bottom-color: rgba(255, 255, 255, 0.06);
      box-shadow: 0 8px 24px rgba(20, 28, 38, 0.12);
  }

  .header-main {
      display: flex;
      align-items: center;
      gap: 24px;
  }

  .header-main .logo {
      display: flex;
      align-items: center;
      flex-shrink: 0;
  }

  .header-main .logo img {
      display: block;
      width: 120px;
      transition: transform 0.32s ease;
      animation: logoSpin 0.7s ease;
  }

  .header-main .logo:hover img {
      transform: rotate(-16deg) scale(1.05);
  }

  /* logo旋轉動畫 */
  @keyframes logoSpin {
      from {
          transform: rotate(0deg);
      }

      to {
          transform: rotate(360deg);
      }
  }

  .header-main nav ul {
      list-style: none;
      display: flex;
      gap: 24px;
  }

  .header-main nav ul li a {
      text-decoration: none;
      font-size: 18px;
      color: #ffffff;
      transition: color 0.28s ease;

      position: relative;
  }

  .header-main nav ul li a::after {
      content: "";
      position: absolute;
      left: 50%;
      bottom: -4px;
      transform: translateX(-50%);

      width: 0;
      height: 2px;
      background: #ffda94;
      transition: width 0.28s ease;
  }

  .header-main nav ul li a:hover,
  .header-main nav ul li a.active {
      color: #FEC96B;
  }

  .header-main nav ul li a:hover::after {
      width: 100%;
  }

  .header-btns {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 24px;
  }

  .header-btns a {
      text-decoration: none;
      text-align: center;
      font-size: 18px;
      color: #ffffff;
      padding: 8px 24px;
      border-radius: 16px;

      transition:
          color 0.28s ease,
          background-color 0.28s ease,
          border-color 0.28s ease,
          box-shadow 0.28s ease,
          transform 0.28s ease;
  }

  .header-btns .signUp-btn {
      background-color: #FEC96B;
      color: #141C26;

      box-shadow: 0 4px 12px rgba(254, 201, 107, 0.12);
  }

  .header-btns .signUp-btn:hover {
      background-color: #ffda94;
      box-shadow: 0 7px 18px rgba(254, 201, 107, 0.2);
      transform: translateY(-2px);
  }

  .header-btns .signIn-btn {
      border: 1px solid rgba(255, 255, 255, 0.8);
      color: #ffffff;
  }

  .header-btns .signIn-btn:hover {
      border-color: #ffda94;
      color: #ffda94;
      transform: translateY(-2px);
  }

  .header-btns a:active {
      transform: translateY(0);
  }
</style>