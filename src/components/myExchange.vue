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
                <p class="title">Step.1 交換中</p>
                <p class="info">選擇交換對象</p>
              </div>
            </div>
            <div class="icon-next-sop">
              <i class="fa-solid fa-right-long"></i>
            </div>
          </li>
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
            <div class="icon-next-sop">
              <i class="fa-solid fa-right-long"></i>
            </div>
          </li>
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
            <div class="icon-next-sop">
              <i class="fa-solid fa-right-long"></i>
            </div>
          </li>
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

    <div class="container" v-if="filteredList.length">
      <ProductCard
        v-for="item in filteredList"
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

  </section>
</template>

<script>
import StatusTabs from "./controlTabs.vue";
import ProductCard from "./productCard.vue";
// 分頁器
import Pagination from "./pagination.vue";
import { exchangeList, statusLabelMap } from "../assets/js/mockExchangeData.js";  

export default {
  name: "MyExchange",

  components: {
    StatusTabs,
    ProductCard
  },

  data() {
    return {
      currentStatus: "all",
      currentUserId: 101,     
      exchangeList,
      statusLabelMap,
      activeTab: 'myexchange'
    };
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

    changeState(){
      // console.log(this);
      // this.
    }
  }
};
</script>

<style lang="scss" scoped>
  @use '@/assets/scss/_var' as *;

  .sop{
    // padding-inline: 24px;

    .sop-title{
      padding-bottom:12px ;
      font-size: 18px;
      font-weight: 900;
    }

    .sop-list{
      ul{
        display: grid;
        grid-template-columns: repeat(4,1fr);
        flex-wrap: nowrap;
        
        li{
          display: flex;
          .list-item{
            display: flex;
            padding: 12px;
            border: 1px solid #4F8A5B;
            background-color: #E4EEE7;
            border-radius: 10px;
            align-items: center;
            gap: 8px;
          .sop-img{
            width: 50px;
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
              color: #4F8A5B;
              padding-bottom: 12px;
            }
            .info{
              font-size: 14px;
            }
          }
          }
          

        
          
          
        }
        .icon-next-sop{
          font-size: 24px;
          color: map-get($color, secondary );
          display: flex;
          align-items: center;
          padding-inline: 4px;
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
      padding: 20px 16px;
    }
  

  // 平板
  @media screen and (width >= 768px) {
      .container {
        grid-template-columns: repeat(3, 1fr);
      }
  }
</style>