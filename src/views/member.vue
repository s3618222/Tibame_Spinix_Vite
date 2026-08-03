<template>
  <div class="member-center">
    <aside class="member-sidebar">
      <button
        type="button"
        @click="currentSection = 'battle'"
      >
        我的約戰
      </button>

      <button
        type="button"
        @click="currentSection = 'forum'"
      >
        我的論壇
      </button>

      <button
        type="button"
        @click="currentSection = 'exchange'"
      >
        我的交換
      </button>
    </aside>

    <main class="member-content">
      <MyBattle v-if="currentSection === 'battle'" />

      <MyForum v-else-if="currentSection === 'forum'" />

      <MyExchange v-else-if="currentSection === 'exchange'" />
    </main>
  </div>
</template>

<script>
import MyBattle from "@/components/member/battle/myBattle.vue";
import MyForum from "@/components/myForum.vue";
import MyExchange from "@/components/myExchange.vue";

export default {
  name: "Member",

  components: {
    MyBattle,
    MyForum,
    MyExchange
  },

  data() {
    return {
      currentSection: "battle"
    };
  }
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