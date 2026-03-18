import { createRouter, createWebHistory } from "vue-router";
import HubspotView from "../views/HubspotView.vue";
import Dashboard from "../pages/Dashboard.vue";
import Deals from "../pages/Deals.vue";

const routes = [
  {
    path: "/hubspot",
    component: Dashboard,
  },
  {
    path: "/deals",
    name: "deals",
    component: Deals,
  },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});
