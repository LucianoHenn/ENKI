import { ifAuthenticated } from "../../services/auth";
import creationToolsRouter from "./taboola/_creationTools.router";
import performanceToolsRouter from "./taboola/_performanceTools.router";
import pagesRouter from "./taboola/_pages.router";

export default [
    {
        path: "taboola",
        beforeEnter: ifAuthenticated,
        children: [
            {
                path: "creation-tools",
                name: "taboola.creation-tools",
                children: [...creationToolsRouter],
            },
            ...performanceToolsRouter,
            ...pagesRouter,
        ],
    },
];
