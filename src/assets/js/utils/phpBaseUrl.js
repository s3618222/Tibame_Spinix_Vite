export const phpBaseUrl =
  location.hostname === "localhost" || location.hostname === "127.0.0.1"
    ? "http://localhost:8888/Spinix/php"
    : "/ckd101/g2/php";