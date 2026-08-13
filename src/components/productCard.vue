<template>
   <div class="card" @click="goToDetail">
      <div class="pic">
         <img :src="image" :alt="title">
      </div>
      <div class="card-info">
         <h4>{{ title }}</h4>

         <div class="wrapper">
            <img :src="avatar" alt="">
            <p>{{ username }}</p>
         </div>

         <div class="wrapper">
            <i class="fa-solid fa-calendar-days icon-color"></i>
            <p class="date">
               刊登日期:
            </p>
            <span>{{ postDate }}</span>
         </div>
         
         <p>
            <i class="fa-solid fa-location-dot icon-color"></i>   
            {{ city }}<span>{{ district }}</span>
         </p>
      </div>

      <div class="card-footer">
         <p class="chip" :class="chipModifier">{{ state }}</p>
         <a :href="'product_detail.html'" class="btnNoFill" @click.stop>查看詳情</a>
      </div>
      <div class="show-detail">
         <a :href="'product_detail.html'" class="detail_link" @click.stop>查看詳情</a>
      </div>
   </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
   id: {
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
   postDate: {
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
   }
});

// 狀態 → chip modifier 對照
const stateChipMap = {
   '可交換': 'chip--exchangeable',
   '交換中': 'chip--category',
   '待確認': 'chip--state',
   '交換完成': 'chip--completed'
};

const chipModifier = computed(() => stateChipMap[props.state] || '');

function goToDetail() {
   window.location.href = `product_detail.html`;
}
</script>


<style lang="scss">
   @use '@/assets/scss/_var' as *;

   .icon-color{
      color: map-get($color , neutral);
   }

   .show-detail{
      padding-top: 12px;
      .detail_link{
         display: block;
         padding: 8px 12px;
         // background-color: orange;
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

         img {
            width: 36px;
         }

         p {
            height: fit-content;
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

         .date {
            display: none;
         }
      }

      .card-footer {
         display: flex;
         padding-top: 12px;
         font-size: map-get($fontSize , hint);
         align-items: center;
         justify-content: space-between;

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

               .date {
                  display: block;
               }
            }
         }

         .card-footer {
            align-items: center;

            .btnNoFill {
               display: block;
               padding: 12px 16px;
            }
         }
      }
   }
</style>