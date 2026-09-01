<template>
  <section class="beyblade-manage">
    <div class="beyblade-manage-header">
      <h2 class="beyblade-manage-title">陀螺圖庫管理</h2>
    </div>

    <!-- 篩選工具列 + 新增零件按鈕 -->
    <div class="beyblade-manage-toolbar">
      <div class="beyblade-manage-filter">
        <div class="filter-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input
            type="text"
            v-model="filters.keyword"
            placeholder="搜尋零件名稱、ID..."
          >
        </div>

        <div class="filter-item">
          <select v-model="filters.category">
            <option value="">全部類別</option>
            <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>

        <div class="filter-item">
          <select v-model="filters.status">
            <option value="">全部狀態</option>
            <option value="使用中">使用中</option>
            <option value="已停用">已停用</option>
          </select>
        </div>
      </div>

      <button type="button" class="btn-add-part" @click="handleAddPart">
        新增零件
      </button>
    </div>

    <!-- 資料表卡片 -->
    <div class="beyblade-manage-list">
      <div class="beyblade-table-wrap">
        <div class="beyblade-row beyblade-row--head">
          <div class="col col-id">零件編號</div>
          <div class="col col-preview">零件預覽</div>
          <div class="col col-name">零件名稱</div>
          <div class="col col-category">零件類別</div>
          <div class="col col-attack">攻擊</div>
          <div class="col col-defense">防禦</div>
          <div class="col col-status">上架狀態</div>
          <div class="col col-action">操作面板</div>
        </div>

        <template v-if="paginatedParts.length">
          <div class="beyblade-row" v-for="part in paginatedParts" :key="part.id">
            <div class="col col-id">{{ part.code }}</div>
            <div class="col col-preview">
              <div class="beyblade-thumb" :class="{ 'beyblade-thumb--disabled': !part.is_show }">
                <img v-if="getBeybladeImageUrl(part.pic) && !imgFailedMap[part.id]"
                :src="getBeybladeImageUrl(part.pic)" 
                :alt="part.name"
                @error="imgFailedMap[part.id] = true">
                <i v-else class="fa-solid fa-image" aria-hidden="true"></i>
              </div>
            </div>
            <div class="col col-name">{{ part.name }}</div>
            <div class="col col-category">
              <span class="chip" :class="getCategoryChipClass(part.category)">{{ part.category }}</span>
            </div>
            <div class="col col-attack">{{ part.attack }}</div>
            <div class="col col-defense">{{ part.defense }}</div>
            <div class="col col-status">
              <span
                class="status-badge"
                :class="part.is_show ? 'status-badge--success' : 'status-badge--error'"
              >
                {{ part.is_show ? '使用中' : '已停用' }}
              </span>
            </div>
            <div class="col col-action">
              <RouterLink
                :to="{ name: 'backend-beyblade-edit', params: { id: part.id } }"
                class="btn-view"
              >
                查看
              </RouterLink>
            </div>
          </div>
        </template>

        <p v-else class="beyblade-manage-empty">目前沒有符合條件的零件資料</p>
      </div>

      <div class="beyblade-manage-list-footer">
        <p>顯示 {{ displayRangeStart }}-{{ displayRangeEnd }} 筆，共 {{ filteredParts.length }} 筆</p>

        <div class="beyblade-manage-paginator">
          <Pagination
            v-model:current-page="currentPage"
            :page-size="pageSize"
            :total="filteredParts.length"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import Pagination from "@/components/pagination.vue";
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";

export default {
  name: "BeybladeManage",

  components: {
    Pagination
  },

  data() {
    return {
      baseUrl: import.meta.env.BASE_URL,

      currentPage: 1,
      pageSize: 5,

      categoryLabelMap: {
        Blade: "戰刃",
        Ratchet: "固鎖",
        Bit: "軸心"
      },

      filters: {
        keyword: "",
        category: "",
        status: ""
      },

      parts: [],

      imgFailedMap: {}
    };
  },

  computed: {
    filteredParts() {
      const keyword = this.filters.keyword.trim().toLowerCase();

      return this.parts.filter(part => {
        const matchKeyword =
          !keyword ||
          part.name.toLowerCase().includes(keyword) ||
          part.code.toLowerCase().includes(keyword);

        const matchCategory =
          !this.filters.category ||
          part.category === this.filters.category;

        const matchStatus =
          !this.filters.status ||
          (this.filters.status === "使用中" ? part.is_show : !part.is_show);

        return matchKeyword && matchCategory && matchStatus;
      });
    },

    paginatedParts() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredParts.slice(start, start + this.pageSize);
    },

    displayRangeStart() {
      if (this.filteredParts.length === 0) {
        return 0;
      }
      return (this.currentPage - 1) * this.pageSize + 1;
    },

    displayRangeEnd() {
      return Math.min(this.currentPage * this.pageSize, this.filteredParts.length);
    },

    categoryOptions() {
      //Object.values：把一個物件裡所有的「值」抓出來,轉換成一個真正的陣列
      return Object.values(this.categoryLabelMap);
    }
  },

  watch: {
    filters: {
      handler() {
        this.currentPage = 1;
      },
      deep: true
    }
  },

  async created() {
    try {
      const res = await fetch(`${phpBaseUrl}/build/getBeybladeManageList.php`, {
        credentials: "include"
      });
      const result = await res.json();

      if (result.success) {
        this.parts = result.data.map(item => ({
          id: item.beyblade_id,
          code: `#P-${item.beyblade_id}`,
          name: item.name,
          category: this.categoryLabelMap[item.category] ?? item.category,
          attack: Number(item.attack),
          defense: Number(item.defense),
          pic: item.pic,
          is_show: Number(item.is_show)
        }));
      } else {
        alert(result.message);
      }
    } catch (error) {
      console.error("零件列表載入失敗", error);
      alert("零件列表載入失敗，請稍後再試");
    }
  },

  methods: {
    getCategoryChipClass(category) {
      const map = {
        戰刃: "chip--state",
        固鎖: "chip--category",
        軸心: "chip--neutral"
      };

      return map[category] || "chip--neutral";
    },

    handleAddPart() {
      this.$router.push({ name: "backend-beyblade-new" });
    },

    getBeybladeImageUrl(pic) {
      if (!pic) {
        return null;
      }
      if (pic.startsWith("uploads/beyblade/")) {
        return `${phpBaseUrl}/${pic}`;
      }
      return this.baseUrl + pic;
    },
  }
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.beyblade-manage {
  width: 100%;
}

.beyblade-manage-title {
  color: map-get($color, secondary2);
  font-weight: 600;
  font-size: map-get($fontSize, h1);
  margin-bottom: 28px;
}

/* 篩選工具列 + 新增零件按鈕 */
.beyblade-manage-toolbar {
  width: 100%;
  margin-bottom: 32px;

  display: flex;
  align-items: center;
  gap: 16px;
}

.beyblade-manage-filter {
  flex: 1 0 0;
  min-width: 0;
  padding: 16px;

  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px;

  background-color: map-get($color, white);
  border-radius: 12px;
  box-shadow: 0 4px 10px rgba(20, 28, 38, 0.05);
}

.filter-search {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1 0 0;
  min-width: 200px;
  padding: 10px 16px;

  border: 1px solid map-get($color, gray);
  border-radius: 8px;
  background-color: map-get($color, tertiary);

  i {
    color: map-get($color, neutral);
  }

  input {
    width: 100%;
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

.filter-item select {
  min-width: 140px;
  padding: 8px 30px 8px 12px;

  border: 1px solid #ddd6c8;
  border-radius: 10px;
  outline: none;

  background-color: map-get($color, tertiary);
  color: map-get($color, secondary);
  font-size: 14px;

  transition: border-color 0.24s;

  &:focus {
    border-color: map-get($color, secondary2);
  }
}

.btn-add-part {
  flex-shrink: 0;
  padding: 10px 24px;

  border: none;
  border-radius: 8px;

  background-color: map-get($color, primary);
  color: map-get($color, secondary);

  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
  transition: background-color 0.24s;

  &:hover {
    background-color: darken(#fec96b, 6%);
  }
}

/* 資料表卡片 */
.beyblade-manage-list {
  width: 100%;

  border-radius: 12px;
  overflow: hidden;

  background-color: map-get($color, white);
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
}

.beyblade-table-wrap {
  width: 100%;
  overflow-x: auto;
}

.beyblade-row {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 860px;
  padding: 12px 16px;

  &:not(.beyblade-row--head) {
    border-top: 1px solid rgba(228, 226, 224, 0.5);
    transition: background-color 0.2s;

    &:hover {
      background-color: map-get($color, tertiary);
    }
  }
}

.beyblade-row--head {
  background-color: rgba(254, 201, 107, 0.4);
  font-weight: 600;
  color: map-get($color, secondary);
}

.col {
  min-width: 0;
  font-size: 14px;
  color: map-get($color, secondary);
  text-align: center;
}

// 各欄 flex-grow 依原本固定寬度比例分配（90/90/140/100/80/80/120/90 → 除以 10），
// 讓寬螢幕下多餘空間依比例分散到每一欄，而不是被 .col-name 用 flex:1 獨吞。
// 第三個參數維持原本的固定寬度值作為 flex-basis，欄位對齊方式不受影響。
.col-id {
  flex: 9 0 90px;
}

.col-preview {
  flex: 9 0 90px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.col-name {
  flex: 14 0 140px;
}

.col-category {
  flex: 10 0 100px;
}

.col-attack,
.col-defense {
  flex: 8 0 80px;
  text-align: right;
}

.col-status {
  flex: 12 0 120px;
  text-align: center;
}

.col-action {
  flex: 9 0 90px;
  text-align: center;
}

.btn-view {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  padding: 5px 16px;

  border: 1px solid map-get($color, secondary);
  border-radius: 8px;

  background-color: transparent;
  color: map-get($color, secondary);

  font-size: 14px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background-color: map-get($color, secondary);
    color: map-get($color, white);
  }
}

/* 零件縮圖 */
.beyblade-thumb {
  width: 48px;
  height: 48px;
  flex-shrink: 0;

  overflow: hidden;
  border: 1px solid rgba(228, 226, 224, 0.5);
  border-radius: 8px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);

  background-color: #efedeb;

  display: flex;
  align-items: center;
  justify-content: center;

  img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
  }

  i {
    font-size: 20px;
    color: map-get($color, neutral);
  }

  // 已停用零件：縮圖灰階降飽和 + 80% 透明度
  &--disabled {
    opacity: 0.8;

    img {
      filter: grayscale(100%);
    }
  }
}

// 本頁面內覆蓋分類 chip 的膠囊比例，讓它跟 .status-badge 呈現一致的寬高比例
// （border-radius: 999px、padding: 4px 12px，數值取自 .status-badge 本體）。
// 因為此區塊是 <style scoped>，Vue 編譯時會自動幫這些選擇器加上本元件專屬的
// data-v-xxxxxxxx attribute selector，只會命中本頁面渲染出的節點，不影響其他頁面
// （如 ProdDetail.vue／productCard.vue／forumManage.vue）用到的同名 chip class。
.chip--state,
.chip--category,
.chip--neutral {
  border-radius: 999px;
  padding: 4px 12px;
}

.beyblade-manage-empty {
  padding: 48px 16px;
  text-align: center;
  color: map-get($color, neutral);
}

.beyblade-manage-list-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 16px;

  padding: 16px 20px;
  border-top: 1px solid map-get($color, warmGray);

  p {
    font-size: 14px;
    color: map-get($color, secondary);
    white-space: nowrap;
  }
}

.beyblade-manage-paginator {
  display: flex;
  justify-content: flex-end;
}

@media screen and (max-width: 768px) {
  .beyblade-manage-toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .btn-add-part {
    width: 100%;
  }

  .beyblade-manage-filter {
    padding: 16px;
  }

  .filter-item select {
    min-width: 0;
    width: 100%;
  }
}

@media screen and (max-width: 576px) {
  .beyblade-manage-filter {
    flex-direction: column;
    align-items: stretch;
  }

  .beyblade-manage-list-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .beyblade-manage-paginator {
    justify-content: center;
  }
}
</style>
