<template>
  <section class="member-manage">
    <!-- 標題 -->
    <div class="member-manage__title">
      <h1>會員管理</h1>
    </div>

    <!-- 篩選列 -->
    <div class="member-filter">
      <div class="member-filter__select">
        <select v-model="filterType">
          <option value="all">所有會員</option>
          <option value="suspended">已停權會員</option>
        </select>
      </div>
      <div class="member-filter__search">
        <input
          v-model="searchId"
          type="text"
          placeholder="搜尋會員ID"
        >
      </div>
    </div>

    <!-- 資料表 -->
    <div class="member-table-wrap">
      <div class="member-table">
        <!-- 表頭 -->
        <div class="member-table__caption">
          <div class="col col--id">ID</div>
          <div class="col col--name">使用者名稱</div>
          <div class="col col--account">帳號</div>
          <div class="col col--status">約戰權限</div>
          <div class="col col--status">論壇權限</div>
          <div class="col col--status">交換權限</div>
          <div class="col col--action">操作</div>
        </div>

        <!-- 資料列 -->
        <div
          v-for="member in members"
          :key="member.id"
          class="member-table__row"
        >
          <div class="col col--id">#{{ member.id }}</div>
          <div class="col col--name">{{ member.name }}</div>
          <div class="col col--account">{{ member.account }}</div>
          <div class="col col--status">
            <MemberStatusPill :status="member.battleStatus" />
          </div>
          <div class="col col--status">
            <MemberStatusPill :status="member.forumStatus" />
          </div>
          <div class="col col--status">
            <MemberStatusPill :status="member.marketStatus" />
          </div>
          <div class="col col--action">
            <button
              type="button"
              class="action-btn"
              @click="toggleMenu(member.id)"
            >
              <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>

            <!-- 列操作下拉 -->
            <div
              v-if="openMenuId === member.id"
              class="action-menu"
            >
              <button
                type="button"
                class="action-menu__item action-menu__item--suspend"
                @click="openModal('suspend', member)"
              >
                停權處置
              </button>
              <button
                type="button"
                class="action-menu__item action-menu__item--restore"
                @click="openModal('restore', member)"
              >
                恢復權限
              </button>
            </div>
          </div>
        </div>

        <!-- 分頁列 -->
        <div class="member-table__footer">
          <span class="member-table__count">顯示：{{ members.length }}筆</span>
          <div class="pagination">
            <button
              type="button"
              class="pagination__btn"
              @click="prevPage"
            >
              <i class="fa-solid fa-angle-left"></i>
            </button>
            <button
              v-for="page in totalPages"
              :key="page"
              type="button"
              class="pagination__btn"
              :class="{ 'is-active': page === currentPage }"
              @click="currentPage = page"
            >
              {{ page }}
            </button>
            <button
              type="button"
              class="pagination__btn"
              @click="nextPage"
            >
              <i class="fa-solid fa-angle-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 停權處置 Modal -->
    <SuspendModal
      v-if="activeModal === 'suspend'"
      :member="selectedMember"
      @close="closeModal"
      @submit="handleSuspend"
    />

    <!-- 恢復權限 Modal -->
    <RestoreModal
      v-if="activeModal === 'restore'"
      :member="selectedMember"
      @close="closeModal"
      @submit="handleRestore"
    />
  </section>
</template>

<script setup>
  import { ref } from "vue";
  import MemberStatusPill from "@/components/backend/member/MemberStatusPill.vue";
  import SuspendModal from "@/components/backend/member/SuspendModal.vue";
  import RestoreModal from "@/components/backend/member/RestoreModal.vue";

  // 假資料（欄位命名對齊 DB member 表語意，方便日後接 API）
  const members = ref([
    { id: 1234, name: "大爆殺神", account: "killer@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "restricted", marketUntil: "2026/9/12" },
    { id: 1235, name: "旋風小子", account: "spin@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "normal" },
    { id: 1236, name: "陀螺王", account: "king@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "normal" },
    { id: 1237, name: "無敵鐵金剛", account: "iron@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "normal" },
    { id: 1238, name: "閃電俠", account: "flash@gmail.com", battleStatus: "restricted", battleUntil: "2026/8/31", forumStatus: "normal", marketStatus: "normal" },
    { id: 1239, name: "小小兵", account: "minion@gmail.com", battleStatus: "normal", forumStatus: "restricted", forumUntil: "2026/9/5", marketStatus: "normal" },
    { id: 1240, name: "戰鬥陀螺", account: "beyblade@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "normal" },
    { id: 1241, name: "旋轉大師", account: "master@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "normal" },
    { id: 1242, name: "暴走族", account: "runaway@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "normal" },
    { id: 1243, name: "金屬狂潮", account: "metal@gmail.com", battleStatus: "normal", forumStatus: "normal", marketStatus: "normal" }
  ]);

  // 篩選 / 搜尋（純外觀，尚未做實際過濾）
  const filterType = ref("all");
  const searchId = ref("");

  // 分頁（純外觀）
  const currentPage = ref(1);
  const totalPages = ref(3);

  function prevPage() {
    if (currentPage.value > 1) currentPage.value--;
  }

  function nextPage() {
    if (currentPage.value < totalPages.value) currentPage.value++;
  }

  // 列操作下拉
  const openMenuId = ref(null);

  function toggleMenu(id) {
    openMenuId.value = openMenuId.value === id ? null : id;
  }

  // Modal 控制
  const activeModal = ref(null); // 'suspend' | 'restore' | null
  const selectedMember = ref({});

  function openModal(type, member) {
    selectedMember.value = member;
    activeModal.value = type;
    openMenuId.value = null;
  }

  function closeModal() {
    activeModal.value = null;
    selectedMember.value = {};
  }

  function handleSuspend(payload) {
    // 尚未串接 API
    console.log("停權處置", payload);
  }

  function handleRestore(payload) {
    // 尚未串接 API
    console.log("恢復權限", payload);
  }
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  .member-manage {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .member-manage__title h1 {
    font-size: map-get($fontSize, h1);
    color: map-get($color, secondary2);
  }

  // 篩選列
  .member-filter {
    display: flex;
    gap: 12px;
    align-items: center;

    &__select select,
    &__search input {
      padding: 8px 12px;
      font-size: map-get($fontSize, default);
      color: map-get($color, secondary);
      background-color: map-get($color, white);
      border: 1px solid #ddd6c8;
      border-radius: 10px;
    }

    &__select select {
      min-width: 140px;
      appearance: none;
      cursor: pointer;
    }

    &__search input {
      width: 200px;

      &::placeholder {
        font-size: map-get($fontSize, hint);
        color: map-get($color, hint);
      }
    }
  }

  // 資料表
  .member-table-wrap {
    overflow-x: auto;
  }

  .member-table {
    min-width: 960px;

    border: 1px solid map-get($color, hint);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0 16px 0 rgba(0, 0, 0, 0.15);

    &__caption,
    &__row {
      display: flex;
      align-items: center;
      padding: 0 16px;
    }

    &__caption {
      height: 48px;
      background-color: rgba(254, 201, 107, 0.4);
    }

    &__row {
      height: 56px;
      border-bottom: 1px solid map-get($color, black);
      background-color: map-get($color, white);
    }
  }

  .col {
    font-size: 16px;
    color: map-get($color, secondary);

    &--id {
      width: 120px;
    }

    &--name {
      width: 200px;
    }

    &--account {
      width: 240px;
    }

    &--status {
      width: 100px;
    }

    &--action {
      position: relative;
      width: 100px;
      display: flex;
      justify-content: flex-end;
    }
  }

  .member-table__caption .col--action {
    justify-content: flex-end;
  }

  // 列操作
  .action-btn {
    width: 24px;
    height: 24px;
    cursor: pointer;
    color: map-get($color, secondary);
  }

  .action-menu {
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 20;

    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px;

    background-color: map-get($color, white);
    border: 1px solid map-get($color, hint);
    border-radius: 8px;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);

    &__item {
      font-size: map-get($fontSize, default);
      white-space: nowrap;
      cursor: pointer;
      text-align: left;

      &--suspend {
        color: map-get($color, error);
      }

      &--restore {
        color: map-get($color, olive);
      }
    }
  }

  // 分頁列
  .member-table__footer {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;

    background-color: map-get($color, white);
    border-top: 1px solid map-get($color, hint);
  }

  .member-table__count {
    font-size: map-get($fontSize, default);
    color: #262626;
  }

  .pagination {
    flex: 1;
    display: flex;
    justify-content: center;
    gap: 16px;

    &__btn {
      min-width: 40px;
      padding: 4px 12px;

      font-size: map-get($fontSize, default);
      font-weight: 500;
      color: map-get($color, hint);
      background-color: map-get($color, white);
      border: 1px solid rgba(238, 238, 238, 0.93);
      border-radius: 8px;
      cursor: pointer;

      &.is-active {
        color: map-get($color, secondary);
        background-color: map-get($color, primary);
        border-color: map-get($color, primary);
      }
    }
  }
</style>
