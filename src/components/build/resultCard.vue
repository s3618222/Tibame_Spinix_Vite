<!-- resultCard.vue -->
<template>
  <div class="result-card">
    <div class="card-tag">當前配置</div>
    <div class="pic-logo">spinix</div>

    <div class="pics-container">
      <div class="part-item" v-for="slot in partSlots" :key="slot.category">
        <div class="part-img-box">
          <img src="/build/background-circle.png" alt="" class="bg-ring">
          <!-- 有選圖 + 圖片載入成功 =>顯示零件圖 -->
          <img
            v-if="selectedParts[slot.category] && !imgFailedMap[slot.category]"
            :src="imgUrl(selectedParts[slot.category])"
            alt=""
            class="preview-pic"
            @error="imgFailedMap[slot.category] = true"
          >
          <!-- 有選圖 + 圖片載入失敗 => 顯示icon圖示 -->
          <i
            v-else-if="selectedParts[slot.category] && imgFailedMap[slot.category]"
            class="fa-solid fa-image preview-fallback"
          ></i>
          <!-- 都沒選 => 顯示零件類別名稱 -->
          <span v-else class="preview-placeholder">{{ slot.label }}</span>
        </div>
        <div class="part-txt">
          <p class="part-title">{{ slot.label }}</p>
          <p class="part-name">{{ selectedParts[slot.category] ? selectedParts[slot.category].name : '尚未選擇' }}</p>
        </div>

      </div>
    </div>



    <ul class="stat-list">
      <li class="stat-item">
        <span class="stat-label">攻擊</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" :style="{ width: statBarWidth(totalStats.atk) }"></span>
        </div>
        <span class="stat-value">{{ totalStats.atk }}</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">防守</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" :style="{ width: statBarWidth(totalStats.def) }"></span>
        </div>
        <span class="stat-value">{{ totalStats.def }}</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">持久</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" :style="{ width: statBarWidth(totalStats.sta) }"></span>
        </div>
        <span class="stat-value">{{ totalStats.sta }}</span>
      </li>
      <li class="stat-item">
        <span class="stat-label">重量(g)</span>
        <div class="stat-bar">
          <span class="stat-bar-fill" :style="{ width: statBarWidth(totalStats.wei) }"></span>
        </div>
        <span class="stat-value">{{ totalStats.wei }}</span>
      </li>
    </ul>

  </div>
</template>

<script>
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";
export default {
  name: "ResultCard",

  props: {
    selectedParts: { type: Object, default: () => ({}) }
  },

  data() {
    return {
      partSlots: [
        { category: 'blade', label: '戰刃' },
        { category: 'ratchet', label: '固鎖' },
        { category: 'bit', label: '軸心' }
      ],
      imgFailedMap: {} // { blade: true/false, ratchet: ..., bit: ... }
    }
  },

  computed: {
    totalStats() {
      return ['atk', 'def', 'sta', 'wei'].reduce((totals, key) => {
        totals[key] = this.partSlots.reduce((sum, slot) => {
          const part = this.selectedParts[slot.category];
          return sum + (part?.[key] ?? 0);
        }, 0);
        return totals;
      }, {});
    }
  },

  watch: {
    selectedParts: {
      deep: true,
      handler(newVal, oldVal) {
        // 逐一比對每個槽位，如果這個槽位的零件真的換了（id 不一樣），
        // 才重置對應的失敗記錄，避免沒換零件時被誤重置
        this.partSlots.forEach(slot => {
          const newPart = newVal[slot.category];
          const oldPart = oldVal?.[slot.category];

          if (newPart?.id !== oldPart?.id) {
            this.imgFailedMap[slot.category] = false;
          }
        });
      }
    }
  },

  methods: {
    imgUrl(part) {
      const baseUrl = import.meta.env.BASE_URL;
      const pic = part.image;
      if (pic.startsWith("uploads/beyblade/")) {
        return `${phpBaseUrl}/${pic}`;
      }

      return `${baseUrl}${pic.replace(/^\//, '')}`;
    },
    statBarWidth(value) {
      return `${Math.min(value, 100)}%`;
    }
  }
}
</script>

<style lang="scss" scoped>
@use "@/assets/scss/var" as *;
img {
  box-sizing: border-box;
}
.result-card {
  position: relative;
  width: 100%;
  background-color: map-get($color, secondary );
  border-radius: 12px;
  overflow: hidden;

}

.card-tag {
  color: map-get($color, secondary );
  font-weight: 600;
  font-size: 14px;
  padding: 4px 16px;
  position: absolute;
  top: 0;
  right: 0;
  background-color: map-get($color, secondary2 );
  border-radius: 0 0 0 12px;

}

.pic-logo {
  color: map-get($color, neutral );
  padding: 12px;
}

.pics-container {
  justify-content: space-between;
  padding: 12px 12px;
  // padding-inline: 8px;
  width: 100%;
  min-width: 0;
  // background-color: map-get($color, secondary );
  // height: 200px;
  display: flex;
  gap: 12px;

  .part-item {
    font-size: 16px;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    flex: 1 1 0;
    min-width: 0;

    .part-img-box {
      position: relative;
      width: 100%;
      max-width: 240px;
      aspect-ratio: 1 / 1;

      display: flex;
      align-items: center;
      justify-content: center;

      .bg-ring {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        animation: rotate 20s linear infinite;
      }

      @keyframes rotate {
        from {
          rotate: 0deg;
        }

        to {
          rotate: 360deg;
        }
      }

      .preview-pic {
        position: relative;
        z-index: 2;
        width: 60%;
        height: 60%;
        min-width: 0;
      }

      .preview-fallback {
        position: relative;
        z-index: 2;
        font-size: 40px;
        color: rgba(255, 255, 255, 0.6);
      }

      .preview-placeholder {
        position: relative;
        z-index: 2;
        font-size: 20px;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.5);
      }

    }

    .part-txt {
      color: white;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

  }

}




.stat-list {
  color: map-get($color , white );
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.stat-item{
  display: flex;
  align-items: center;
  line-height: 14px;
  font-size: 14px;
  gap: 4px;

  .stat-bar {
    background-color: map-get($color , hint );
    height: 10px;
    flex: 1;
    border-radius: 5px;
    position: relative;
    overflow: hidden;

    .stat-bar-fill {
      display: inline-block;
      position: absolute;
      top: 0;left: 0;
      height: 100%;
      background-color: map-get($color, primary );

    }
  }
}
</style>
