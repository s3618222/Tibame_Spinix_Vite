<template>
  <div class="comment-form-card">
    <div v-if="isLogin" class="login-form">
      123
      <div id="default">
        
      </div>
      
    </div>
    <div v-else class="logout-box">
      <a href="" class="login-area">想發表你的看法嗎？登入會員即可參與討論</a>
    </div>
    
  </div>
  
</template>

<script>
  export default {
    name: "ArticleMain",

    data(){
      return {
        isLogin: false

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

</style>