<!-- src/views/PostFormView.vue -->
<template>
  <div class="forum-form-page">
    <div class="form-container">
      
      <!-- 返回列表 -->
      <a :href="`${baseUrl}forum.html`" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> 
        <span>返回論壇列表</span>
      </a>

      <!-- 頁面大標題 -->
      <h1 class="page-title">{{ isEdit ? '編輯貼文' : '發布新貼文' }}</h1>

      <form @submit.prevent="handleSubmit" class="form-card">
        
        <!-- 1. 選擇文章類別 -->
        <div class="form-group">
          <label>選擇文章類別<span class="required">*</span>
          </label>
          <select v-model="formData.category" required>
            <option value="" disabled selected>請選擇文章類別...</option>
            <option value="announcement">公告</option>
            <option value="unboxing">配裝開箱</option>
            <option value="event">賽事情報</option>
            <option value="chat">玩家閒聊</option>
            <option value="chat">戰術討論</option>
            <option value="chat">新手發問</option>
          </select>
        </div>

        <!-- 2. 文章標題 -->
        <div class="form-group">
          <label>文章標題
            <span class="required">*</span>
          </label>
          <input 
            type="text" 
            v-model="formData.title" 
            placeholder="請輸入文章標題..." 
            required 
          />
        </div>

        <!-- 3. 文章內容 -->
        <div class="form-group">
          <label>文章內容
            <span class="required">*</span>
          </label>
          <div class="custom-editor">
            <textarea name="" id="default"></textarea>


            <!-- 頂部功能工具列 -->
            <!-- <div class="editor-toolbar">
              <button type="button" class="toolbar-btn" title="粗體">
                <i class="fa-solid fa-bold"></i>
              </button>
              <button type="button" class="toolbar-btn" title="斜體">
                <i class="fa-solid fa-italic"></i>
              </button>
              <span class="divider"></span>
              <button type="button" class="toolbar-btn" title="插入連結">
                <i class="fa-solid fa-link"></i>
              </button>
              <button type="button" class="toolbar-btn" title="上傳圖片">
                <i class="fa-regular fa-image"></i>
              </button>
            </div> -->

            <!-- 輸入區域 -->
            <!-- <textarea
              v-model="formData.content"
              rows="12"
              placeholder="請輸入貼文內容，分享你的戰鬥陀螺心得、戰術或配裝..."
              required
            ></textarea> -->
          </div>
        </div>

        <!-- 4. 底部按鈕組 -->
        <div class="form-actions">
          <button type="button" class="btn-cancel" @click="handleCancel">取消</button>
          <button type="submit" class="btn-submit">
            {{ isEdit ? '確認修改' : '確認發布' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: "forumForm",

  data() {
    return {
      baseUrl: import.meta.env.BASE_URL,
      articleId: null,
      formData: {
        category: "",
        title: "",
        content: ""
      },
      
  
    };
  },

  computed: {
    isEdit() {
      return Boolean(this.articleId);
    }
  },

  mounted() {
    const urlParams = new URLSearchParams(window.location.search);
    this.articleId = urlParams.get("id");

    if (this.isEdit) {
      this.fetchArticleData(this.articleId);
    }

    tinymce.init({
      selector: 'textarea#default',    // 綁定到id上面
      license_key: 'gpl',
      plugins: 'link image',
      toolbar: 'undo redo | link image'
      // images_upload_url: 'https://notes.webmix.cc/tinymce/upload.php'
    });
  },

  methods: {
    fetchArticleData(id) {
      // 假資料代入
      this.formData = {
        category: "unboxing",
        title: "【心得】關於 B-201 的配重塊最佳化方案",
        content: "這是我經過多次測試後的軸心配置..."
      };
    },

    handleSubmit() {
      alert(this.isEdit ? "修改成功！" : "發布成功！");
      window.location.href = "forum.html";
    },

    handleCancel() {
      window.history.back();
    }
  }
};
</script>

<style lang="scss" scoped>
@use "sass:map";
@use '@/assets/scss/var' as *;

.forum-form-page {
  background-color: map-get($color, tertiary );
  min-height: 100vh;
  padding: 40px 20px 80px;

  .form-container {
    max-width: 800px;
    margin: 0 auto;
  }

  .back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #666;
    text-decoration: none;
    font-size: 14px;
    margin-bottom: 16px;

    &:hover { color: #333; }
  }

  .page-title {
    font-size: map-get($fontSize , h1 );
    font-weight: 600;
    color: #e67e22; /* 搭配主題橘色 */
    margin-bottom: 24px;
  }

  /* 表單白色卡片外框 */
  .form-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    
    label {
      font-weight: bold;
      color: #333;
      font-size: 14px;

      .required {
        color: red;
      }
    }
    
    input[type="text"],
    select {
      width: 100%;
      padding: 12px 16px;
      border-radius: 8px;
      // border: 1px solid #e2e8f0;
      background-color: map-get($color , tertiary );
      font-size: 15px;
      outline: none;

      &:focus {
        border-color: map.get($color, primary);
        background-color: #fff;
      }
    }
  }

  /* -------------------------------------------------------------
   * 關鍵：自訂 Rich Text Editor 視覺外框 (完全還原圖片設計)
   * ------------------------------------------------------------- */
  .custom-editor {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f8f9fa;

    .editor-toolbar {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px 16px;
      background-color: #f1f3f5;
      border-bottom: 1px solid #e2e8f0;

      .toolbar-btn {
        background: none;
        border: none;
        color: #555;
        font-size: 16px;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background 0.2s;

        &:hover {
          background-color: #e9ecef;
          color: #111;
        }
      }

      .divider {
        width: 1px;
        height: 16px;
        background-color: #ccc;
      }
    }

    textarea {
      width: 100%;
      padding: 16px;
      border: none;
      background-color: transparent;
      resize: vertical;
      font-size: 15px;
      line-height: 1.6;
      outline: none;

      &::placeholder {
        color: #aaa;
      }
    }
  }

  /* 底部按鈕 */
  .form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 12px;

    .btn-cancel {
      padding: 10px 24px;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      background: #fff;
      color: #666;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-submit {
      padding: 10px 28px;
      border-radius: 8px;
      border: none;
      background-color: map.get($color, primary);
      color: map.get($color, secondary);
      cursor: pointer;
      font-weight: bold;
    }
  }
}
</style>