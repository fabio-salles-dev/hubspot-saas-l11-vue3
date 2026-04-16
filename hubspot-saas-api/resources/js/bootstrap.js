console.log("STEP 1");

import Echo from "laravel-echo";
import Pusher from "pusher-js";

console.log("STEP 2");

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: "pusher",
    key: import.meta.env.VITE_REVERB_APP_KEY,
});

console.log("STEP 3", window.Echo);
setTimeout(() => {
    console.log("DELAY CHECK:", window.Echo);
}, 500);
