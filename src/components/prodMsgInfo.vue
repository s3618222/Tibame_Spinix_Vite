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
            <p class="msg-date">{{postDate}}</p>
            <a href="./complaint.html" target="_blank">
               <i class="fa-solid fa-triangle-exclamation"></i>
            </a>
         </div>
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
      gap: 12px;

      &:last-of-type {
         border: none;
         padding-bottom: 0;
      }

      &:first-of-type {
         border-top: 1px solid map-get($color, gray);
      }


      .msg-content {
         display: flex;
         flex-direction: column;
         gap: 12px;
         .user-info {
            display: flex;
            gap: 12px;
            align-items: end;

            .user-name {
               font-weight: 600;
               font-size: 18px;
            }
         }

         .msg-date {
            color: map-get($color, neutral );
            font-size: 14px;
         }
      }
   }

   @media screen and (width >= 992px) {
      li {
         padding-block: 20px;
      }
   }
</style>