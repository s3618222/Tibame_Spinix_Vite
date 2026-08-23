<template>
  <el-pagination
    class="pagination"
    :current-page="currentPage"
    :page-size="pageSize"
    :total="total"
    :pager-count="5"
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
    gap: 12px;

    :deep(.btn-prev), //上一頁按鈕
    :deep(.btn-next), // 下一頁
    :deep(.el-pager li) { //中間頁碼
      min-width: 32px;
      aspect-ratio: 1/1;
      margin: 0;
      cursor: pointer;

      border-radius: 6px;
      border: 1px solid #dddddd;
      background-color: transparent;

      font-size: 16px;
      font-weight: 500;

      transition: all .3s;
      
    }
    :deep(.el-pager){
      display: flex;
      gap: 12px;
      color: #aaaaaa;
      
      li{
        text-align: center;
        line-height: 32px;
      }
    }
    :deep(.el-pager li.is-active) {
      background-color: #fec96b;
      color: #141c26;
    }

    :deep(.btn-prev:hover),
    :deep(.btn-next:hover),
    :deep(.el-pager li:hover) {
      background-color: #fec96b;
    }

    :deep(.el-pager li.is-active:hover) {
      color: #141c26;
    }
  }

  @media screen and (max-width: 576px) {
    .pagination {
      gap: 6px;

      :deep(.el-pager) {
        gap: 8px;
      }

      :deep(.btn-prev),
      :deep(.btn-next),
      :deep(.el-pager li) {
        min-width: 28px;
        font-size: 14px;
      }
    }
  }

</style>