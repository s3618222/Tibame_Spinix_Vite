<template>
  <el-pagination
    :current-page="currentPage"
    :page-size="pageSize"
    :total="total"
    layout="prev, pager, next"
    @current-change="changePage"
  />
</template>

<script>
  export default {
    name: "Pagination",

    props: {
      currentPage: { //現在第幾頁
        type: Number,
        required: true
      },

      pageSize: { //每頁顯示幾筆
        type: Number,
        required: true
      },

      total: { //總筆數
        type: Number,
        required: true
      }
    },

    emits: ["update:currentPage"],

    methods: {
      changePage(page) {
        this.$emit("update:currentPage", page);
      }
    }
  };
</script>

<style lang="scss" scoped>
</style>