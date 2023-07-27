import CampaignGenerator from "../../../views/taboola/CreationTools/CampaignGenerator";
import Templates from "../../../views/taboola/CreationTools/Templates";
import Domains from "../../../views/taboola/CreationTools/Domains";
import Partnerships from "../../../views/taboola/CreationTools/Partnerships";

export default [
  {
    path: "campaign-generator",
    name: "taboola.campaign-generator",
    component: CampaignGenerator,
  },
  {
    path: "templates",
    name: "taboola.templates",
    component: Templates,
  },
  {
    path: "domains",
    name: "taboola.domains",
    component: Domains,
  },
  {
    path: "partnerships",
    name: "taboola.domaipartnershipsns",
    component: Partnerships,
  },
];
