<template>
  <section id="page-myexchange">
    <div class="page-myexchange-title">
      <h2>我的交換</h2>
      <p>管理你的交換紀錄</p>
    </div>

    <div class="exchange-category">
      <button 
      type="button" 
      class="myexchange tabs-style"
      :class="{'active':activeTab === 'myexchange'}"
      @click="activeTab = 'myexchange'"
      >
        我刊登的交換
    </button>
      <button type="button" class="myapply tabs-style"
      :class="{'active':activeTab === 'myapply'}"
      @click="activeTab = 'myapply'"
      >
        我提出的申請
      </button>
    </div>

    <StatusTabs 
      :tabs="statusTabs" 
      v-model="currentStatus" 
    />

    <!-- 交換流程說明 -->
    <div class="sop">
      <p class="sop-title">交換流程說明</p>
      <div class="sop-list">
        <ul>
          <li>
            <div class="list-item" v-if="activeTab === 'myexchange'">
              <div class="sop-img">
                <img src="/sop01.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.1 可交換</p>
                <p class="info">選擇想交換對象</p>
              </div>
            </div>
            <div class="list-item" v-if="activeTab === 'myapply'">
              <div class="sop-img">
                <img src="/sop-apply.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.1 申請中</p>
                <p class="info">等待對方選擇交換</p>
              </div>
            </div>
          </li>
          <div class="icon-sop-next">
              <i class="fa-solid fa-right-long"></i>
            </div>
          <li>
            <div class="list-item"  v-if="activeTab === 'myexchange'">
              <div class="sop-img">
                <img src="/sop02.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.2 待確認</p>
                <p class="info">等待對方確認交換</p>
              </div>
            </div>
            <div class="list-item" v-if="activeTab === 'myapply'">
              <div class="sop-img">
                <img src="/sop-checked.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.2 已回覆</p>
                <p class="info">請確認是否交換</p>
              </div>
            </div>
          </li>
          <div class="icon-sop-next">
            <i class="fa-solid fa-right-long"></i>
          </div>
          <li>
            <div class="list-item">
              <div class="sop-img">
                <img src="/sop03.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.3 交換中</p>
                <p class="info">討論交換細節</p>
              </div>
            </div>
          </li>
          <div class="icon-sop-next">
              <i class="fa-solid fa-right-long"></i>
            </div>
          <li>
            <div class="list-item">
              <div class="sop-img">
                <img src="/sop04.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.4 交換完成</p>
                <p class="info">已順利交換完成</p>
              </div>
            </div>
          </li>
        </ul>

      </div>
    </div>

    <div class="container" v-if="paginatedList.length">
        <ProductCard
          v-for="item in paginatedList"
          :key="item.cardKey"
          :post_id="item.post_id"
          :title="item.title"
          :image="item.product_img"
          :avatar="item.headshot"
          :username="item.name"
          :create_time="item.create_time"
          :city="item.city"
          :district="item.district"
          :context="context"
          :state="item.stateLabel"
          :type="item.typeLabel"
          :condition="item.conditionLabel"
          @reply-exchange="handleReplyExchange"
      />
    </div>

    <p v-else class="empty-msg">目前沒有符合的紀錄</p>

    
      <!-- 分頁器 -->
      <Pagination
        v-model:currentPage="currentPage"
        :pageSize="pageSize"
        :total="filteredList.length"
        class="pagination"
      />
  
  
  </section>
</template>

<script>
import StatusTabs from "./controlTabs.vue";
import ProductCard from "./productCard.vue";
// import {
//   exchangeList,
//   fakeComments,
//   statusLabelMap,
//   applyStatusLabelMap,
//   replyExchange
// } from "@/data/ExchangeData.js";

import { exchangeList, getComments ,statusLabelMap, applyStatusLabelMap, replyExchange ,typeLabelMap ,conditionLabelMap } from '@/data/ExchangeData.js';
// 分頁器
import Pagination from "@/components/pagination.vue";

export default {
  name: "MyExchange",

  components: {
    StatusTabs,
    ProductCard,
    Pagination
  },

  data() {
    return {
      currentStatus: "all",
      currentUserId: null,
      exchangeListData : [],
      fakeComments : [],
      statusLabelMap,
      applyStatusLabelMap,
      activeTab: 'myexchange',
      currentPage: 1,
      pageSize: 9,
    };
  },

  watch: {
    currentStatus() {
      this.currentPage = 1;
    },
    activeTab() {
      this.currentPage = 1;
      this.currentStatus = "all"; // 切換分頁時，篩選狀態重置，避免殘留篩選條件
    }
  },

  computed: {
    // 卡片按鈕要走哪套邏輯：myPosts（我刊登）／myApplications（我提出的申請）
    context() {
      return this.activeTab === 'myexchange' ? 'myPosts' : 'myApplications';
    },

    // 我刊登的交換：直接從 exchangeList 篩自己刊登的文章
    myExchangeList() {
      if (!Array.isArray(this.exchangeListData)) return [];   // 防呆
      return this.exchangeListData.filter(item => item.mem_id === this.currentUserId).map(item => ({
        ...item,
        cardKey: `post-${item.post_id}`,
        headshot: item.mem_photo,
        name: item.mem_name,
        product_img: item.post_pic1,
        stateLabel: this.statusLabelMap[item.status] || '',
        typeLabel: typeLabelMap[item.type],
        conditionLabel: conditionLabelMap[item.condition]
      }));
    },

    // 我提出的申請：從 fakeComments 篩「我留過言的」，再組合對應文章資訊
    myApplyList() {
      if (!Array.isArray(this.fakeComments)) return [];   // 防呆
      return this.fakeComments.filter(comment => comment.mem_id === this.currentUserId).map(comment => {
        const article = this.exchangeListData.find(item => item.post_id === comment.post_id);
        if (!article) return;

        const isChosen = comment.is_choose === true;
        const stateLabel = (article.status === 'available' || !isChosen) ? this.applyStatusLabelMap['available'] : this.applyStatusLabelMap[article.status];

        return {
          ...article,
          cardKey: `apply-${comment.comm_id}`,
          applyId: comment.comm_id,
          status: article.status,
          isChosen,
          stateLabel
        };
      }).filter(Boolean);
    },

    // 依目前分頁，決定要用哪份清單
    currentList() {
      return this.activeTab === 'myexchange' ? this.myExchangeList : this.myApplyList;
    },

    filteredList() {
      if (this.currentStatus === "all") {
        return this.currentList;
      }

      if (this.activeTab === 'myexchange') {
        return this.currentList.filter(item => item.status === this.currentStatus);
      }

      // 我提出的申請：用 stateLabel 比對
      return this.currentList.filter(item => item.stateLabel === this.currentStatus);
    },

    // 狀態篩選 tab：兩個分頁使用不同的狀態選項
  statusTabs() {
    const list = this.currentList;

    if (this.activeTab === 'myexchange') {
      return [
        { label: "全部", value: "all", count: list.length },
        { label: "可交換", value: "available", count: list.filter(i => i.status === "available").length },
        { label: "待確認", value: "pending", count: list.filter(i => i.status === "pending").length },
        { label: "交換中", value: "exchanging", count: list.filter(i => i.status === "exchanging").length },
        { label: "交換完成", value: "completed", count: list.filter(i => i.status === "completed").length }
      ];
    }

  // 我提出的申請：四種狀態
      return [
        { label: "全部", value: "all", count: list.length },
        { label: "申請中", value: "申請中", count: list.filter(i => i.stateLabel === "申請中").length },
        { label: "已回覆", value: "已回覆", count: list.filter(i => i.stateLabel === "已回覆").length },
        { label: "交換中", value: "交換中", count: list.filter(i => i.stateLabel === "交換中").length },
        { label: "交換完成", value: "交換完成", count: list.filter(i => i.stateLabel === "交換完成").length }
      ];
  },
    paginatedList() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.filteredList.slice(start, end);
    },

    
  },
  async created(){
    await this.fetchCurrentMember();
    await this.fetchExchangeList();
  },

  methods: {
  
    async fetchCurrentMember() {
      const phpBaseUrl =
        location.hostname === "localhost" || location.hostname === "127.0.0.1"
        ? "http://localhost:8888/Spinix/php"
        : `${location.origin}/ckd101/g2/php`;
      const res = await fetch(`${phpBaseUrl}/member/currentMember_get.php`,{
        credentials: "include" 
      });
      const result = await res.json();

      this.currentUserId = result.success && result.isLoggedIn  ? result.member.id : null ;
    },

    async fetchExchangeList(){
      try{
        const res = await exchangeList();
        this.exchangeListData = Array.isArray(res) ? res : [];
      }catch(err){
        console.log('取得商品列表失敗:',err);
        this.exchangeListData = [];
      }
    },
    handleReplyExchange(item) {
      replyExchange(this.exchangeListData, {
        postId: item.post_id,
        applyId: item.applyId,
        posterName: item.username
      });
    }
  }
};
</script>

<style lang="scss" scoped>
  @use '@/assets/scss/_var' as *;


  .empty-msg{
    text-align: center;
    color: map-get($color, hint );
  }

  .pagination{
    margin: 0 auto;
    width: fit-content;
  }

  .sop{


    .sop-title{
      padding-bottom:12px ;
      font-size: 18px;
      font-weight: 900;
    }

    .sop-list{
      ul{
        width: 100%;
        display: grid;  
        grid-template-columns: repeat(2,1fr);
        gap: 12px;

        li{
          

          .list-item{
            display: flex;
            flex-direction: column;
            justify-content: center;
            
            padding: 12px 8px;
            border: 1px solid #fec96b;
            background: linear-gradient(145deg,#ffffff 0%,#fff7e8 100%);;
            border-radius: 10px;
            align-items: center;
            gap: 8px;

            max-width: 200px;
            margin: 0 auto;

            box-shadow: 0 6px 16px rgba(20, 28, 38, 0.06);
              .sop-img{
                width: 80px;
                img{
                  width: 100%;
                }
          }

          .sop-txt{
            flex: 1;
            color: map-get($color, secondary );
            .title{
              font-size: 16px;
              font-weight:bolder;
              color: #F29B00;
              padding-bottom: 12px;
              text-align: center;
            }

            .info{
              font-size: 14px;
              text-align: center;
            }
          }
        }
        
      }

        

        .icon-sop-next{
          display: none;
        }
      }
    }
  }

  #page-myexchange{
    display: flex;
    flex-direction: column;
    gap: 32px;
  }
  .page-myexchange-title{
    
    h2{
      color: map-get($color, secondary2 );
      font-size: 30px;
      font-weight: 700;
      padding-bottom: 12px;
    }

    p{
      font-size: 18px;
    }
  }
  
  .exchange-category{
    display: flex;
    gap: 12px;
    .tabs-style{
      padding: 0 12px;
      border-left: 3px solid map-get($color, hint );
      font-size: 16px;
      line-height: 1.5;
      color: map-get($color, hint );
    
      &.active{
        border-left-color:map-get($color, secondary2 );
        color: map-get($color, secondary );
      }
    }

    
  }
    .container{
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      
    }
  

  // 會員中心電腦版
  @media screen and (width >= 768px) {
      .container {
        grid-template-columns: repeat(3, 1fr);
        padding-block: 20px;
      }
        .sop{

          .sop-title{
            padding-bottom:24px ;
            font-size: 26px;
          }

    .sop-list{
      ul{
        display: flex;
        gap: 8px;
        
        li{
          display: flex;
          flex: 1;
          cursor: pointer;
          transition: transform .3s ease;

          .list-item{
            flex: 1;

            .sop-txt{

              .title{
                font-size: 18px;
              }
              .info{
                font-size: 16px;
                
              }
            }
          }
          
          &:hover{
            transform: translateY(-3px);
          }
        
          
          
        }
        .icon-sop-next{
          font-size: 32px;
          color: map-get($color, secondary2 );
          display: flex;
          align-items: center;
          
        }
      }
    }
  }
  }
</style>