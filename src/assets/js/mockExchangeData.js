// mockExchangeData.js
export const exchangeList = [
   {
      id: 1,
      userId: 101,
      type: 'beyblade',
      product_img: 'CX13_01.webp',
      title: '龍王閃擊',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-07-26',
      city: '基隆市',
      district: '信義區',
      status: 'available'
   },
   {
      id: 2,
      userId: 101,
      type: 'blade',
      product_img: 'BX_02.webp',
      title: '蒼龍突擊',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-06',
      city: '台北市',
      district: '中正區',
      status: 'exchanging'
   },
   {
      id: 3,
      userId: 101,
      type: 'ratchet',
      product_img: 'BX23_01.webp',
      title: '鳳凰飛翼',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-07-06',
      city: '新北市',
      district: '板橋區',
      status: 'pending'
   },
   {
      id: 4,
      userId: 101,
      type: 'bit',
      product_img: 'CX02_01.webp',
      title: '魔導致尊',
      headshot: 'spinix_member_default.png',
      name: 'Lone軍團長',
      date: '2026-08-12',
      city: '台北市',
      district: '中正區',
      status: 'completed'
   },
   {
      id: 5,
      userId: 102,
      type: 'other',
      product_img: 'BX-28.webp',
      title: '旋風發射器 白色版',
      headshot: 'spinix_member_default.png',
      name: '會員B',
      date: '2026-08-13',
      city: '台北市',
      district: '中正區',
      status: 'available'
   }
];

// 狀態中文對照表，統一放這裡，兩邊 import 同一份
export const statusLabelMap = {
   available: '可交換',
   exchanging: '交換中',
   pending: '待確認',
   completed: '交換完成'
};