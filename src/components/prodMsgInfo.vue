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
               <a href="./complaint.html" target="_blank" v-if="!isMyComment">
                  <i class="fa-solid fa-triangle-exclamation"></i>
               </a>
            </div>
            <p class="msg-txt">{{msgtxt}}</p>
            <div class="applyer-contact">
               <div class="drawer-item">
                  <ContactDrawer
                  :contact="contact"
                  :show="articleStatus === 'exchanging'"
                  
               />
               </div>
               <div v-if="isOwner && isChoose && articleStatus === 'exchanging'" class="exchange-actions">
                  <button 
                     class="btnFill"
                     @click="handleCompleteExchange">
                     完成交換
                  </button>
                  <button 
                     class="btnRemoveNoFill"
                     @click="handleCancelExchange">
                     取消交換
                  </button>
               </div>
            </div>
            
         </div>
         
         <!-- 交換按鈕 -->
         <div class="btn-choose">
            <button
               class="btnNeutral"
               v-if="isOwner && !isChoose && articleStatus === 'available'"
               type="button"
               @click="$emit('select-applicant', { commentId: id })"
            >跟他交換</button>
            <p
               class="--chosen"
               v-if="isOwner && isChoose && articleStatus === 'pending'"
            >等待對方回覆中</p>

            <div v-if="isMyComment && isChoose && articleStatus === 'pending'" class="selected-notice">
               <p class="--chosen">恭喜你被選中了！<br>快回覆對方，一起完成交換吧!</p>
               <!-- 留言元件 -->
               <button class="btnFill" @click="handleReplyExchange">
                  回覆邀請
               </button>
            </div>
         </div>
      </div>
      
   </li>         
</template>
<script setup>
   import { computed } from 'vue';
   import { exchangeList, replyExchange, completeExchange } from '@/data/mockExchangeData.js';
   import ContactDrawer from '@/components/ContactDrawer.vue';

   const props = defineProps({
      id: { type: [String, Number], required: true },
      image: { type: String, required: true },
      username: { type: String, required: true },
      postDate: { type: String, required: true },
      msgtxt: { type: String, required: true },
      isMyComment: { type: Boolean, default: false },
      isOwner: { type: Boolean, default: false },
      isChoose: { type: Boolean, default: false },
      articleStatus: { type: String, default: 'available' },
      postId: { type: [String, Number], required: true },   // 商品的 post_id
      posterName: { type: String, required: true },         // 發文者名字
      contact: { type: String, required: true }   // 新增：留言者自己的聯絡方式
   });

   const statusClass = computed(() => (props.isChoose ? 'li--selected' : ''));

   function handleReplyExchange() {
      replyExchange(exchangeList, {
         postId: props.postId,
         applyId: props.id,
         posterName: props.posterName
      })
   }

function handleCompleteExchange() {
   // props.username 就是這則留言的申請人，不用反查
   const isConfirm = window.confirm(
      `確定要完成與「${props.username}」的交換嗎？\n交換物品：「${props.msgtxt}」\n確定後無法復原。`
   );

   if (isConfirm) {
      completeExchange(exchangeList, { postId: props.postId });
      window.alert('交換已完成！');
   }
}

function handleCancelExchange() {
   const isConfirm = window.confirm(`確定要取消與「${props.username}」的交換嗎？\n取消後無法復原。`);

   if (isConfirm) {
      cancelExchange(exchangeList, { postId: props.postId });
      window.alert('已取消交換');
   }
}
</script>

<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;

   .applyer-contact{
      padding-top: 8px;
      display: flex;
      border-top:1px solid  map-get($color ,primary ) ;

      .drawer-item{
         flex: 1;
      }
   }

   .exchange-actions{
      display: flex;
      gap: 12px;
      height: fit-content;
   }

   p{
      color: #141C26;
   }

   .selected-notice{
      .btnFill{
         display: block;
         margin: 0 auto;
      }
   }

   .--chosen{
      color: #A86B00;
      font-weight: 600;
      text-align: center;
      line-height: 1.5;
      display: flex;
      flex-direction: column;
      padding-bottom: 8px;
   }


   li {
      padding: 12px;
      display: flex;
      border-bottom: 1px solid map-get($color, gray);
      gap: 12px;

      &.li--selected {
         background-color: #FFF2D6;   // 例如淺黃色，代表「已選中」
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
            gap: 12px;
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
            align-items: start;

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