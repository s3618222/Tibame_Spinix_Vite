<template>
   <div class="msg-list">
      <ul>
         <MessageItem
            v-for="msg in sortedMessages"
            :key="msg.id"
            :id="msg.id"
            :image="msg.image"
            :username="msg.username"
            :postDate="msg.postDate"
            :msgtxt="msg.msgtxt"
         />
      </ul>
      <p v-if="sortedMessages.length === 0" class="empty-state">目前沒有留言</p>
   </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import MessageItem from './prodMsgInfo.vue'

// 假資料,之後串接後端時,把這個陣列換成 fetch API 拿到的結果即可
const messages = ref([
   {
      id: 1,
      image: 'spinix_member_test2.jpg',
      username: '已讀不回的飯糰',
      postDate: '2026-08-06',
      msgtxt: '想用CX 系列改裝陀螺跟版主做交換！'
   },
   {
      id: 2,
      image: 'spinix_member_test3.png',
      username: '虛空將',
      postDate: '2026-08-16',
      msgtxt: '想用CX 系列改裝陀螺跟版主做交換！'
   },
   {
      id: 3,
      image: 'spinix_member_test1.png',
      username: '小桃子脆片',
      postDate: '2026-08-26',
      msgtxt: '想用CX 系列改裝陀螺跟版主做交換！'
   },
   {
      id: 4,
      image: 'spinix_member_test2.jpg',
      username: '軟糖大王',
      postDate: '2026-07-16',
      msgtxt: '想用CX 系列改裝陀螺跟版主做交換！'
   },
   {
      id: 5,
      image: 'spinix_member_test3.png',
      username: '虛假餘燼',
      postDate: '2026-07-30',
      msgtxt: '想用CX 系列改裝陀螺跟版主做交換！'
   },
   {
      id: 6,
      image: 'spinix_member_test1.png',
      username: '寂寞王座',
      postDate: '2026-08-02',
      msgtxt: '想用CX 系列改裝陀螺跟版主做交換！'
   }

]);

const sortOrder = ref('newest')

function handleSortChange(e) {
   sortOrder.value = e.detail.sortOrder
}

onMounted(() => {
   
   window.addEventListener('msg-sort-change', handleSortChange)
})
onUnmounted(() => {
   window.removeEventListener('msg-sort-change', handleSortChange)
})

const sortedMessages = computed(() => {
   return [...messages.value].sort((a, b) => {
      const dateA = new Date(a.postDate)
      const dateB = new Date(b.postDate)
      return sortOrder.value === 'newest' ? dateB - dateA : dateA - dateB
   })
})
</script>

<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;


   .container {
      .message-board {
         .msg-list {
            ul {
               margin-inline: -12px;
            }
         }
      }
   }

</style>