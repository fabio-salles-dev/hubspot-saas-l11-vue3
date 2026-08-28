import "./bootstrap";

import { createApp } from "vue";
import App from "./App.vue";

createApp(App).mount("#app");

console.log("APP OK");
console.log("Echo:", window.Echo);
