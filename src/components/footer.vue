<template>
  <footer>
    <div class="footer-main">
      <div class="footer-col footer-brand">
        <a href="index.html" class="footer-logo">
          <img src="/spinix_logo_words.png" alt="Spinix Logo">
        </a>
        <!-- <p>
          從尋找對戰、分享配置到交流心得與交換陀螺，讓每一次旋轉都能連結新的玩家。
        </p> -->
      </div>

      <div class="footer-col footer-intro ">
        <h4>網站介紹</h4>
        <p>
          從尋找對戰、分享配置到交流心得與交換陀螺，讓每一次旋轉都能連結新的玩家。
        </p>
      </div>

      <div class="footer-col footer-contact">
        <h4>合作與洽詢</h4>
          <p>對合作、活動舉辦、聯名宣傳有興趣？</p>
          <p>歡迎與我們聯繫！</p>
        <a href="mailto:contact@spinix.com.tw">contact@spinix.com.tw</a>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2026 Spinix. All rights reserved.</p>
    </div>
  </footer>
</template>

<script>
  export default {
    name: "Footer"
  }
</script>

<style lang="scss" scoped>
  footer {
    width: 100%;
    padding: 32px;
    background-color: #141C26;
    font-size: 16px;
  }

  .footer-main {
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 40px;
  }

  .footer-col {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      &:first-child{
        width: fit-content;
        img{
          margin-inline: auto;
        }
      }
      h4{
        color: #FEC96B;
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 12px;
      }
      p{
        color: #ffffff;
        line-height: 1.5;
      }
  }

  .footer-brand {
      width: 25%;
  }

  .footer-logo {
      display: inline-block;
  }

  .footer-brand img {
      display: block;
      width: 120px;
      margin-bottom: 24px;

      transition:
          transform 0.32s ease,
          opacity 0.32s ease;
  }

  .footer-brand img:hover {
      transform: translateY(-3px);
      opacity: 0.92;
  }

  @keyframes footerSpin {
      from {
          transform: rotate(0deg);
      }

      to {
          transform: rotate(360deg);
      }
  }

  .footer-contact{
    flex: 1;
  }

  .footer-contact a {
      margin-top: 12px;
      text-decoration: none;
      color: #ffffff;
      font-weight: 700;
      letter-spacing: 0.1em;
      transition: color .32s ease;
  }

  .footer-contact a:hover {
      color: #FEC96B;
  }

  .footer-col ul {
      list-style: none;
  }

  .footer-col ul li {
      margin-bottom: 12px;
  }

  .footer-col ul li a {
      display: inline-block;
      text-decoration: none;
      color: #ffffff;
      transition: color .32s ease, transform .32s ease;
  }

  .footer-col ul li a:hover {
      color: #ffda94;
      transform: translateX(4px);
  }

  .footer-bottom {
      width: 100%;
      margin: 24px auto 0;
      padding-top: 20px;
      border-top: 1px solid rgba(255, 255, 255, 0.12);

      display: flex;
      justify-content: center;
      align-items: center;

      color: #999;
      font-size: 14px;
  }

  @media screen and (width < 768px) {
    footer {
      padding: 24px;
    }

    .footer-main {
      flex-direction: column;
      gap: 28px;
    }

    .footer-intro {
      display: none;
    }

    .footer-col:first-child,
    .footer-contact {
      width: 100%;
      min-width: 0;
    }

    .footer-contact a {
      overflow-wrap: anywhere;
    }
}

  
</style>