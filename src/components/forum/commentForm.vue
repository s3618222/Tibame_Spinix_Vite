<template>
  <div class="comment-form-card">
    <div v-if="isLoggedIn" class="login-form">
      <textarea
        v-model="commentText"
        placeholder="撰寫回覆..."
        class="comment-textarea"
      ></textarea>
      <div class="form-footer">
        <label class="upload-btn">
          <i class="fa-regular fa-image"></i>
          <input type="file" accept="image/*" @change="handleFileChange" hidden>
        </label>
        <button type="button" class="btnFill" @click="handleSubmit" :disabled="!commentText.trim()">
          <i class="fa-solid fa-paper-plane"></i>
          送出回覆
        </button>
      </div>
    </div>
    <div v-else class="logout-box">
      <a :href="`${baseUrl}signIn.html`" class="login-area">想發表你的看法嗎？登入會員即可參與討論</a>
    </div>

  </div>

</template>

<script>
  export default {
    name: "CommentForm",

    props: {
      isLoggedIn: {
        type: Boolean,
        default: true
      }
    },

    emits: ["submit-comment"],

    data(){
      return {
        baseUrl: import.meta.env.BASE_URL,
        commentText: "",
        commentImage: null
      }
    },

    methods: {
      handleFileChange(event) {
        this.commentImage = event.target.files[0] || null;
      },
      handleSubmit() {
        if (!this.commentText.trim()) return;
        this.$emit("submit-comment", {
          content: this.commentText.trim(),
          image: this.commentImage
        });
        this.commentText = "";
        this.commentImage = null;
      }
    }
  }

</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.logout-box {
  background-color: #fff;
  box-shadow: 3px 3px 8px 3px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  height: 200px;
  width: 100%;
  // height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

a.login-area {
  display: inline-flex;
  width: 95%;
  height: 85%;

  justify-content: center;
  align-items: center;
  background-color: map-get($color, tertiary );
  text-align: center;
  border-radius: 12px;
}

.login-form {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  align-self: stretch;
  padding: 24px;
  gap: 24px;
  border-radius: 16px;
  border: 1px solid rgba(228, 226, 224, 0.50);
  background: #FFFFFF;
  box-shadow: 0 4px 20px 0 rgba(20, 28, 38, 0.05);
  width: 100%;
}

.comment-textarea {
  width: 100%;
  resize: vertical;
  border: 1px solid transparent;
  outline: none;
  font-family: inherit;
  font-size: 14px;
  min-height: 60px;
  padding: 8px;
  border-radius: 8px;
  background-color: map-get($color, tertiary);
  transition: background-color 0.2s, border-color 0.2s;

  &::placeholder {
    color: map-get($color, neutral);
  }

  &:focus {
    background-color: #fff;
    border-color: map-get($color, primary);
  }
}

.form-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.upload-btn {
  display: inline-flex;
  align-items: center;
  cursor: pointer;
  color: map-get($color, neutral);
  font-size: 18px;
}

.btnFill:disabled {
  background-color: lighten(map-get($color, primary), 15%);
  cursor: not-allowed;
  opacity: 0.7;
}

</style>