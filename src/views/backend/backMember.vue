<template>
  <div class="back-member">
    <aside class="back-sidebar"
      :class="{ active: isSidebarOpen }"
    >
      <h1>
        SPINIX
        <br>
        後台管理
      </h1>

      <nav class="back-nav">
        <RouterLink
          :to="{ name: 'backend-member' }"
          class="back-nav-link"
        >
          會員管理
        </RouterLink>

        <RouterLink
          :to="{ name: 'backend-battle' }"
          class="back-nav-link"
        >
          約戰專區
        </RouterLink>

        <RouterLink
          :to="{ name: 'backend-beyblade' }"
          class="back-nav-link"
        >
          陀螺圖庫
        </RouterLink>

        <RouterLink
          :to="{ name: 'backend-forum' }"
          class="back-nav-link"
        >
          論壇管理
        </RouterLink>

        <RouterLink
          :to="{ name: 'backend-exchange' }"
          class="back-nav-link"
        >
          交換管理
        </RouterLink>

        <RouterLink
          :to="{ name: 'backend-complaint' }"
          class="back-nav-link"
        >
          申訴管理
        </RouterLink>

        <RouterLink
          :to="{ name: 'backend-admin-account' }"
          class="back-nav-link"
        >
          管理員帳號
        </RouterLink>
      </nav>
    </aside>

    <!-- 手機、平板 sidebar 開啟時的背景遮罩 -->
    <div
      v-if="isSidebarOpen"
      class="back-sidebar-mask"
      @click="isSidebarOpen = false"
    >
    </div>

    <div class="back-main">
      <header class="back-header">
        <!-- 平板、手機漢堡列；sidebar 關閉時才顯示漢堡 -->
        <button
          type="button"
          class="back-menu-btn"
          :class="{ active: isSidebarOpen }"
          aria-label="切換後台導覽"
          @click="isSidebarOpen = !isSidebarOpen"
        >
            <span></span>
            <span></span>
            <span></span>
          </button>
        <!-- nav右上管理員操作menu、登出 -->
        <div class="back-header-admin">
          <span>管理員</span>
          <button type="button">登出</button>
        </div>
      </header>

      <main class="back-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script>
  export default {
    name: "BackMember",

    data() {
      return {
        isSidebarOpen: false
      };
    }
  };
</script>

<style lang="scss" scoped>
  .back-member {
    min-height: 100vh;

    display: grid;
    grid-template-columns: 168px minmax(0, 1fr);

    color: #141c26;
    background-color: #f7f5f3;

    h1 {
      text-align: center;
    }
  }

  .back-sidebar {
    height: 100vh;
    position: sticky;
    top: 0;
    z-index: 1000;

    padding: 48px 20px;

    color: #ffffff;
    background-color: #141c26;
  }

  .back-sidebar h1 {
    margin-block: 12px 40px;
    font-size: 28px;
    line-height: 1.3;
  }

  .back-nav {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .back-nav-link {
    padding: 8px;
    color: #ffffff;
    text-decoration: none;
    border-right: 4px solid transparent;
  }

  .back-nav-link:hover {
    color: #fec96b;
  }

  .back-nav-link.router-link-active {
    color: #fec96b;
    border-right-color: #fec96b;
  }

  .back-main {
    min-width: 0;
  }

  .back-header {
    height: 56px;
    padding: 0 32px;

    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;

    background-color: #ffffff;
  }

  .back-header-admin {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .back-content {
    padding: 48px;
  }

  // 漢堡按鈕
  .back-menu-btn {
    width: 32px;
    height: 32px;
    padding: 0px;

    display: none;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 5px;

    border: 2px solid transparent;
    border-radius: 10px;

    background-color: transparent;

    cursor: pointer;

    transition:
      border-color 0.3s ease,
      border-radius 0.3s ease,
      background-color 0.3s ease;

    span {
      width: 24px;
      height: 2px;

      display: block;

      border-radius: 999px;
      background-color: #141c26;

      transition:
        transform 0.3s ease,
        opacity 0.2s ease,
        background-color 0.3s ease;
    }
  }

  .back-menu-btn.active {
    border-color: #fec96b;
    border-radius: 50%;

    background-color: #141c26;

    span {
      background-color: #fec96b;
    }

    span:nth-child(1) {
      transform: translateY(7px) rotate(45deg);
    }

    span:nth-child(2) {
      opacity: 0;
    }

    span:nth-child(3) {
      transform: translateY(-7px) rotate(-45deg);
    }
  }

  // sidebar 背景遮罩
  .back-sidebar-mask {
    display: none;
  }


  @media screen and (max-width: 1024px) {
    .back-member {
      grid-template-columns: minmax(0, 1fr);
    }


    .back-sidebar {
      width: 200px;
      height: 100vh;

      position: fixed;
      top: 0;
      left: 0;

      z-index: 1000;

      transform: translateX(-100%);
      transition: transform 0.3s ease;
    }


    .back-sidebar.active {
      transform: translateX(0);
    }


    .back-sidebar-mask {
      position: fixed;
      inset: 0;

      display: block;

      background-color: rgba(20, 28, 38, 0.35);

      z-index: 900;
    }

     // sidebar 控制按鈕固定畫面左上
    .back-menu-btn {
      width: 40px;
      height: 40px;

      position: fixed;
      top: 8px;
      left: 20px;
      z-index: 1100;

      display: flex;

      border: 2px solid #fec96b;
      border-radius: 10px;

      background-color: #ffffff;

      box-shadow: 0 3px 10px rgba(20, 28, 38, 0.16);

      span {
        width: 22px;
        height: 2px;

        background-color: #141c26;
      }
    }


    // sidebar 打開後，同一顆按鈕變成 X
    .back-menu-btn.active {
      border-color: #fec96b;
      border-radius: 50%;

      background-color: #141c26;

      span {
        background-color: #fec96b;
      }

      span:nth-child(1) {
        transform: translateY(7px) rotate(45deg);
      }

      span:nth-child(2) {
        opacity: 0;
      }

      span:nth-child(3) {
        transform: translateY(-7px) rotate(-45deg);
      }
    }


    .back-header {
      justify-content: flex-end;

      // 左側保留一些空間，
      // 避免 header 內容未來撞到固定漢堡
      padding-left: 76px;
    }

    .back-content {
      padding: 32px;
    }

  }

</style>