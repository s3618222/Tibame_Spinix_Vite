<template> 
   <li :class="statusClass">
      <div class="img-msg-user">
         <div class="img-user">
            <img :src="image" alt="">
         </div>
      </div>
      <div class="msg-content">
         <div class="user-info">
            <div class="user-info-title">
               <div class="user-info-txt">
                  <p class="user-name">{{username}}</p>
                  <p class="msg-date">{{postDate}}</p>
               </div>
               <a href="./complaint.html" target="_blank">
                  <i class="fa-solid fa-triangle-exclamation"></i>
               </a>
            </div>
            <p class="msg-txt">{{msgtxt}}</p>
         </div>
         
         <!-- 交換按鈕 -->
         <div class="btn-choose">
            <button
               class="btnNeutral"
               v-if="isSeller && !isChoose && articleStatus === 'available'"
               type="button"
               @click="$emit('select-applicant', { commentId: id })"
            >跟他交換</button>
            <p
               class="--chosen"
               v-if="isSeller && isChoose"
            >等待對方回覆中</p>
         </div>
      </div>
      
   </li>         
</template>
<script setup>
   import { computed } from 'vue';

   const props = defineProps({
      id: { type: [String, Number], required: true },
      image: { type: String, required: true },
      username: { type: String, required: true },
      postDate: { type: String, required: true },
      msgtxt: { type: String, required: true },
      isMyComment: { type: Boolean, default: false },
      isSeller: { type: Boolean, default: false },
      isChoose: { type: Boolean, default: false },
      articleStatus: { type: String, default: 'available' }
   });

   const statusClass = computed(() => (props.isChoose ? 'li--selected' : ''));
</script>

<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;

   .--chosen{
      color: #A86B00;
      font-weight: 600;
   }

   li {
      padding: 12px;
      display: flex;
      border-bottom: 1px solid map-get($color, gray);
      gap: 12px;

      &.li--selected {
         background-color: #fff3cd;   // 例如淺黃色，代表「已選中」
      }

      &.li--rejected {
         background-color: #f5f5f5;   // 例如灰色，代表「未被選中」
         opacity: 0.6;                 // 也可以加透明度讓它看起來比較不顯眼
      }

      &:last-of-type {
         border: none;
         // padding-bottom: 8px;
         margin-bottom: -12px;
      }

      &:first-of-type {
         border-top: 1px solid map-get($color, gray);
      }


      .msg-content {
         width: 100%;
         display: flex;
         flex-direction: column;
         align-items: start;
         gap: 12px;


         .user-info {
            display: flex;
            gap: 8px;
            flex-direction: column;

            .user-info-title{
               display: flex;
               gap: 8px;

               .user-name {
                  font-weight: 600;
                  font-size: 18px;
               }

               .msg-date {
                  color: map-get($color, neutral );
                  font-size: 14px;
                  padding-top: 4px;
               }
            }
         }
      }
   }

   @media screen and (width >= 992px) {
      li {
         padding-block: 20px;

         .msg-content{
            flex-direction: row;
            align-items: center;

            .user-info{
               flex: 1;

               .user-info-txt{
                  display: flex;
                  gap: 8px;
                  align-items: center;

                  .msg-date{
                     padding: 0;
                  }
               }
            }
         }
      }
      
   }
</style>