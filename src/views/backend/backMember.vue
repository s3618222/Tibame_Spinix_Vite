<template>
  <div class="back-member">
    <aside class="back-sidebar">
      <h1>
        SPINIX
        <br>
        後台管理系統
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

    <div class="back-main">
      <header class="back-header">
        <span>管理員</span>
        <button type="button">登出</button>
      </header>

      <main class="back-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<script>
  export default {
    name: "BackMember"
  };
</script>

<style lang="scss" scoped>
  .back-member {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 244px minmax(0, 1fr);

    color: #141c26;
    background-color: #f7f5f3;
  }

  .back-sidebar {
    min-height: 100vh;
    padding: 48px 24px;

    color: #ffffff;
    background-color: #141c26;
  }

  .back-sidebar h1 {
    margin-bottom: 48px;
    font-size: 28px;
    line-height: 1.3;
  }

  .back-nav {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .back-nav-link {
    padding: 12px;

    color: #ffffff;
    text-decoration: none;
    border-right: 6px solid transparent;
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

  .back-content {
    padding: 48px;
  }
</style>