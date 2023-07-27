import { ifAuthenticated } from "../services/auth";
import AppLayout from "../layouts/app-layout.vue";
import Dashboard from "../views/index.vue";
import adminRoutes from "./dashboard/_admin.router";
import faceBookRouters from "./dashboard/_facebook.router";
import taboolaRouters from "./dashboard/_taboola.router";
import databaseRouters from "./dashboard/_database.router";
import keywordToolsRouter from "./dashboard/_keyword-tools.router";

export default [
  {
    path: "/dashboard",
    component: AppLayout,
    beforeEnter: ifAuthenticated,
    children: [
      {
        path: "",
        name: "dashboard",
        component: Dashboard,
      },
      ...databaseRouters,
      ...taboolaRouters,
      ...faceBookRouters,
      ...adminRoutes,
      ...keywordToolsRouter,
    ],
  },
];
