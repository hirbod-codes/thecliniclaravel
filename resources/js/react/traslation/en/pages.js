import { account } from './pages/account.js';
import { auth } from './pages/auth.js';
import { orders } from './pages/orders.js';
import { visits } from './pages/visits.js';

let pages = {
    auth: auth,
    visits: visits,
    orders: orders,
    account: account
};

export { pages };
