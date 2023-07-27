import Sites from "../../../views/facebook/CreationTools/Sites";
import AdCopies from "../../../views/facebook/CreationTools/AdCopies";
import CampaignGenerator from "../../../views/facebook/CreationTools/CampaignGenerator";
import PagesMapper from "../../../views/facebook/CreationTools/PagesMapper";
import Partnerships from "../../../views/facebook/CreationTools/Partnerships";

export default [
  {
    path: "sites",
    name: "facebook.sites",
    component: Sites,
  },
  {
    path: "ad-copies",
    name: "facebook.ad-copies",
    component: AdCopies,
  },
  {
    path: "campaign-generator",
    name: "facebook.campaign-generator",
    component: CampaignGenerator,
  },
  {
    path: "pages-mapper",
    name: "facebook.pages-mapper",
    component: PagesMapper,
  },
  {
    path: "partnerships",
    name: "facebook.partnerships",
    component: Partnerships,
  },
];
