<template>
   <div class="card" @click="goToDetail">
      <div class="pic">
         <img :src="image" :alt="title">
         <!-- 格式:https://tibamef2e.com/ckd101/g2/php/uploads/articles/exchange_ab8f43ded063f855.png -->
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
            <p class="chip chip--state">{{ condition }}</p>
            <p class="chip chip--category">{{ type }}</p>
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
      <p class="chip tag-pos" :class="chipModifier">{{ state }}</p>
      <div class="card-footer">
         <div class="card-buttons" @click.stop>
            <button
               v-for="btn in buttons"
               :key="btn.action"
               type="button"
               
               :class="getBtnClass(btn)"
               @click="handleBtnClick(btn)"
            >{{ btn.label }}</button>
         </div>
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
   type: {
      type: String,
      required:true
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
   condition:{
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

const emit = defineEmits(['reply-exchange']);

// 狀態文字 → chip modifier 對照，涵蓋文章狀態 & 申請狀態
const stateChipMap = {
   '可交換': 'chip--exchangeable',
   '交換中': 'chip--category',
   '待確認': 'chip--state',
   '交換完成': 'chip--completed',
   '申請中': 'chip--exchangeable',
   '已回覆': 'chip--state'
};


const chipModifier = computed(() => stateChipMap[props.state] || '');

const buttons = computed(() => {
   const viewMore = { label: '查看詳情', action: 'view-more', type: 'btnNoFill' };

   if (props.context === 'myApplications' && props.state === '已回覆') {
      return [{ label: '回覆交換', action: 'reply-exchange', type: 'btnFill' }];
   }

   return [viewMore];
});
function getBtnClass(btn) {
   return [`${btn.type}`, { 'btn-disabled': btn.disabled }];
}

function handleBtnClick(btn) {
   if (btn.disabled) return;

   if (btn.action === 'view-more') {
      goToDetail();
      return;
   }

   emit(btn.action, { id: props.post_id, title: props.title,username:props.username });
}

function goToDetail() {
   const params = new URLSearchParams({
      id: props.post_id,
      from: props.context
   });
   window.location.href = `product_detail.html?${params.toString()}`;
}
</script>


<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;

   .page-title {
         color: #F29B00;
         font-size: 26px;
         font-weight: 900;
      }

   .icon-style{
      display: none;
   }

   .card {
      width: 100%;
      background-color: white;
      border: 1px solid #dddddd;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(20, 28, 38, 0.06);
      height: fit-content;
      overflow: hidden;
      color: #141C26;
      position: relative;

      .tag-pos {
         position: absolute;
         top: 8px;
         right: 8px;
      }

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
         overflow: hidden;
         border: 1px solid #dddddd;

         img {
            display: block;
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
         }
      }

      .card-info {
         padding: 16px 12px;
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

      
   }
   
   @media screen and ( 768px > width) {
      .card-footer {
         padding-top: 12px;
         font-size: map-get($fontSize , hint);

         .chip{
            width: fit-content;
         }

         .card-buttons {
            display: flex;
            justify-content: center;
            margin: 12px -12px -12px;
            border-top: 1px solid #dddddd;

            .btnNoFill,
            .btnFill{
               border: none;
               background-color: transparent;
            }

            .btnFill{
               color: map-get($color, secondary2 );
            }
         }

      }
   }


// == 平板 ========================================
   @media screen and (width >=768px) {
      .card-footer {
         display: flex;
         align-items: center;
         justify-content: space-between;
         padding: 0 12px 12px;

            .card-buttons {
               display: flex;
               width: 100%;
            }

            .btnNoFill {
               width: 100%;
               &:hover{
                  border-color: map-get( $color, neutral);
                  background-color:  map-get( $color, neutral);;
                  color: white;
               }
            }
         }
   }

// == 桌機 =====================================
   @media screen  and (width >= 992px ){
      

      .icon-style {
         display: flex;
         color: map-get($color , neutral);
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

      }
   }
</style>