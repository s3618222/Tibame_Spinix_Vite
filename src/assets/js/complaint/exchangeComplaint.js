import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";

export async function fetchExchangeComplaintContext(post_id, comm_id) {
   const parms = new URLSearchParams();
   if (post_id) parms.append('post_id', post_id);
   if (comm_id) parms.append('comm_id', comm_id);

   const res = await fetch(`${phpBaseUrl}/exchange/get_appeal_exc_content.php?${parms}`, {
      credentials: "include"
   });

   const result = await res.json();
   if (!result.success) {
      throw new Error(result.message || '取得申訴資料失敗');
   }

   return result;
}

export async function submitExchangeComplaint(post_id, comm_id, description, images) {
   const formData = new FormData();
   if (post_id) formData.append("post_id", post_id);
   if (comm_id) formData.append("comm_id", comm_id);
   formData.append("description", description);
   images.forEach(image => {
      formData.append("evidence_images[]", image.file);
   });

   const res = await fetch(`${phpBaseUrl}/exchange/post_appeal_exc.php`, {
      method: "POST",
      credentials: "include",
      body: formData
   });

   const result = await res.json();
   if (!result.success) {
      throw new Error(result.message || "送出失敗");
   }
   return result;
}