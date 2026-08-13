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
            <div class="list-item">
              <div class="sop-img">
                <img src="/sop01.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.1 可交換</p>
                <p class="info">選擇想交換對象</p>
              </div>
            </div>
          </li>
          <div class="icon-sop-next">
              <i class="fa-solid fa-right-long"></i>
            </div>
          <li>
            <div class="list-item">
              <div class="sop-img">
                <img src="/sop02.png" alt="">
              </div>
              <div class="sop-txt">
                <p class="title">Step.2 待確認</p>
                <p class="info">等待對方確認交換</p>
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
                <p class="info">交換已順利完成</p>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div class="container" v-if="paginatedList.length">
      <ProductCard
        v-for="item in paginatedList"
        :key="item.id"
        :id="item.id"
        :title="item.title"
        :image="item.product_img"
        :avatar="item.headshot"
        :username="item.name"
        :postDate="item.date"
        :city="item.city"
        :district="item.district"
        :state="statusLabelMap[item.status]"
      />
    </div>

    <p v-else class="empty-msg">目前沒有符合的交換紀錄</p>

    
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
import { exchangeList, statusLabelMap } from "../assets/js/mockExchangeData.js";  
// 分頁器
import Pagination from "@/components/pagination.vue";
import ElementPlus from "element-plus";
// import "element-plus/dist/index.css";



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
      currentUserId: 101,     
      exchangeList,
      statusLabelMap,
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
      }
  },

  computed: {
    
    myExchangeList() {
      return this.exchangeList.filter(item => item.userId === this.currentUserId);
    },

    
    filteredList() {
      if (this.currentStatus === "all") {
        return this.myExchangeList;
      }
      return this.myExchangeList.filter(item => item.status === this.currentStatus);
    },

    statusTabs() {
      const list = this.myExchangeList;  
      return [
        { label: "全部", value: "all", count: list.length },
        { label: "可交換", value: "available", count: list.filter(i => i.status === "available").length },
        { label: "交換中", value: "exchanging", count: list.filter(i => i.status === "exchanging").length },
        { label: "待確認", value: "pending", count: list.filter(i => i.status === "pending").length },
        { label: "交換完成", value: "completed", count: list.filter(i => i.status === "completed").length }
      ];
    },

    paginatedList() {
      const start = (this.currentPage - 1) * this.pageSize;
      const end = start + this.pageSize;
      return this.filteredList.slice(start, end);
    },
  }


};
</script>

<style lang="scss" scoped>
  @use '@/assets/scss/_var' as *;

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
            background-color: #FFF2D6;
            border-radius: 10px;
            align-items: center;
            gap: 8px;

            max-width: 200px;
            margin: 0 auto;

            box-shadow: 2px 2px 12px rgba(20, 28, 38, 0.1);
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