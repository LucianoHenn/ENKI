export default [
  {
    name: "dashboard",
    icon: "home",
    title: "Dashboard",
    roles: ["admin", "normal"],
    to: "/dashboard",
  },
  {
    name: "database",
    icon: "database",
    title: "Database",
    roles: ["admin", "normal"],
    children: [
      {
        name: "images",
        icon: "image",
        title: "Images",
        permissions: [], // todo later
        to: "/dashboard/database/images",
      },
      {
        name: "keywords",
        icon: "key",
        title: "Keywords",
        permissions: [], // todo later
        to: "/dashboard/database/keywords",
      },
    ],
  },
  {
    name: "keyword-tools",
    icon: "tool",
    title: "Keyword Tools",
    roles: ["admin", "normal"],
    children: [
      {
        name: "Google",
        title: "Google",
        permissions: [], // todo later
        children: [
          {
            name: "keyword-tools.google.keyword-ideas",
            title: "Keyword Ideas",
            permissions: [], // todo later
            to: "/dashboard/keyword-tools/google/keyword-ideas",
          },
          {
            name: "keyword-tools.google.historical-data",
            title: "Historical Data",
            permissions: [], // todo later
            to: "/dashboard/keyword-tools/google/historical-data",
          },
        ],
      },
    ],
  },
  {
    name: "facebook",
    icon: "facebook",
    title: "Facebook",
    roles: ["admin", "normal"],
    children: [
      {
        name: "creationTools",
        title: "Creation Tools",
        permissions: [], // todo later
        children: [
          {
            name: "facebook.creation-tools.campaign-generator",
            title: "Partnerships",
            permissions: [], // todo later
            to: "/dashboard/facebook/creation-tools/partnerships",
          },
          {
            name: "facebook.creation-tools.sites",
            title: "Sites",
            permissions: [], // todo later
            to: "/dashboard/facebook/creation-tools/sites",
          },
          {
            name: "facebook.creation-tools.pages-mapper",
            title: "Pages Mapper",
            permissions: [], // todo later
            to: "/dashboard/facebook/creation-tools/pages-mapper",
          },
          {
            name: "facebook.creation-tools.ad-copies",
            title: "AdCopies",
            permissions: [], // todo later
            to: "/dashboard/facebook/creation-tools/ad-copies",
          },
          {
            name: "facebook.creation-tools.campaign-generator",
            title: "Campaign Generator",
            permissions: [], // todo later
            to: "/dashboard/facebook/creation-tools/campaign-generator",
          },
        ],
      },
      {
        name: "optimizationTools",
        title: "Optimization Tools",
        permissions: [], // todo later
        to: "/dashboard/facebook/optimization-tools",
        children: [
          {
            name: "performanceAnalyzer",
            title: "Performance Analyzer",
            permissions: [], // todo later
            to: "/dashboard/facebook/performance-analyzer",
          },
          {
            name: "adSetsUpdater",
            title: "AdSets Updater",
            permissions: [], // todo later
            to: "/dashboard/facebook/ad-sets-updater",
          },
        ],
      },
      {
        name: "bidMultipliers",
        title: "Bid Multipliers",
        permissions: [], // todo later
        to: "/dashboard/facebook/optimization-tools",
        children: [
          {
            name: "configs",
            title: "Configs",
            permissions: [], // todo later
            to: "/dashboard/facebook/bid-configs",
          },
          {
            name: "Updater",
            title: "Updater",
            permissions: [], // todo later
            to: "/dashboard/facebook/bid-updater",
          },
        ],
      },
    ],
  },
  {
    name: "taboola",
    icon: "type",
    title: "Taboola",
    roles: ["admin", "normal"],
    children: [
      {
        name: "creationTools",
        title: "Creation Tools",
        permissions: [], // todo later
        children: [
          {
            name: "taboola.creation-tools.templates",
            title: "Templates",
            permissions: [], // todo later
            to: "/dashboard/taboola/creation-tools/templates",
          },
          // {
          //   name: "taboola.creation-tools.partnerships",
          //   title: "Partnerships",
          //   permissions: [], // todo later
          //   to: "/dashboard/taboola/creation-tools/partnerships",
          // },
          {
            name: "taboola.creation-tools.domains",
            title: "Domains",
            permissions: [], // todo later
            to: "/dashboard/taboola/creation-tools/domains",
          },
          {
            name: "taboola.creation-tools.campaign-generator",
            title: "Campaign Generator",
            permissions: [], // todo later
            to: "/dashboard/taboola/creation-tools/campaign-generator",
          },
        ],
      },
      {
        name: "performanceTools",
        title: "Performance Tools",
        permissions: [], // todo later
        children: [
          {
            name: "overview",
            title: "Overview",
            permissions: [], // todo later
            to: "/dashboard/taboola/overview",
          },
          {
            name: "reports",
            title: "Reports",
            permissions: [], // todo later
            to: "/dashboard/taboola/reports",
          },
          {
            name: "campaigns",
            title: "Campaigns",
            permissions: [], // todo later
            to: "/dashboard/taboola/campaigns",
          },
        ],
      },
    ],
  },
  {
    name: "admin",
    icon: "users",
    title: "Admin",
    roles: ["admin"],
    children: [
      {
        name: "users",
        icon: "users",
        title: "Users",
        permissions: [], // todo later
        to: "/dashboard/admin/users",
      },
      {
        name: "clients",
        icon: "coffee",
        title: "Clients",
        permissions: [], // todo later
        to: "/dashboard/admin/clients",
      },
      {
        name: "options",
        icon: "key",
        title: "Options",
        permissions: [], // todo later
        to: "/dashboard/admin/options",
      },
      {
        name: "Arc Associations",
        icon: "key",
        title: "Arc Associations",
        permissions: [], // todo later
        to: "/dashboard/admin/arc-associations",
      },
    ],
  },
  {
    name: "pages",
    icon: "file",
    title: "Pages",
    roles: ["admin"],
    children: [
      {
        name: "FAQ",
        title: "FAQ",
        permissions: [], // todo later
        to: "/dashboard/taboola/faq",
      },
      {
        name: "Privacy Policy",
        title: "Privacy Policy",
        permissions: [], // todo later
        to: "/dashboard/taboola/privacy-policy",
      },
    ],
  },
];
