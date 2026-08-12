<template> 
   <li>
      <div class="img-msg-user">
         <div class="img-user">
            <img :src="image" alt="">
         </div>
      </div>
      <div class="msg-content">
         <div class="user-info">
            <p class="user-name">{{username}}</p>
            <a href="./complaint.html" target="_blank">
               <i class="fa-solid fa-triangle-exclamation"></i>
            </a>
         </div>
         <p class="msg-date">{{postDate}}</p>
         <p class="msg-txt">{{msgtxt}}</p>
      </div>
   </li>         
</template>
<script setup>
   const props = defineProps({
      id: {
         type: [String, Number],
         required: true
      },
      image: {
         type: String,
         required: true
      },
      username: {
         type: String,
         required: true
      },
      postDate: {
         type: String,
         required: true
      },
      msgtxt:{
         type:String,
         required:true
      }
   });
</script>

<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;


   li {
      padding: 12px;
      display: flex;
      border-bottom: 1px solid map-get($color, gray);

      &:last-of-type {
         border: none;
         padding-bottom: 0;
      }

      &:first-of-type {
         border-top: 1px solid map-get($color, gray);
      }

      .img-msg-user {
         padding-right: 12px;
      }

      .msg-content {
         .user-info {
            display: flex;
            gap: 12px;

            .user-name {
               font-weight: 600;
            }
         }

         .msg-date {
            padding-block: 12px;
         }
      }
   }

   @media screen and (width >= 992px) {
      li {
         padding-block: 20px;
      }
   }
</style>