import { ifAdminAuthenticated } from '../../services/auth';
import clientRouter from './admin/_client.router';
import userRouter from './admin/_user.router';
import optionsRouter from './admin/_option.router';
import arcsRouter from './admin/_arc.router';
export default [{
  path: 'admin',
  beforeEnter: ifAdminAuthenticated,
  children: [
    ...userRouter,
    ...clientRouter,
    ...optionsRouter,
    ...arcsRouter
  ]
}];
