import BidConfig from "../../../views/facebook/BidMultipliers/Config";
import BidUpdater from "../../../views/facebook/BidMultipliers/Updater";

export default [
  {
    name: "facebook.bid-updater",
    path: "bid-updater",
    component: BidUpdater,
  },
  {
    name: "facebook.bid-configs",
    path: "bid-configs",
    component: BidConfig,
  },
];
