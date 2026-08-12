<template>
   <div class="user-notice">
      <div class="panel-title">
         <button type="button"  @click="$emit('close')">
            <i class="fa-solid fa-angle-left"></i>
            <p>會員通知</p>
         </button>
      </div>
      <div class="tabs">

         <button 
            type="button" 
            :class="{ '-active': activeTab === 'all' }"
            @click="activeTab = 'all'"
         >
            全部
         </button>
         <button 
            type="button"
            :class="{ '-active': activeTab === 'unread' }"
            @click="activeTab = 'unread'"
         >
            未讀
         </button>
      </div>

      <!-- 全部已讀 -->
         <div class="mark-all-read">
            <button type="button" @click="markAllAsRead" :disabled="!hasUnread">
               全部已讀
            </button>
         </div>
      <div class="notice-content">
         <ul  v-if="filteredList.length">
            <!-- <li class="notice-info isUnread">
               <a href="#">
                  <span class="notice-txt">會員A申請約戰配對</span>
                  <span class="notice-date">2026-08-12 22:47</span>   
               </a>
            </li>
            <li class="notice-info isUnread">
               <a href="#">
                  <span class="notice-txt">會員A申請約戰配對</span>
                  <span class="notice-date">2026-08-12 22:47</span>   
               </a>
            </li> -->
            <li 
               v-for="item in filteredList" 
               :key="item.id"
               class="notice-info"
               :class="{ isUnread: item.isUnread }"
            >
               <a href="#" @click.prevent="handleClick(item)">
                  <span class="notice-txt">{{ item.text }}</span>
                  <span class="notice-date">{{ item.date }}</span>
               </a>
            </li>
         </ul>
         <!-- 沒資料時的提示 -->
         <p v-else class="empty-msg">目前沒有通知</p>
      </div>
   </div>
</template>

<script>
   export default {
      name: "NoticePanel",

      data() {
         return {
         activeTab: "all", // all | unread

         // 通知類別對照表：之後新增類別只要在這裡加一筆
         typeMap: {
            battle: { label: "對戰配對", path: "/battle" },
            forum: { label: "論壇", path: "/forum" },
            market: { label: "交換專區", path: "/market" },
            system: { label: "系統通知", path: "/member" }
         },

         // 假資料
         noticeList: [
         {
            id: 1,
            type: "battle",
            text: "會員A申請約戰配對",
            date: "2026-08-12 22:47",
            isUnread: true,
            targetId: 101 // 對應的資料 id，例如要連到哪一場對戰
         },
         {
            id: 2,
            type: "forum",
            text: "會員B回覆了你的貼文",
            date: "2026-08-12 20:15",
            isUnread: true,
            targetId: 202
         },
         {
            id: 3,
            type: "market",
            text: "會員C想跟你交換陀螺",
            date: "2026-08-11 18:30",
            isUnread: false,
            targetId: 303
         },
         {
            id: 4,
            type: "system",
            text: "你的帳號已完成信箱驗證",
            date: "2026-08-10 09:00",
            isUnread: false,
            targetId: null
         }
         ]
      };
   },

   computed: {
      // 依照目前選中的 tab 過濾資料
      filteredList() {
         if (this.activeTab === "unread") {
         return this.noticeList.filter(item => item.isUnread);
         }
         return this.noticeList;
      },
      hasUnread() {
         return this.noticeList.some(item => item.isUnread);
      }
   },

   methods: {
      // 點擊通知：標記已讀 + 導頁到對應類別頁面
      handleClick(item) {
         // 1. 標記已讀
         item.isUnread = false;

         // 2. 根據類別連到對應頁面
         const target = this.typeMap[item.type];
         if (!target) {
         console.warn(`找不到對應的類別設定: ${item.type}`);
         return;
         }

         // 如果有帶 targetId（例如特定對戰場次），可以組成 query 或路徑參數
         if (item.targetId) {
         this.$router.push({ path: target.path, query: { id: item.targetId } });
         } else {
         this.$router.push(target.path);
         }
      },
      markAllAsRead() {
         this.noticeList.forEach(item => {
            item.isUnread = false;
         });
      }
      
   }
};
</script>

<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;

   .mark-all-read {
      display: flex;
      justify-content: flex-end;  
      padding-block: 8px;
      flex-shrink: 0;   

   button {
      font-size: 14px;
      color: map-get($color, neutral);
      border-bottom:1px solid  map-get($color,tertiary );
      transition: all 0.3s ease;

      &:disabled {
         opacity: 0.4;
         cursor: not-allowed;
      }

      &:hover:not(:disabled) {
         border-bottom:1px solid  map-get($color, neutral);
      }
   }
}

   .user-notice{
      width: 100%;
      display: flex;          
      flex-direction: column;
      height: 100%;            
      min-height: 0;
      padding: 20px 12px;       
      
      .panel-title{
         flex-shrink: 0; 
         >button{
            display: flex;
            // padding-block: 4px;
            font-size: 18px;
            font-weight: 600;
            align-items: center;
            color:map-get($color, secondary) ;
         }
      }

         .tabs{
            display: flex;
            margin: 8px -12px 0px;

         button{
            flex: 1;
            padding: 4px;
            color: map-get($color,neutral );
            border-bottom:2px solid #ddd;
            transition: all .3s ease;

            &.-active{
               border-bottom: 2px solid  map-get($color, secondary2 );
               color:map-get($color, secondary2 );
               background-color: #FFF2D6;
            }
         }
         
      }

      .notice-content{
            flex: 1; 
            min-height: 0;   
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 8px;
            
         ul{
            .notice-info{
               position: relative;
               a{
                  display: flex;
                  flex-direction: column;
                  gap: 12px;   
                  padding-block: 16px;
                  // margin-inline: -12px;
               }

               &.isUnread::after{
                  content: '';
                  position: absolute;
                  width: 14px;
                  aspect-ratio: 1/1;
                  border-radius: 50%;
                  background-color: map-get($color, secondary2);
                  right: 5%;
                  top: 50%;
                  transform: translateY(-50%);
               }

            
            }
         }

      }
   }

   @media screen and (width >=992px) {
      .user-notice{    

         .panel-title{
            >button{
               width: 100%;
               transition: all .3s;
               &:hover{
                  color: map-get($color , secondary2 );
               }
                  
            }
         }


      .notice-content{
         margin-inline:-12px ;
            ul{
               margin-inline:-12px ;
               .notice-info{
                  transition: all .3s ease;
                  // margin-inline:12px ;
                  padding-inline: 24px;
                  &.isUnread::after{
                     right: 15%;
                  }

                  &:hover{
                     background-color: map-get($color , pending );
                  }

                  .notice-txt{
                     font-size: 18px;
                  }

                  .notice-date{
                     font-size: 16px;
                     color:map-get($color, neutral );
                  }
               }
            }
            
         

      }
   }
   }
</style>