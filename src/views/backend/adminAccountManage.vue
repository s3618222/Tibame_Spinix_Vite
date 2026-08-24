<template>
  <section class="admin-manage">
    <!-- 標題 -->
    <div class="admin-manage__title">
      <h1>管理員帳號管理</h1>
    </div>

    <!-- 新增管理員按鈕 -->
    <div class="admin-manage__toolbar">
      <button
        type="button"
        class="admin-add-btn"
        @click="openModal('add')"
      >
        新增管理員
      </button>
    </div>

    <!-- 資料表 -->
    <div class="admin-table-wrap">
      <div class="admin-table">
        <!-- 表頭 -->
        <div class="admin-table__caption">
          <div class="col col--id">ID</div>
          <div class="col col--account">帳號</div>
          <div class="col col--name">管理員姓名</div>
          <div class="col col--time">建立時間</div>
          <div class="col col--type">管理員類型</div>
          <div class="col col--state">管理員狀態</div>
          <div class="col col--action">操作</div>
        </div>

        <!-- 資料列 -->
        <div
          v-for="admin in admins"
          :key="admin.id"
          class="admin-table__row"
        >
          <div class="col col--id">#{{ admin.id }}</div>
          <div class="col col--account">{{ admin.account }}</div>
          <div class="col col--name">{{ admin.name }}</div>
          <div class="col col--time">{{ admin.createTime }}</div>
          <div class="col col--type">{{ admin.type }}</div>
          <div class="col col--state">{{ admin.state }}</div>
          <div class="col col--action">
            <button
              type="button"
              class="action-btn"
              @click="toggleMenu(admin.id)"
            >
              <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>

            <!-- 列操作下拉 -->
            <div
              v-if="openMenuId === admin.id"
              class="action-menu"
            >
              <button
                type="button"
                class="action-menu__item"
                @click="openModal('edit', admin)"
              >
                編輯
              </button>
              <button
                type="button"
                class="action-menu__item"
                @click="openModal('reset', admin)"
              >
                重設密碼
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 新增管理員 Modal -->
    <AdminAddModal
      v-if="activeModal === 'add'"
      @close="closeModal"
      @submit="handleAdd"
    />

    <!-- 編輯 Modal -->
    <AdminEditModal
      v-if="activeModal === 'edit'"
      :admin="selectedAdmin"
      @close="closeModal"
      @submit="handleEdit"
    />

    <!-- 重設密碼 Modal -->
    <AdminResetPasswordModal
      v-if="activeModal === 'reset'"
      :admin="selectedAdmin"
      @close="closeModal"
      @submit="handleReset"
    />
  </section>
</template>

<script setup>
  import { ref } from "vue";
  import AdminAddModal from "@/components/backend/admin/AdminAddModal.vue";
  import AdminEditModal from "@/components/backend/admin/AdminEditModal.vue";
  import AdminResetPasswordModal from "@/components/backend/admin/AdminResetPasswordModal.vue";

  // 假資料（欄位命名對齊 DB admin 表語意，方便日後接 API）
  const admins = ref([
    { id: "01", account: "adminA", name: "管理員Ａ", createTime: "2026-3-10", type: "一般管理員", state: "在職" },
    { id: "02", account: "adminB", name: "管理員B", createTime: "2026-2-10", type: "一般管理員", state: "離職" },
    { id: "03", account: "adminC", name: "管理員C", createTime: "2026-1-10", type: "超級管理員", state: "在職" }
  ]);

  // 列操作下拉
  const openMenuId = ref(null);

  function toggleMenu(id) {
    openMenuId.value = openMenuId.value === id ? null : id;
  }

  // Modal 控制
  const activeModal = ref(null); // 'add' | 'edit' | 'reset' | null
  const selectedAdmin = ref({});

  function openModal(type, admin = {}) {
    selectedAdmin.value = admin;
    activeModal.value = type;
    openMenuId.value = null;
  }

  function closeModal() {
    activeModal.value = null;
    selectedAdmin.value = {};
  }

  function handleAdd(payload) {
    // 尚未串接 API
    console.log("新增管理員", payload);
  }

  function handleEdit(payload) {
    // 尚未串接 API
    console.log("編輯管理員", payload);
  }

  function handleReset(payload) {
    // 尚未串接 API
    console.log("重設密碼", payload);
  }
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  .admin-manage {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .admin-manage__title h1 {
    font-size: map-get($fontSize, h1);
    color: map-get($color, secondary2);
  }

  // 新增按鈕
  .admin-manage__toolbar {
    display: flex;
  }

  .admin-add-btn {
    padding: 8px 24px;

    font-size: map-get($fontSize, default);
    font-weight: 500;
    color: map-get($color, secondary);
    background-color: map-get($color, primary);
    border-radius: 8px;
    cursor: pointer;
  }

  // 資料表
  .admin-table {
    width: 100%;
    min-width: 760px; // 窄於此則由頁面層級橫向捲動（不裁切陰影/下拉）

    border: 1px solid map-get($color, hint);
    border-radius: 8px;
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
      border-radius: 8px 8px 0 0; // 維持上緣圓角（取代 overflow:hidden）
    }

    &__row {
      height: 48px;
      border-bottom: 1px solid map-get($color, black);
      background-color: map-get($color, white);

      &:last-child {
        border-bottom: none;
        border-radius: 0 0 8px 8px; // 維持下緣圓角
      }
    }
  }

  .col {
    font-size: 16px;
    color: map-get($color, secondary);
    min-width: 0; // 允許在 flex 版面中收縮（RWD）

    &--id {
      flex: 0 1 80px;
    }

    // 佔比最大，吸收多餘空間 → 把後面欄位（含操作）推到最右
    &--account {
      flex: 2 1 140px;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    &--name {
      flex: 1 1 100px;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    &--time {
      flex: 1 1 100px;
    }

    &--type {
      flex: 0 1 100px;
    }

    &--state {
      flex: 0 1 90px;
    }

    // 固定寬且為最後一欄 → 靠齊最右
    &--action {
      position: relative;
      flex: 0 0 56px;
      display: flex;
      justify-content: flex-end;
    }
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
      color: map-get($color, secondary);
    }
  }
</style>
