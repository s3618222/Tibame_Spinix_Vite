<template>
   <button
      type="button"
      class="btnRemove"
      v-if="isShow && isAdmin"
      @click="emit('toggle')"
   >
      下架{{title}}
   </button>
   <button
      type="button"
      class="btnFill"
      v-if="!isShow && isAdmin"
      @click="emit('toggle')"
   >
      恢復上架
   </button>
</template>
<script setup>
   const props = defineProps({
      title:{
         type:String,
         required:true
      },
      isShow:{ // 文章或留言是否為上架中
         type:Boolean,
         default:true
      },
      isAdmin:{
         type:Boolean,
         default:true
      }
   });

   const emit = defineEmits(['toggle']);
</script>
<style lang="scss" scoped>
   @use '@/assets/scss/_var' as *;
   
</style>