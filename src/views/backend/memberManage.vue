<template>
  <section class="member-manage">
    <!-- 標題 -->
    <div class="member-manage__title">
      <h1>會員管理</h1>
    </div>

    <!-- 篩選工具列 -->
    <div class="member-manage-filter">
      <div class="filter-item">
        <select v-model="filterType">
          <option value="all">所有會員</option>
          <option value="suspended">已停權會員</option>
        </select>
        <i class="fa-solid fa-chevron-down"></i>
      </div>
      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input
          v-model="searchName"
          type="text"
          placeholder="搜尋會員名稱"
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
          v-for="member in pagedMembers"
          :key="member.id"
          class="member-table__row"
        >
          <div class="col col--id">#{{ member.id }}</div>
          <div class="col col--name">{{ member.name }}</div>
          <div class="col col--account">{{ member.account }}</div>
          <div class="col col--status">
            <MemberStatusPill :status="member.battleStatus"/>
          </div>
          <div class="col col--status">
            <MemberStatusPill :status="member.forumStatus"/>
          </div>
          <div class="col col--status">
            <MemberStatusPill :status="member.marketStatus"/>
          </div>
          <div class="col col--action">
            <button
              type="button"
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
          <span class="member-table__count">顯示：{{ pageSize }}筆</span>
          <Pagination
            v-model:current-page="currentPage"
            :page-size="pageSize"
            :total="total"
          />
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
  import { ref, computed, watch, onMounted } from "vue";
  import MemberStatusPill from "@/components/backend/member/MemberStatusPill.vue";
  import SuspendModal from "@/components/backend/member/SuspendModal.vue";
  import RestoreModal from "@/components/backend/member/RestoreModal.vue";
  import Pagination from "@/components/pagination.vue";

  // PHP API 路徑（比照其他後台頁）
  const phpBaseUrl =
    location.hostname === "localhost" || location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : "/ckd101/g2/php";

  // 會員清單（由後端載入）
  const members = ref([]);

  async function fetchMembers() {
    try {
      const res = await fetch(`${phpBaseUrl}/member/member_manage_get.php`, {
        credentials: "include"
      });
      const data = await res.json();
      if (data.success) {
        members.value = data.members;
      }
    } catch {
      // 讀取失敗時保持空清單
    }
  }

  onMounted(fetchMembers);

  // 篩選 / 搜尋
  const filterType = ref("all");
  const searchName = ref("");

  // 依篩選（所有 / 已停權）+ 搜尋會員名稱過濾
  const filteredMembers = computed(() => {
    return members.value.filter((m) => {
      // 已停權：三種權限任一為受限
      if (filterType.value === "suspended") {
        const suspended =
          m.battleStatus === "restricted" ||
          m.forumStatus === "restricted" ||
          m.marketStatus === "restricted";
        if (!suspended) return false;
      }
      // 搜尋會員名稱（不分大小寫、字串包含）
      const keyword = searchName.value.trim().toLowerCase();
      if (keyword && !m.name.toLowerCase().includes(keyword)) return false;
      return true;
    });
  });

  // 分頁
  const currentPage = ref(1);
  const pageSize = ref(10);
  const total = computed(() => filteredMembers.value.length);

  const pagedMembers = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value;
    return filteredMembers.value.slice(start, start + pageSize.value);
  });

  // 切換篩選 / 搜尋時回到第 1 頁
  watch([filterType, searchName], () => {
    currentPage.value = 1;
  });

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

  async function handleSuspend(payload) {
    // 前端驗證：至少一項範圍、原因必填
    if (!payload.scopes || payload.scopes.length === 0) {
      alert("請至少選擇一項停權範圍");
      return;
    }
    if (!payload.reason || !payload.reason.trim()) {
      alert("請填寫停權原因");
      return;
    }

    try {
      const formData = new FormData();
      formData.append("memberId", payload.memberId);
      formData.append("scopes", payload.scopes.join(","));
      formData.append("reason", payload.reason);
      formData.append("duration", payload.duration);

      const res = await fetch(`${phpBaseUrl}/member/member_suspend_post.php`, {
        method: "POST",
        body: formData,
        credentials: "include"
      });
      const data = await res.json();
      alert(data.message);
      if (data.success) fetchMembers();
    } catch {
      alert("無法連線至伺服器，請稍後再試");
    }
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

  // 篩選工具列（對齊其他後台頁樣式）
  .member-manage-filter {
    width: 100%;
    padding: 20px;

    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px;

    background-color: map-get($color, white);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
  }

  .filter-item {
    position: relative;
    display: flex;
    flex-direction: row;
    align-items: center;

    select {
      min-width: 120px;
      padding: 8px 30px 8px 12px;
      appearance: none;
      -webkit-appearance: none;

      border: 1px solid map-get($color, warmGray);
      border-radius: 10px;
      outline: none;

      background-color: map-get($color, tertiary);
      color: map-get($color, secondary);
      font-size: 14px;
      cursor: pointer;

      transition: border-color 0.24s;

      &:focus {
        border-color: map-get($color, secondary2);
      }
    }

    i {
      position: absolute;
      right: 8px; // 箭頭距右緣 8px
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none; // 點擊穿透到 select

      color: map-get($color, secondary);
      font-size: 14px;
    }
  }

  .filter-search {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    margin-left: auto;

    border: 1px solid map-get($color, warmGray);
    border-radius: 10px;
    background-color: map-get($color, tertiary);

    i {
      color: map-get($color, neutral);
    }

    input {
      width: 240px;
      border: none;
      outline: none;
      background-color: transparent;
      font-size: 14px;
      color: map-get($color, secondary);

      &::placeholder {
        color: map-get($color, hint);
      }
    }
  }

  // 資料表
  .member-table-wrap {
    overflow-x: auto;
  }

  .member-table {
    width: 100%;
    min-width: 640px; // 收縮到此為止，更窄則由外層 wrap 水平捲動

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
    min-width: 0; // 允許在 flex 版面中收縮（RWD）

    &--id {
      flex: 0 1 80px;
    }

    &--name {
      flex: 1 1 120px;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    // 佔比最大，吸收多餘空間 → 把後面的欄位（含操作）推到最右側
    &--account {
      flex: 2 1 180px;
      overflow: hidden;
      white-space: nowrap;
      text-overflow: ellipsis;
    }

    &--status {
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
    justify-content: space-between;
    gap: 8px;
    padding: 12px 20px;

    background-color: map-get($color, white);
    border-top: 1px solid map-get($color, hint);
  }

  .member-table__count {
    font-size: map-get($fontSize, default);
    color: #262626;
  }
</style>
