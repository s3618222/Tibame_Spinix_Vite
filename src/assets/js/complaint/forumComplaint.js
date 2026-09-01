import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";

// 取得指定文章/留言的申訴初始資料（被檢舉人是誰、標題內容等）
export async function fetchForumComplaintContext(artId, msgId) {
  const params = new URLSearchParams();
  if (artId) params.append("art_id", artId);
  if (msgId) params.append("msg_id", msgId);

  const response = await fetch(`${phpBaseUrl}/forum/forum_appeal_context_get.php?${params}`, {
    credentials: "include"
  });

  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.message || "取得論壇申訴資料失敗");
  }

  return data;
}

// 串接送出論壇申訴 API
export async function submitForumComplaint(artId, msgId, description, images) {
  const formData = new FormData();

  if (artId) formData.append("art_id", artId);
  if (msgId) formData.append("msg_id", msgId);
  formData.append("description", description);

  images.forEach((image) => {
    formData.append("evidence_images[]", image.file);
  });

  const response = await fetch(`${phpBaseUrl}/forum/forum_appeal_post.php`, {
    method: "POST",
    credentials: "include",
    body: formData
  });

  const data = await response.json();

  if (!response.ok || !data.success) {
    throw new Error(data.message || "論壇申訴送出失敗");
  }

  return data;
}