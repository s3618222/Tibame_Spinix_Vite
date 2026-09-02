<template> 
   <li :class="[statusClass, {'remove-style': !isShow &&!isMyComment && !isAdmin}]">
      <div class="msg-wrapper">
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
                  <a :href="complaintUrl" v-if="!isMyComment && !isAdmin && isShow">
                     <i class="fa-solid fa-triangle-exclamation"></i>
                  </a>
               </div>
               <p class="msg-txt" >{{msgtxt}}</p>
               <div
                  v-if="!isAdmin"
                  class="applyer-contact"
                  :class="{'topLine':props.isChoose && props.articleStatus === 'exchanging'}"
                  >
                  <div class="drawer-item">
                     <ContactDrawer
                     :contact="props.contact"
                     :show="props.articleStatus === 'exchanging' && props.isChoose"
                  />
                  </div>
                  <div v-if="props.isOwner && props.isChoose && props.articleStatus === 'exchanging' && !isAdmin"
                     class="exchange-actions">
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
               v-if="isOwner && !isChoose && articleStatus === 'available' && !isAdmin && isShow "
               type="button"
               @click="$emit('select-applicant', { commentId: id, username: username })"
            >
               跟他交換
            </button>
            <p class="--chosen" v-if="isOwner && isChoose && !isAdmin && articleStatus === 'pending'">等待對方回覆中</p>

            <div v-if="isMyComment && isChoose && articleStatus === 'pending' && !isAdmin" class="selected-notice">
               <p class="--chosen">恭喜你被選中了！<br>快回覆對方，一起完成交換吧!</p>
               <div class="exchange-actions">
                  <button class="btnFill" @click="handleConfirmExchange">
                     確認交換
                  </button>
                  <button class="btnRemoveNoFill" @click="handleRejectExchange">
                     拒絕交換
                  </button>
               </div>
            </div>
            <p class="--chosen" v-if=" isChoose && !isAdmin && articleStatus === 'completed' && (isOwner ||isMyComment)">交換成功!</p>
         </div>
         <!-- 上下架按鈕(管理員畫面) -->
         <div class="btn-statusToggle">
            <StatusToggleButton
               v-if="articleStatus !== 'completed'"
               title="留言"
               :isShow="isShow"
               :isAdmin="isAdmin"
               @toggle="$emit('toggle-comment-status', { commentId: id })"
            />
         </div>
      </div>
      </div>
      <!-- 被申訴時警告訊息 -->
      <div v-if="!isShow && (isMyComment || isAdmin)">
         <WarningBanner
            title= "留言"
            :remove_reason = "remove_reason"
            :show_contact="isMyComment"
            class="warning-banner"
         />
      </div>
   </li>         
</template>
<script setup>
   import { computed } from 'vue';
   import ContactDrawer from '@/components/ContactDrawer.vue';
   import WarningBanner from '@/components/WarningBanner.vue';
   import StatusToggleButton from '@/components/StatusToggleButton.vue';

   const emit = defineEmits(['select-applicant', 'toggle-comment-status' , 'reply-confirmed','exchange-completed']);
   const phpBaseUrl =
   location.hostname === "localhost" ||
      location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : `${location.origin}/ckd101/g2/php`;

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
      contact: { type: String, required: true },   // 新增：留言者自己的聯絡方式
      isShow:{ type: Boolean , default: true },
      remove_reason:{ type: String, default:''},
      isAdmin:{ type:Boolean , default:false}
   });

   const statusClass = computed(() => (props.isChoose ? 'li--selected' : ''));

   async function handleReplyExchange() {


      const success = await replyExchange({
         applyId: props.id,
         posterName: props.posterName
      });

      if(success){
         emit('reply-confirmed');
      }
   }

   async function handleConfirmExchange() {
   const isConfirm = window.confirm(
      `是否確認與「${props.posterName}」交換?按下確認後將可以查看雙方的聯絡資訊`
   );
   if (!isConfirm) return;

   const payload = { comm_id: props.id };

   try {
      const res = await fetch(`${phpBaseUrl}/exchange/replyToConfirm.php`, {
         method: "PATCH",
         credentials: "include",
         headers: { 'Content-Type': 'application/json' },
         body: JSON.stringify(payload)
      });

      const result = await res.json();

      if (result.success) {
         alert('確認成功!現在可以查看對方的聯絡資訊了');
         emit('reply-confirmed');
      } else {
         alert(result.message || '確認失敗');
      }
   } catch (err) {
      alert('系統發生錯誤，請稍後再試');
   }
}

async function handleRejectExchange() {
   const isConfirm = window.confirm(`確定要拒絕與「${props.posterName}」的交換邀請嗎?`);
   if (!isConfirm) return;

   const payload = { postId: props.postId, action: 'cancel' };

   try {
      const res = await fetch(`${phpBaseUrl}/exchange/CompleteExchange.php`, {
         method: "PATCH",
         credentials: "include",
         headers: { 'Content-Type': 'application/json' },
         body: JSON.stringify(payload)
      });

      const result = await res.json();

      if (result.success) {
         alert('已拒絕交換邀請');
         emit('reply-confirmed'); // 沿用同一支重新抓資料的事件
      } else {
         alert(result.message || '操作失敗，請稍後再試');
      }
   } catch (err) {
      alert('系統發生錯誤，請稍後再試');
   }
}

   async function handleCompleteExchange() {
   // props.username 就是這則留言的申請人，不用反查
      const isConfirm = window.confirm(
         `確定要完成與「${props.username}」的交換嗎？\n交換物品：「${props.msgtxt}」\n確定後無法復原。`
      );
      if(!isConfirm) return;

      const payload = {
         postId: props.postId,
         action: 'complete'
      };

      try{
         const res = await fetch(`${phpBaseUrl}/exchange/CompleteExchange.php`, {
            method: "PATCH",
            credentials: "include",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
         });

         const result = await res.json();

         if(result.success){
            alert('交換已完成');
            emit('exchange-completed'); 
            return true;
         }
      }catch(err){
         alert('系統發生錯誤，請稍後再試');
         return false;
      }
   }

   async function handleCancelExchange() {
      const isConfirm = window.confirm(`確定要取消與「${props.username}」的交換嗎？\n取消後無法復原。`);
      if(!isConfirm) return;

      const payload = {
         postId: props.postId,
         action: 'cancel'
      };

      try{
         const res = await fetch(`${phpBaseUrl}/exchange/CompleteExchange.php`, {
            method: "PATCH",
            credentials: "include",
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
         });

         const result = await res.json();

         if(result.success){
            alert('已取消交換');
            emit('exchange-completed'); 
            return true;
         }
      }catch(err){
         alert('系統發生錯誤，請稍後再試');
         return false;
      }
   }

   const complaintUrl = computed(()=>{
      return `complaint.html?type=exchange&comm_id=${props.id}`;
   });

</script>

<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;

   

   .btn-statusToggle{
      padding-top: 12px;
      
   }

   .warning-banner{
      margin-top: 12px;
   }

   .applyer-contact{
      display: flex;
      gap: 12px;

      .drawer-item{
         flex: 1;
         // padding-bottom: 12px;
      }
   }

   .topLine{
      border-top:1px solid  map-get($color ,primary );
   }
   
   .exchange-actions{
      display: flex;
      gap: 12px;
      height: fit-content;
      margin-top: 12px;
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

   .msg-wrapper{
      display: flex;
      gap: 12px;
      align-items: center;
   }


   li {
      padding: 12px 16px;
      border-bottom: 1px solid map-get($color, gray);

      &.remove-style{
         position: relative;
         &::after{
            content: '';
            inset: 0;
            background-color:rgba(170, 170, 170,0.45);
            pointer-events: none;   // 避免遮罩擋住點擊事件
            z-index: 1;
            position: absolute;
         }
      }

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
         align-items: center;
         gap: 12px;
         justify-content: space-between;


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
         
         .msg-content{
            flex-direction: row;
            // align-items: center;

            .user-info{
               flex: 1;
               // align-items: start;

               .user-info-txt{
                  display: flex;
                  gap: 8px;
                  // align-items: center;

                  .msg-date{
                     padding: 0;
                  }
               }
            }
         }
      }
      
   }
</style>