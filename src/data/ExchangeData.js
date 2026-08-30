const phpBaseUrl =
   location.hostname === "localhost" ||
      location.hostname === "127.0.0.1"
      ? "http://localhost:8888/Spinix/php"
      : `${location.origin}/ckd101/g2/php`;


// 顯示全部商品列表
export async function exchangeList() {
   let res = await fetch(`${phpBaseUrl}/exchange/get_Exchange.php`, {
      method: "GET",
      credentials: "include"
   });
   if (!res.ok) throw new Error('取得清單失敗');
   return await res.json();
}


// 顯示單筆商品詳細資料
export async function getExchangeDetail(postId) {
   let res = await fetch(`${phpBaseUrl}/exchange/get_ExchangeDetail.php?id=${postId}`, {
      method: "GET",
      credentials: "include"
   });

   if (!res.ok) throw new Error('取得詳細資料失敗');
   return await res.json();
}


// 取得文章中的留言
export async function getComments(postId) {
   let res = await fetch(`${phpBaseUrl}/exchange/get_ExchangeMsg.php?id=${postId}`, {
      method: "GET",
      credentials: "include"
   });

   if (!res.ok) throw new Error('取得留言失敗');
   return await res.json();
}


// 取得自己申請的留言
export async function getMyComments() {
   let res = await fetch(`${phpBaseUrl}/exchange/get_MyApplyComment.php`, {
      method: "GET",
      credentials: "include"
   });

   if (!res.ok) throw new Error('取得留言失敗');
   return await res.json();
}


// 文章本身的狀態中文對照表（可交換／交換中／待確認／交換完成）
export const statusLabelMap = {
   available: '可交換',
   exchanging: '交換中',
   pending: '待確認',
   completed: '交換完成'
};

// 陀螺類別
export const typeLabelMap = {
   beyblade: '陀螺本體',
   blade: '戰刃',
   ratchet: '固鎖',
   bit: '軸心',
   others: '其他'
};

// 商品狀態
export const conditionLabelMap = {
   new: '全新',
   good: '二手-良好',
   fair: '二手-有使用痕跡'
};
// 「我提出的申請」用的狀態中文對照表（跟文章狀態脫鉤，是「我」這則申請的結果）
export const applyStatusLabelMap = {
   available: '申請中',
   pending: '已回覆',
   exchanging: '交換中',
   completed: '交換完成'
};

// 回覆確認
export async function replyExchange({ applyId, posterName }) {
   const isConfirm = window.confirm(
      `${posterName}對你的交換提議有興趣!\n是否確認交換?按下確認後將可以查看雙方的聯絡資訊`
   );

   if (!isConfirm) return;

   const payload = {
      comm_id: applyId
   };


   try {
      const res = await fetch(`${phpBaseUrl}/exchange/replyToConfirm.php`, {
         method: "PATCH",
         credentials: "include",
         headers: { 'Content-Type': 'application/json' },
         body: JSON.stringify(payload)
      });

      const result = await res.json();

      if (result.success) {
         alert('確認成功!現在可以查看對方的聯絡資訊了');
         return true;
      } else {
         alert(result.message || '確認失敗');
         return false;
      }
   } catch (err) {
      alert('系統發生錯誤，請稍後再試');
      return false;
   }


}

export function completeExchange(exchangeList, { postId }) {
   const targetArticle = exchangeList.find(article => article.post_id === postId);
   if (targetArticle) {
      targetArticle.status = 'completed';
   }
}

export function cancelExchange(exchangeList, { postId }) {
   const targetArticle = exchangeList.find(article => article.post_id === postId);
   if (targetArticle) {
      targetArticle.status = 'available';
      targetArticle.exchange_comm_id = null;
   }
}