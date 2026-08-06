<template>
  <el-pagination
    class="pagination"
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

  .pagination {
    display: flex;
    align-items: center;
    gap: 4px;

    :deep(.btn-prev), //上一頁按鈕
    :deep(.btn-next), // 下一頁
    :deep(.el-pager li) { //中間頁碼
      min-width: 32px;
      height: 32px;
      margin: 0;

      border-radius: 6px;
      background-color: transparent;

      color: #141c26;
      font-size: 16px;
      font-weight: 500;
    }

    :deep(.el-pager li.is-active) {
      background-color: #fec96b;
      color: #141c26;
    }

    :deep(.btn-prev:hover),
    :deep(.btn-next:hover),
    :deep(.el-pager li:hover) {
      color: #f29b00;
    }

    :deep(.el-pager li.is-active:hover) {
      color: #141c26;
    }
  }

</style>