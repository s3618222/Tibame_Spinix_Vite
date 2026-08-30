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
          <select v-model="formData.category" :class="{ '-isError': fieldErrors.category }" @change="fieldErrors.category = false">
            <option value="" disabled selected>請選擇文章類別...</option>
            <option v-for="opt in categoryOptions" :key="opt.id"  :value="opt.id">{{ opt.name }}</option>
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
            :class="{ '-isError': fieldErrors.title || fieldErrors.titleTooLong }" @input="fieldErrors.title = false; fieldErrors.titleTooLong = false"
          />
        </div>

        <!-- 3. 文章內容 -->
        <div class="form-group">
          <label>文章內容
            <span class="required">*</span>
          </label>
          <div class="custom-editor" :class="{ '-isError': fieldErrors.content }">
            <textarea name="" id="default">{{ formData.content }}</textarea>
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
import {CATEGORY_LABELS} from '@/assets/js/utils/articleCategory.js';
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl";
export default {
  name: "forumForm",

  data() {
    //抓網址列上?後面的值，如果有值就是文章id；如果是null，代表當前是新增模式
    const urlParams = new URLSearchParams(window.location.search);
    let my_articleId = urlParams.get("id");

    return {
      baseUrl: import.meta.env.BASE_URL,
      articleId: my_articleId,
      formData: {
        category: "",
        title: "",
        content: ""
      },

      fieldErrors: {
        category: false,
        title: false,
        titleTooLong: false,
        content: false
      },
      tinymce_init: false,
      currentMemberId: null
  
    };
  },

  computed: {
    categoryOptions(){
      const order = ["unboxing", "event", "chat", "strategy", "faq", "announcement"];
      return order.map(id => ({ id, name: CATEGORY_LABELS[id] }));
    },
    isEdit() {
      return Boolean(this.articleId);
    }
  },

  async created(){
    // 未登入者阻擋
    const isLoggedIn = await this.checkLoginStatus();
    if (!isLoggedIn) return;

    if (this.isEdit) {
      await this.fetchArticleData(this.articleId);
    }
  },

  mounted(){
    // ?後面沒有值，為新增模式，初始化tinymce
    if (!this.isEdit) {
      this.myTinyMCEActivate();
    }
  },

  updated() {
    // 不能用isEdit來判斷，會變成永遠都是true，一直觸發tinymce初始化；而且isEdit是computed的計算結果，不能在其他地方改動；變成變數的話會牽扯到其他判斷邏輯
    // 預設值tinymce還沒初始化，當偵測到畫面變化了(formData抓回的文章資料丟到模板上)，觸發update，執行初始化tinymce，並用是否完成初始化作為旗標，避免使用者增修文章持續觸發tinymce初始化
    if(!this.tinymce_init){
      this.tinymce_init = true;
      this.myTinyMCEActivate();
    }
  },
  

  methods: {
    async checkLoginStatus() {
      try {
        const res = await fetch(`${phpBaseUrl}/member/currentMember_get.php`, {
          credentials: "include"
        });
        const result = await res.json();

        if (!result.isLoggedIn) {
          alert("請先登入才能發表文章");
          window.location.href = `${this.baseUrl}signIn.html`;
          return false;
        }
        
        this.currentMemberId = result.member.id;
        return true;
      } catch (error) {
        console.error("登入狀態確認失敗", error);
        return false;
      }
    },

    async fetchArticleData(id) {
      try {
        const res = await fetch(`${phpBaseUrl}/forum/getArticleById.php?id=${id}`, {
          credentials: "include"
        });
        const result = await res.json();

        if (!result.success) {
          alert(result.message || "找不到這篇文章");
          window.location.href = `${this.baseUrl}forum.html`;
          return;
          
        }
        
        if(Number(this.currentMemberId) !== Number(result.data.mem_id)){
          alert("您沒有權限編輯這篇文章");
          window.location.href = `${this.baseUrl}forum.html`;
          return;
        }

        this.formData = {
          category: result.data.category,
          title: result.data.title,
          content: result.data.content
        };
      } catch (error) {
        console.error("文章資料載入失敗", error);
      }
    },

    validateForm(content) {
      this.fieldErrors.category = !this.formData.category;
      this.fieldErrors.title = this.formData.title.trim() === "";
      this.fieldErrors.titleTooLong = this.formData.title.trim() !== "" && this.formData.title.length > 100;
      this.fieldErrors.content = content.trim() === "";

      const hasError = Object.values(this.fieldErrors).some(isError => isError);

      if (this.fieldErrors.titleTooLong) {
        alert("文章標題不可超過 100 字");
      } else if (hasError) {
        alert("星號*為必填項目");
      }

      return !hasError;
    },

    async handleSubmit() {
      const content = tinymce.get('default').getContent(); // 取得使用者在tinymce裡寫的內容，tinymce預設會回傳字串，不可能是null，所以if條件不需針對null的情況

      // 先驗證表單，再往下走
      if (!this.validateForm(content)) return;

      try{
        const formData = new FormData();
        formData.append("title", this.formData.title);
        formData.append("category", this.formData.category);
        formData.append("content", content);

        //用變數apiUrl 決定要打哪支api
        const apiUrl = this.isEdit
        ? `${phpBaseUrl}/forum/updateArticle.php`
        : `${phpBaseUrl}/forum/addArticle.php`;

        if (this.isEdit) {
          formData.append("art_id", this.articleId);
        }

        const res = await fetch(apiUrl, {
          method: "POST",
          credentials: "include",
          body: formData
        });
        const result = await res.json();

        if(result.success){
          alert(this.isEdit ? "修改成功！" : "發布成功！");
          const redirectId = this.isEdit ? this.articleId : result.data.art_id;
          window.location.href = `${this.baseUrl}forumArticle.html?id=${redirectId}`;

        }else{
          alert(result.message || (this.isEdit ? "修改失敗，請稍後再試" : "發布失敗，請稍後再試"));
        }
      }catch(error){
        console.error(this.isEdit ? "修改文章失敗" : "發布文章失敗", error);
      }
    },

    handleCancel() {
      window.history.back();
    },
    myTinyMCEActivate(){
      tinymce.init({
        selector: 'textarea#default',
        license_key: 'gpl',
        plugins: 'link image lists',
        toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image',
        height: 400,
        menubar: false,
        branding: false,

        block_formats: '內文=p; 標題一=h2; 標題二=h3; 標題三=h4',

        content_style: `
          body {
            background-color: #F7F5F3;
            font-family: "Segoe UI", "Microsoft JhengHei", "PingFang TC", sans-serif;
            font-size: 18px;
            line-height: 1.6;              
            color: #141C26;
          }
          h2 { font-size: 30px; font-weight: 700; margin: 24px 0 12px; color: #141C26; }
          h3 { font-size: 26px; font-weight: 700; margin: 20px 0 10px; color: #141C26; }
          h4 { font-size: 22px; font-weight: 700; margin: 16px 0 8px; color: #141C26; }
          p { margin: 0 0 16px; }          
          ul, ol { padding-left: 24px; margin: 8px 0; }
          li { margin-bottom: 4px; }
          a { color: #fec96b; text-decoration: underline; }
        `,

        setup: (editor) => {
          //on() 是 TinyMCE 提供的方法，意思是「監聽某個事件」
          //'input' 事件,是 TinyMCE 自己定義的事件名稱,意思是 使用者在編輯器裡打字、內容有變動
          editor.on('input', () => {
            this.fieldErrors.content = false;
          });
        },

        images_upload_handler: async (blobInfo) => {
          const formData = new FormData();
            formData.append("file", blobInfo.blob(), blobInfo.filename());
            
            const res = await fetch(`${phpBaseUrl}/forum/uploadArticleImage.php`, {
              method: "POST",
              credentials: "include",
              body: formData
            });
            const result = await res.json();

            if(result.location){
              return result.location;
            }else{
              throw new Error(result.error || "圖片上傳失敗");
            }
          
        }

      });
    }
  }
};
</script>

<style lang="scss" scoped>
@use "sass:map";
@use '@/assets/scss/var' as *;

.-isError {
  border-color: map-get($color, error) !important;
}

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
      border: 1px solid transparent;
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