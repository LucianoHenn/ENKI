import OptimizationTools from '../../../views/facebook/OptimizationTools.vue';
import AdSetsUpdater from '../../../views/facebook/AdSetsUpdater.vue';
import PerformanceAnalyzer from '../../../views/facebook/PerformanceAnalyzer.vue';

export default [
  {
    name: 'facebook.optimization-tools',
    path: 'optimization-tools',
    component: OptimizationTools,
  },
  {
    name: 'facebook.ad-sets-updater',
    path: 'ad-sets-updater',
    component: AdSetsUpdater,
  },
  {
    name: 'facebook.performance-analyzer',
    path: 'performance-analyzer',
    component: PerformanceAnalyzer,
  },
]
