<template>
  <section class="forum-detail">
    <RouterLink :to="{ name: 'backend-forum' }" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      <span>返回文章管理列表</span>
    </RouterLink>

    <article class="article-card">
      <div class="badge-group">
        <span class="status-badge" :class="getStatusClass(article)">{{ getStatusText(article) }}</span>
        <span v-if="article.has_pending_appeal" class="status-badge status-badge--appeal">待審申訴</span>
        <span class="badge-neutral">ID：{{ article.id }}</span>
      </div>

      <p v-if="showRemoveReason(article)" class="remove-reason-text">下架原因：{{ article.remove_reason }}</p>

      <div class="article-header">
        <h1 class="article-title">{{ article.title }}</h1>
        <span v-if="getActionState(article) === 'appeal'" class="appeal-hint">
          <i class="fa-solid fa-circle-info"></i>
          此文章有待審申訴，請至申訴管理處理
        </span>
        <button
          v-else-if="getActionState(article) === 'remove'"
          type="button"
          class="btn-remove"
          @click="openRemoveArticleModal"
        >
          <i class="fa-solid fa-ban"></i>
          下架文章
        </button>
        <button
          v-else
          type="button"
          class="btn-restore"
          @click="openRestoreArticleModal"
        >
          <i class="fa-solid fa-rotate-left"></i>
          恢復上架
        </button>
      </div>

      <div class="article-meta">
        <div class="author-row">
          <img
            class="author-avatar"
            :src="getAvatarUrl(article.author.img)"
            :alt="article.author.name"
          >
          <span class="author-name">{{ article.author.name }}</span>
        </div>
        <span class="chip chip--neutral">{{ article.category }}</span>
        <span class="meta-time">
          <i class="fa-regular fa-clock"></i>
          {{ article.createTime }}
        </span>
      </div>

      <div class="article-content">
        <p v-for="(paragraph, index) in article.content" :key="index">{{ paragraph }}</p>
      </div>

      <div v-if="article.has_pending_appeal" class="report-box">
        <h3 class="report-box-title">
          <i class="fa-solid fa-triangle-exclamation"></i>
          檢舉詳情
        </h3>
        <p class="report-box-text">{{ article.reportReason }}</p>
      </div>
    </article>

    <section class="comment-card">
      <div class="comment-card-header">
        <h2>留言歷史紀錄</h2>
        <p>共 {{ comments.length }} 則留言</p>
      </div>

      <div class="comment-list">
        <div class="comment-row" v-for="comment in comments" :key="comment.id">
          <img
            class="comment-avatar"
            :src="getAvatarUrl(comment.author.img)"
            :alt="comment.author.name"
          >

          <div class="comment-main">
            <div class="comment-meta">
              <span class="comment-name">{{ comment.author.name }}</span>
              <span class="comment-time">{{ comment.createTime }}</span>
              <span class="status-badge" :class="getStatusClass(comment)">{{ getStatusText(comment) }}</span>
              <span v-if="comment.has_pending_appeal" class="status-badge status-badge--appeal">待審申訴</span>
            </div>
            <p class="comment-content">{{ comment.content }}</p>
            <p v-if="showRemoveReason(comment)" class="remove-reason-text">下架原因：{{ comment.remove_reason }}</p>
          </div>

          <span v-if="getActionState(comment) === 'appeal'" class="appeal-hint">
            <i class="fa-solid fa-circle-info"></i>
            此留言有待審申訴，請至申訴管理處理
          </span>
          <button
            v-else-if="getActionState(comment) === 'remove'"
            type="button"
            class="btn-remove"
            @click="openRemoveCommentModal(comment)"
          >
            <i class="fa-solid fa-ban"></i>
            下架留言
          </button>
          <button
            v-else
            type="button"
            class="btn-restore"
            @click="openRestoreCommentModal(comment)"
          >
            <i class="fa-solid fa-rotate-left"></i>
            恢復上架
          </button>
        </div>
      </div>
    </section>

    <ConfirmReasonModal
      :visible="isModalOpen"
      :title="modalTitle"
      @cancel="closeModal"
      @confirm="handleConfirmAction"
    />
  </section>
</template>

<script>
import ConfirmReasonModal from "@/components/common/ConfirmReasonModal.vue";

export default {
  name: "ForumManageDetail",

  components: {
    ConfirmReasonModal
  },

  data() {
    return {
      isModalOpen: false,
      modalTitle: "請說明下架原因",
      modalTarget: null, // { type: "article" | "comment", id, action: "remove" | "restore" }

      // 文章詳情假資料
      article: {
        id: 1,
        title: "WX-01爆裂天龍最佳配重環測試報告",
        author: { name: "Blader_X", img: "" },
        category: "零件搭配",
        createTime: "2026-05-12 14:30",
        content: [
          "各位戰鬥陀螺好手們，今天來分享一下近期在競技場勝率極高的 WX-01 爆裂天龍 攻擊特化配裝。這套配置主打開局高爆發，適合喜歡速戰速決的玩家。",
          "經過 50 場實測後整理出以下心得：核心配置為結晶輪盤 WX-01 爆裂天龍，搭配鐵盤 0 (Zero) 提供極致的重量與穩定性，軸心選用 X (Xtreme) 橡膠平底，爆發力最強但續航極差。",
          "如果對戰對手是防禦特化型，建議搭配較重的固鎖環以提升抗撞擊能力，開局互撞時較不容易被直接彈飛出場。"
        ],
        // 本頁僅有單一 article 物件，同一時間只能呈現一種狀態組合，故先示範
        // 「上架中 + 有待審申訴」這組最能驗證按鈕隱藏規則的組合。若要手動測試
        // 其餘三種組合，可暫時替換下面四個欄位：
        //   已下架（admin_removed）：is_show:0, delete_type:'admin_removed', remove_reason:'違反社群規範，經管理員審核下架', has_pending_appeal:false
        //   使用者已刪除（self_deleted）：is_show:0, delete_type:'self_deleted', remove_reason:null, has_pending_appeal:false
        //   上架中且無待審申訴：is_show:1, delete_type:null, remove_reason:null, has_pending_appeal:false
        is_show: 1,
        delete_type: null,
        remove_reason: null,
        has_pending_appeal: true,
        reportReason: "這篇文章的測試數據疑似造假，圖片來源不明，且部分內容涉嫌置入廣告連結，請管理員協助查核。"
      },

      // 留言列表假資料
      comments: [
        {
          id: 101,
          author: { name: "打野戰神", img: "" },
          createTime: "2026-05-12 15:38",
          content: "這套配裝我實測過，爆發力真的很強，但續航確實偏弱，比賽後期容易被穩定型對手翻盤。",
          is_show: 1,
          delete_type: null,
          remove_reason: null,
          has_pending_appeal: false
        },
        {
          id: 102,
          author: { name: "陀螺新手阿翔", img: "" },
          createTime: "2026-05-12 16:05",
          content: "請問這篇文章的數據是自己測的嗎？感覺跟官方公佈的數值差很多，該不會是騙人的吧！！！",
          is_show: 1,
          delete_type: null,
          remove_reason: null,
          has_pending_appeal: true
        },
        {
          id: 103,
          author: { name: "配裝控", img: "" },
          createTime: "2026-05-12 17:20",
          content: "感謝分享，想請問防禦型配裝有推薦的固鎖環嗎？想搭配鐵盤試試看穩定度。",
          is_show: 1,
          delete_type: null,
          remove_reason: null,
          has_pending_appeal: false
        },
        {
          id: 104,
          author: { name: "收藏家A", img: "" },
          createTime: "2026-05-13 09:12",
          content: "這根本是抄襲隔壁論壇的文章，內容一模一樣，檢舉！",
          is_show: 0,
          delete_type: "admin_removed",
          remove_reason: "內容重複張貼且涉及抄襲指控，經管理員審核後下架",
          has_pending_appeal: false
        },
        {
          id: 105,
          author: { name: "匿名玩家", img: "" },
          createTime: "2026-05-13 10:40",
          content: "這篇分析我覺得太武斷了，附上我自己的實測影片連結給大家參考。",
          is_show: 0,
          delete_type: "self_deleted",
          remove_reason: null,
          has_pending_appeal: false
        }
      ]
    };
  },

  methods: {
    // 複製 authorCard.vue 的頭貼 fallback 邏輯：文章作者列與留言列表都只需要簡單的頭貼橫條，
    // 不需要 AuthorCard 內建的卡片版型與桌機/手機切換樣式，因此這裡不掛載該元件，改為複用同一套邏輯
    getAvatarUrl(img) {
      const baseUrl = import.meta.env.BASE_URL;
      return img
        ? `${baseUrl}${img}`
        : `${baseUrl}spinix_member_default.png`;
    },

    openRemoveArticleModal() {
      this.modalTitle = "請說明下架原因";
      this.modalTarget = { type: "article", id: this.article.id, action: "remove" };
      this.isModalOpen = true;
    },

    openRestoreArticleModal() {
      this.modalTitle = "請說明恢復上架原因";
      this.modalTarget = { type: "article", id: this.article.id, action: "restore" };
      this.isModalOpen = true;
    },

    openRemoveCommentModal(comment) {
      this.modalTitle = "請說明下架原因";
      this.modalTarget = { type: "comment", id: comment.id, action: "remove" };
      this.isModalOpen = true;
    },

    openRestoreCommentModal(comment) {
      this.modalTitle = "請說明恢復上架原因";
      this.modalTarget = { type: "comment", id: comment.id, action: "restore" };
      this.isModalOpen = true;
    },

    closeModal() {
      this.isModalOpen = false;
    },

    handleConfirmAction(reason) {
      console.log(reason, this.modalTarget);
      this.isModalOpen = false;
    },

    // 依 is_show / delete_type 轉換成畫面顯示用的真實狀態文字，文章與留言共用
    getStatusText(item) {
      if (item.is_show === 1) {
        return "上架中";
      }
      if (item.delete_type === "admin_removed") {
        return "已下架";
      }
      if (item.delete_type === "self_deleted") {
        return "使用者已刪除";
      }
      return "";
    },

    // 依 is_show / delete_type 轉換成畫面顯示用的 badge class，文章與留言共用
    getStatusClass(item) {
      if (item.is_show === 1) {
        return "status-badge--success";
      }
      if (item.delete_type === "admin_removed") {
        return "status-badge--error";
      }
      if (item.delete_type === "self_deleted") {
        return "status-badge--muted";
      }
      return "";
    },

    // 待審申訴優先權最高：有待審申訴一律隱藏下架/恢復按鈕，其次才依上架狀態決定顯示哪個按鈕
    getActionState(item) {
      if (item.has_pending_appeal) {
        return "appeal";
      }
      return item.is_show === 1 ? "remove" : "restore";
    },

    // 只有「管理員主動下架」才顯示下架原因，使用者自行刪除不顯示
    showRemoveReason(item) {
      return item.is_show === 0 && item.delete_type === "admin_removed";
    }
  }
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.forum-detail {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;

  color: map-get($color, neutral);
  text-decoration: none;
  font-size: 14px;

  &:hover {
    color: map-get($color, secondary);
  }
}

/* 文章卡片 */
.article-card {
  padding: 32px;
  border-radius: 16px;

  background-color: map-get($color, white);
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);

  display: flex;
  flex-direction: column;
  gap: 20px;
}

.badge-group {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}

// 中性灰底膠囊：用於「上架狀態」「ID」這類單純資訊展示，非狀態語意，
// 因此不重用 status-badge--disabled（該樣式語意為「停用/下架」，跟「上架」相反，混用會誤導）
.badge-neutral {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  padding: 4px 12px;
  border: 1px solid transparent;
  border-radius: 999px;

  background-color: #eeeeee;
  color: map-get($color, neutral);

  font-size: 14px;
  line-height: 1.4;
  white-space: nowrap;
}

// 本頁面專屬的 status-badge modifier（_mixin.scss 沒有對應語意的既有樣式，
// 故不動 _mixin.scss，改在此 scoped 樣式內新增，尺寸/形狀比照 .status-badge 本體，
// 顏色皆取自 _var.scss 既有變數，未新增任何新色碼）。

// 灰階：使用者已自行刪除
.status-badge--muted {
  border-color: rgba(100, 116, 139, 0.4);
  background-color: map-get($color, gray);
  color: map-get($color, neutral);
}

// 橘黃色：待審申訴提醒，與 status-badge--error 的紅色區隔，避免跟「已下架」搞混
.status-badge--appeal {
  border-color: rgba(254, 201, 107, 0.75);
  background-color: map-get($color, pending);
  color: map-get($color, brown);
}

// 待審申訴時，取代下架/恢復按鈕的小型提示文字（刻意不做成 .report-box 那種大區塊）。
// 這裡一定要給 max-width：flex-shrink:0 只代表「不被別人擠壓」，不代表文字會換行，
// 沒有 max-width 的話這段文字會用單行不換行的最大寬度去佔位，反而把旁邊的
// comment-content／article-title 擠得比原本按鈕還窄，所以用 max-width 讓寬度
// 固定在跟按鈕相近的量級，超出的文字改成換行，而不是撐開寬度。
.appeal-hint {
  flex-shrink: 0;
  max-width: 140px;

  display: inline-flex;
  align-items: flex-start;
  gap: 6px;

  color: map-get($color, brown);
  font-size: 13px;
  line-height: 1.5;
  white-space: normal;

  i {
    flex-shrink: 0;
  }
}

// 下架原因文字，只在管理員主動下架時顯示
.remove-reason-text {
  margin: 0;
  color: map-get($color, error);
  font-size: 13px;
  line-height: 1.6;
}

.article-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
}

.article-title {
  min-width: 0;
  margin: 0;
  color: map-get($color, secondary);
  font-size: map-get($fontSize, h3);
  font-weight: 700;
  line-height: 1.4;
}

.article-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
}

.author-row {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.author-avatar {
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
}

.author-name {
  color: map-get($color, secondary);
  font-weight: 600;
  font-size: 14px;
}

.meta-time {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: map-get($color, neutral);
  font-size: 14px;
}

.article-content {
  display: flex;
  flex-direction: column;
  gap: 12px;

  p {
    margin: 0;
    color: map-get($color, secondary);
    font-size: 16px;
    line-height: 1.8;
  }
}

.report-box {
  padding: 16px 20px;

  border: 1px solid rgba(230, 57, 70, 0.35);
  border-radius: 12px;
  background-color: map-get($color, lightRed);
}

.report-box-title {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 8px;

  color: map-get($color, error);
  font-size: 16px;
  font-weight: 700;
}

.report-box-text {
  margin: 0;
  color: map-get($color, secondary);
  font-size: 14px;
  line-height: 1.6;
}

/* 共用：下架動作按鈕（外框紅色、圖示+文字） */
.btn-remove {
  flex-shrink: 0;

  display: inline-flex;
  align-items: center;
  gap: 8px;

  padding: 8px 16px;
  border: 1px solid map-get($color, error);
  border-radius: 8px;

  background-color: map-get($color, white);
  color: map-get($color, error);

  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background-color: map-get($color, error);
    color: map-get($color, white);
  }
}

/* 共用：恢復上架動作按鈕（外框綠色、圖示+文字，樣式比照 .btn-remove，顏色改用
   map-get($color, olive) 表示正向動作，與 status-badge--success 使用同一組既有綠色變數） */
.btn-restore {
  flex-shrink: 0;

  display: inline-flex;
  align-items: center;
  gap: 8px;

  padding: 8px 16px;
  border: 1px solid map-get($color, olive);
  border-radius: 8px;

  background-color: map-get($color, white);
  color: map-get($color, olive);

  font-size: 14px;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background-color: map-get($color, olive);
    color: map-get($color, white);
  }
}

/* 留言卡片 */
.comment-card {
  padding: 32px;
  border-radius: 16px;

  background-color: map-get($color, white);
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);

  display: flex;
  flex-direction: column;
  gap: 20px;
}

.comment-card-header {
  display: flex;
  align-items: baseline;
  gap: 12px;

  h2 {
    margin: 0;
    color: map-get($color, secondary);
    font-size: map-get($fontSize, h4);
    font-weight: 700;
  }

  p {
    margin: 0;
    color: map-get($color, neutral);
    font-size: 14px;
  }
}

.comment-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.comment-row {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding-top: 20px;
  border-top: 1px solid map-get($color, warmGray);

  &:first-child {
    padding-top: 0;
    border-top: 0;
  }
}

// 留言列表用的小頭貼（純 img，不是 AuthorCard 元件，見 script 註解）
.comment-avatar {
  flex-shrink: 0;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  object-fit: cover;
}

.comment-main {
  min-width: 0;
  flex: 1;

  display: flex;
  flex-direction: column;
  gap: 6px;
}

.comment-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

.comment-name {
  color: map-get($color, secondary);
  font-weight: 600;
  font-size: 14px;
}

.comment-time {
  color: map-get($color, neutral);
  font-size: 13px;
}

.comment-content {
  margin: 0;
  color: map-get($color, secondary);
  font-size: 14px;
  line-height: 1.7;
}

@media screen and (max-width: 576px) {
  .article-card,
  .comment-card {
    padding: 20px;
  }

  .article-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .comment-row {
    flex-wrap: wrap;
  }

  .btn-remove,
  .btn-restore,
  .appeal-hint {
    margin-left: auto;
  }
}
</style>
