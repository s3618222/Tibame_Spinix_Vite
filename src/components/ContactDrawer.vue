<!-- components/ContactDrawer.vue -->
<template>
<div class="user-contact" v-if="show">
   <button 
      type="button"
      class="btn-showContact"
      @click="isOpen = !isOpen"
   >
      查看聯絡資訊
      <span class="arrow" :class="{ open: isOpen }">
         <i class="fa-solid fa-chevron-down"></i>
      </span>
   </button>
   <div class="drawer" :data-open="isOpen">
      <p class="drawer-content">{{ contact }}</p>
   </div>
</div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
   contact: { type: String, required: true },
   show: { type: Boolean, default: true }
})

const isOpen = ref(false)
</script>

<style lang="scss" scoped>

   p,
   button{
      color: #141C26;
   }
   


   .btn-showContact{
      padding-top: 8px;
      width: 100%;
      text-align: left;
   }

   .drawer {
      display: grid;
      grid-template-rows: 0fr;
      transition: grid-template-rows 0.35s ease;
   }
   .drawer[data-open="true"] {
      grid-template-rows: 1fr;
   }

   .drawer-content {
      overflow: hidden;
      line-height: 1.6;
      font-size: 14px;
      // padding-bottom: 8px;
   }

   .arrow {
      transition: transform 0.3s ease;
      display: inline-block;
   }

   .arrow.open {
      transform: rotate(-180deg);
   }
</style>