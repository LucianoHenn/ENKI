import Reports from "../../../views/taboola/PerformanceTools/Reports";
import Overview from "../../../views/taboola/PerformanceTools/Overview";
import Campaigns from "../../../views/taboola/PerformanceTools/Campaigns";

export default [
  {
    name: "taboola.reports",
    path: "reports",
    component: Reports,
  },
  {
    name: "taboola.overview",
    path: "overview",
    component: Overview,
  },
  {
    name: "taboola.campaigns",
    path: "campaigns",
    component: Campaigns,
  },
];
