//約戰申訴用的API模組

//判斷目前執行環境，決定PHP的API路徑
const phpBaseUrl =
  location.hostname === "localhost" ||
    location.hostname === "127.0.0.1"
    ? "http://localhost:8888/Spinix/php"
    : "/ckd101/g2/php";


//取得指定約戰的申訴初始資料
export async function fetchBattleComplaintContext(battleId) {
  const response = await fetch(`${phpBaseUrl}/battle/battle_appeal_context_get.php?battle_id=${battleId}`, {
    credentials: "include"
  });

  const data = await response.json();

  //API回傳失敗時，中止目前流程，將錯誤交給catch處理
  if (!response.ok || !data.success) {
    throw new Error(data.message || "取得約戰申訴資料失敗");
  }

  return data;
}

//串接送出約戰申訴API
export async function submitBattleComplaint(battleId, description, images) {
  const formData = new FormData();

  //將約戰ID、申訴說明內容、佐證圖片打包進傳回後端的表單中
  formData.append("battle_id", battleId);
  formData.append("description", description);

  images.forEach((image) => {
    formData.append("evidence_images[]", image.file);
  });

  const response = await fetch(`${phpBaseUrl}/battle/battle_appeal_post.php`,
    {
      method: "POST",
      credentials: "include",
      body: formData
    }
  );

  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.message || "約戰申訴送出失敗");
  }

  return data;
}