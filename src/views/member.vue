<template>
  <Header solid />
  <div class="member-page">
    <main class="member-center">

      <aside class="member-sidebar">
        <RouterLink
          :to="{ name: 'member-profile' }"
          class="member-sidebar-link"
        >
          個人資料
        </RouterLink>

        <RouterLink
          :to="{ name: 'member-battle' }"
          class="member-sidebar-link"
        >
          我的約戰
        </RouterLink>

        <RouterLink
          :to="{ name: 'member-forum' }"
          class="member-sidebar-link"
        >
          我的論壇
        </RouterLink>

        <RouterLink
          :to="{ name: 'member-exchange' }"
          class="member-sidebar-link"
        >
          我的交換
        </RouterLink>

        <RouterLink
          :to="{ name: 'member-appeal' }"
          class="member-sidebar-link"
        >
          我的申訴
        </RouterLink>

        <RouterLink
          :to="{ name: 'member-violation' }"
          class="member-sidebar-link"
        >
          違規紀錄
        </RouterLink>
      </aside>

      
      <section class="member-content">
        <RouterView />
      </section>

    </main>
  </div>
  <Footer />
</template>

<script>
  import Header from "@/components/Header.vue";
  import Footer from "@/components/Footer.vue";

  export default {
    name: "Member",

    components: {
      Header,
      Footer,
    }
  };
</script>

<style lang="scss" scoped>
  .member-page {
    padding-top: 88px;
  }

  .member-center {
    width: min(1200px, calc(100% - 64px));
    margin: 60px auto 60px;
    display: grid;
    grid-template-columns: 200px minmax(0, 1fr);
    gap: 32px;
  }

  .member-sidebar {
    border: 1px solid black;
    text-align: center;

    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  section.member-content {
    padding: 0px; // *修正被其他頁面汙染的css
    min-width: 0;
  }


@media (max-width: 1024px) {
  .member-center {
    grid-template-columns: 1fr;
    gap: 24px;
  }

  .member-sidebar {
    flex-direction: row;
    overflow-x: auto;
    gap: 24px;

    padding: 12px 16px;
    white-space: nowrap;
    text-align: left;
  }
}

</style>