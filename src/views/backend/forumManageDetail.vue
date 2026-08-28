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

      <div class="article-content tinymce-content" v-html="article.content"></div>

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
            <img v-if="comment.pic" :src="comment.pic" alt="留言圖片" class="comment-pic">
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
import { CATEGORY_LABELS } from "@/assets/js/utils/articleCategory.js";
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl";

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

      article: {},
      comments: []
    };
  },

  async created() {
    const articleId = this.$route.params.id;

    try {
      const res = await fetch(
        `${phpBaseUrl}/forum/getForumManageDetail.php?art_id=${articleId}`
      );
      const result = await res.json();

      if (result.success) {
        const a = result.data.article;
        this.article = {
          id: a.art_id,
          title: a.title,
          author: { name: a.author_name, img: a.author_photo },
          category: CATEGORY_LABELS[a.category] || a.category,
          createTime: a.create_time,
          content: a.content,
          is_show: Number(a.is_show),
          delete_type: a.delete_type,
          remove_reason: a.remove_reason,
          has_pending_appeal: Number(a.has_pending_appeal) === 1,
          reportReason: a.report_reason
        };

        this.comments = result.data.comments.map(c => ({
          id: c.msg_id,
          author: { name: c.commenter_name, img: c.commenter_photo },
          createTime: c.create_time,
          content: c.content,
          pic: c.pic,
          is_show: Number(c.is_show),
          delete_type: c.delete_type,
          remove_reason: c.remove_reason,
          has_pending_appeal: Number(c.has_pending_appeal) === 1
        }));
      } else {
        alert(result.message);
      }
    } catch (err) {
      console.error("取得文章詳情失敗", err);
      alert("取得文章詳情失敗，請稍後再試");
    }
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

    // 下架：需要填寫原因，打開 ConfirmReasonModal
    openRemoveArticleModal() {
      this.modalTitle = "請說明下架原因";
      this.modalTarget = { type: "article", id: this.article.id, action: "REMOVE" };
      this.isModalOpen = true;
    },

    // 恢復上架：不需要填寫原因，改用原生 confirm()，不開 ConfirmReasonModal
    // 注意順序：一定要先設定 modalTarget，再呼叫 handleConfirmAction，
    // 否則 handleConfirmAction 裡讀到的會是舊資料
    openRestoreArticleModal() {
      const confirmed = confirm("確定要恢復這篇文章的上架狀態嗎？");
      if (!confirmed) return;

      this.modalTarget = { type: "article", id: this.article.id, action: "RESTORE" };
      this.handleConfirmAction("");
    },

    openRemoveCommentModal(comment) {
      this.modalTitle = "請說明下架原因";
      this.modalTarget = { type: "comment", id: comment.id, action: "REMOVE" };
      this.isModalOpen = true;
    },

    openRestoreCommentModal(comment) {
      const confirmed = confirm("確定要恢復這則留言的上架狀態嗎？");
      if (!confirmed) return;

      this.modalTarget = { type: "comment", id: comment.id, action: "RESTORE" };
      this.handleConfirmAction("");
    },

    closeModal() {
      this.isModalOpen = false;
    },

    async handleConfirmAction(reason) {
      //物件的解構賦值語法，效果等同於
      // const target = this.modalTarget;
      // const type = target.type;
      // const id = target.id;
      // const action = target.action;
      const { type, id, action } = this.modalTarget;

      const formData = new FormData();
      formData.append("art_id", id);
      formData.append("action", action);
      formData.append("reason", reason);

      try {
        const res = await fetch(
          `${phpBaseUrl}/forum/adminUpdateArticleStatus.php`,
          {
            method: "POST",
            body: formData
          }
        );
        const result = await res.json();

        if (result.success) {
          // 直接操作本地陣列，不整包重新 fetch（比照你自己定案的模式）
          if (type === "article") {
            this.article.is_show = action === "REMOVE" ? 0 : 1;
            this.article.delete_type = action === "REMOVE" ? "admin_removed" : null;
            this.article.remove_reason = action === "REMOVE" ? reason : null;
          }
          alert(result.message);
        } else {
          alert(result.message);
        }
      } catch (err) {
        console.error("處置失敗", err);
        alert("處置失敗，請稍後再試");
      }

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
@use '@/assets/scss/component/tinymceContent' as *;

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
.comment-pic {
  max-width: 200px;
  max-height: 200px;
  width: 100%;
  height: auto;
  border-radius: 8px;
  object-fit: cover;
  display: block;
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