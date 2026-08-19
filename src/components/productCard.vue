<template>
   <div class="card" @click="goToDetail">
      <div class="pic">
         <img :src="image" :alt="title">
      </div>
      <div class="card-info">
         <h4>{{ title }}</h4>

         <div class="wrapper">
            <div class="img-user">
               <img :src="avatar" alt="">
            </div>
            <p class="username">{{ username }}</p>
         </div>

         <div class="wrapper">
            <i class="fa-solid fa-calendar-days icon-style"></i>
            <p>
               刊登日期：
               <span class="date">{{ create_time }}</span>
            </p>
         </div>

         <div class="wrapper">
            <i class="fa-solid fa-location-dot icon-style"></i>
            <p>{{ city }}<span>{{ district }}</span></p>
         </div>
      </div>

      <div class="card-footer">
         <p class="chip" :class="chipModifier">{{ state }}</p>

         <div class="card-buttons" @click.stop>
            <button
               v-for="btn in buttons"
               :key="btn.action"
               type="button"
               class="btnNoFill"
               :class="getBtnClass(btn)"
               @click="handleBtnClick(btn)"
            >{{ btn.label }}</button>
         </div>
      </div>

      <div class="show-detail">
         <a :href="`product_detail.html?id=${post_id}`" class="detail_link" @click.stop>查看詳情</a>
      </div>
   </div>
</template>
<script setup>
import { computed } from 'vue';

const props = defineProps({
   post_id: {
      type: [String, Number],
      required: true
   },
   title: {
      type: String,
      required: true
   },
   image: {
      type: String,
      required: true
   },
   avatar: {
      type: String,
      default: '/spinix_member_default.png'
   },
   username: {
      type: String,
      required: true
   },
   create_time: {
      type: String,
      required: true
   },
   city: {
      type: String,
      required: true
   },
   district: {
      type: String,
      required: true
   },
   state: {
      type: String,
      default: '可交換'
   },
   // 這張卡片目前是在哪個情境下顯示：
   // 'browse'（一般瀏覽，預設）／'myPosts'（我刊登的交換）／'myApplications'（我提出的申請）
   context: {
      type: String,
      default: 'browse'
   }
});

const emit = defineEmits(['complete-exchange', 'reply-exchange']);

// 狀態文字 → chip modifier 對照，涵蓋文章狀態 & 申請狀態
const stateChipMap = {
   '可交換': 'chip--exchangeable',
   '交換中': 'chip--category',
   '待確認': 'chip--state',
   '交換完成': 'chip--completed',
   '等待回復': 'chip--state',
   '已回復': 'chip--exchangeable'
};

const chipModifier = computed(() => stateChipMap[props.state] || '');

const buttons = computed(() => {
   const viewMore = { label: '查看詳情', action: 'view-more', type: 'default' };

   if (props.context === 'myApplications' && props.state === '已回復') {
      return [{ label: '回覆交換', action: 'reply-exchange', type: 'primary' }];
   }

   if (props.context === 'myPosts' && props.state === '交換中') {
      return [{ label: '完成交換', action: 'complete-exchange', type: 'primary' }];
   }

   return [viewMore];
});
function getBtnClass(btn) {
   return [`btn-${btn.type}`, { 'btn-disabled': btn.disabled }];
}

function handleBtnClick(btn) {
   if (btn.disabled) return;

   if (btn.action === 'view-more') {
      goToDetail();
      return;
   }

   emit(btn.action, { post_id: props.post_id, title: props.title });
}

function goToDetail() {
   const params = new URLSearchParams({
      id: props.post_id,
      from: props.context
   });
   window.location.href = `product_detail.html?${params.toString()}`;
}
</script>


<style lang="scss">
   @use '@/assets/scss/_var' as *;

   .icon-style{
      display: none;
   }

   .show-detail{
      padding-top: 12px;
      .detail_link{
         display: block;
         padding: 8px 12px;
         margin: 0 -12px -12px;
         color: map-get($color, neutral );
         text-align: center;
         border-top: 1px solid #dddddd;
      }
   }

   .card {
      width: 100%;
      background-color: white;
      padding: 12px;
      border: 1px solid map-get($color , tertiary);
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(20, 28, 38, 0.06);
      height: fit-content;
      overflow: hidden;

      .wrapper {
         display: flex;
         align-items: center;
         gap: 8px;
      
         .img-user{
            width: 36px;
         }

         .username{
            font-weight: 600;
         }

         p {
            height: fit-content;
         }

         .date{
            margin-left: -4px;
         }
      }

      .pic {
         border-radius: 10px;
         overflow: hidden;

         img {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
         }
      }

      .card-info {
         padding-top: 16px;
         display: flex;
         flex-direction: column;
         gap: 12px;

         h4 {
            font-weight: 600;
            font-size: map-get($fontSize , default);
         }

         p {
            font-size: map-get($fontSize , hini);
            line-height: 18px;
         }

         span {
            margin-left: 4px;
         }
      }

      .card-footer {
         display: flex;
         padding-top: 12px;
         font-size: map-get($fontSize , hint);
         align-items: center;
         justify-content: space-between;

         .card-buttons {
            display: none;
            gap: 8px;
         }

         .btnNoFill {
            display: none;
         }
      }
   }
   


// == 平板 ========================================
   @media screen and (width >=768px) {
      
   }

// == 桌機 =====================================
   @media screen  and (width >= 992px ){
      

      .icon-style {
         display: flex;
         color: map-get($color , neutral);
      }

      .show-detail{
         display: none;
         visibility: hidden;
      }
      .card {
         cursor: pointer;
         transition: transform .5s ease, border .25s ease;

         &:hover {
            border: 1px solid map-get($color , primary );
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(20, 28, 38, 0.1);
         }

         .card-info {
            font-size: 16px;
            gap: 16px;

            .wrapper {
               display: flex;               
            }
         }

         .card-footer {
            align-items: center;

            .card-buttons {
               display: flex;
            }

            .btnNoFill {
               display: block;
               padding: 12px 16px;
            }
         }
      }
   }
</style>