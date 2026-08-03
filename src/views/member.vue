<template>
  <div class="member-center">
    <aside class="member-sidebar">
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
    </aside>

    
    <main class="member-content">
      <RouterView />
    </main>

  </div>
</template>

<script>
  export default {
    name: "Member"
  };
</script>

<style lang="scss" scoped>
.member-center {
  width: min(1200px, calc(100% - 64px));
  margin: 120px auto 60px;

  display: grid;
  grid-template-columns: 240px minmax(0, 1fr);
  gap: 32px;
}

.member-sidebar {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.member-content {
  min-width: 0;
}
</style>