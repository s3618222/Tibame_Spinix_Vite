<template>
   <div class="warningBanner">
      <div class="warning-icon">
         <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div class="warning-content">
         <p>該{{title}}已被下架，下架原因：</p>
         <p>{{remove_reason}}</p>
         <p class="contact_us" v-if="show_contact">
            如果您有對懲處有疑慮，歡迎聯繫我們：
            <a href="#">contact@spinix.com.tw</a>
         </p>
      </div>
   </div>
</template>
<script setup>
   const props = defineProps({
      title:{
         type: String,
         required: true
      },
      remove_reason:{ // 被下架原因
         type: String,
         required: true
      },
      show_contact:{ // 是否顯示「聯繫我們」
         type: Boolean,
         default: false
      }

   });
</script>
<style lang="scss" scoped>
@use '@/assets/scss/_var' as *;

   .warningBanner{
      background-color: map-get($color , lightRed );
      padding: 16px;
      border-radius: 8px;
      box-shadow: 2px 2px 12px rgba(20, 28, 38, 0.1);
      display: flex;
      gap: 8px;

      .fa-triangle-exclamation{
         font-size: 14px;
      }

      .warning-content{
         display: flex;
         flex-direction: column;
         gap: 12px;

         p,a{
            color: map-get($color, error );
            font-weight: 550;
         }

         .contact_us{
            font-size: 12px;

            a:hover{
               text-decoration: underline;
            }
         }
      }
   }
</style>