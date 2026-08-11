<template>
  <Header solid />
  <div class="member-page">
    <main class="member-center">

      <aside class="member-sidebar">
        <div class="sidebar-user">
          <img class="sidebar-avatar" :src="currentUser.avatar" alt="使用者頭像" />
          <p class="sidebar-username">{{ currentUser.username }}</p>
        </div>

        <nav class="sidebar-nav">
          <RouterLink
            v-for="item in navItems"
            :key="item.name"
            :to="{ name: item.name }"
            class="member-sidebar-link"
            :class="{ active: $route.name === item.name }"
          >
            <i :class="item.icon"></i>
            {{ item.label }}
          </RouterLink>
        </nav>
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
    },

    data() {
      return {
        //側邊選單頂部顯示的使用者資訊，先寫死假資料
        currentUser: {
          avatar: "/spinix_member_default.png",
          username: "陀螺戰神123"
        },

        //側邊選單導覽項目設定
        navItems: [
          { name: "member-profile", label: "個人資料", icon: "fa-solid fa-user" },
          { name: "member-battle", label: "我的約戰", icon: "fa-solid fa-gamepad" },
          { name: "member-forum", label: "我的論壇", icon: "fa-regular fa-message" },
          { name: "member-exchange", label: "我的交換", icon: "fa-solid fa-right-left" },
          { name: "member-appeal", label: "我的申訴", icon: "fa-regular fa-flag" },
          { name: "member-violation", label: "違規紀錄", icon: "fa-solid fa-shield-halved" }
        ]
      };
    }
  };
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .member-page {
    padding-top: 88px;
  }

  .member-center {
    width: min(1200px, calc(100% - 64px));
    margin: 60px auto;
    display: grid;
    grid-template-columns: 240px minmax(0, 1fr);
    gap: 32px;
  }

  //側邊導覽列改sticky，分頁高度讓主內容頁負責撐開
  .member-sidebar {
    background-color: rgba(map.get($color, primary), 0.25);
    box-shadow: 0 0 16px rgba(0, 0, 0, 0.15);
    padding: 40px 16px;
    border-radius: 16px;

    display: flex;
    flex-direction: column;
    gap: 40px;

    position: sticky;
    top: 118px; //預留和header(88px)之間約30px的距離
    align-self: start;
  }

  .sidebar-user {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
  }

  .sidebar-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
  }

  .sidebar-username {
    font-size: map.get($fontSize, h2);
    font-weight: 500;
    color: map.get($color, secondary);
    text-align: center;
  }

  .sidebar-nav {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .member-sidebar-link {
    display: flex;
    align-items: center;
    gap: 12px;

    font-size: 20px;
    color: map.get($color, secondary);
    text-decoration: none;

    padding-right: 12px;
    border-right: 8px solid transparent;

    i {
      width: 24px;
      font-size: 20px;
      text-align: center;
    }

    &.active {
      color: map.get($color, secondary2);
      border-right-color: map.get($color, secondary2);
    }
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
    position: static; //取消sticky
    flex-direction: row;
    align-items: center;
    overflow-x: auto;
    gap: 24px;

    padding: 16px;
  }

  .sidebar-user {
    flex-direction: row;
    flex-shrink: 0;
  }

  .sidebar-nav {
    flex-direction: row;
    flex-shrink: 0;
    gap: 24px;
  }

  .member-sidebar-link {
    white-space: nowrap;
    border-right: none;
    border-bottom: 4px solid transparent;
    padding-right: 0;
    padding-bottom: 4px;

    &.active {
      border-bottom-color: map.get($color, secondary2);
    }
  }
}

</style>