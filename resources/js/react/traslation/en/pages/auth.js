import { signup } from './auth/signup.js';
import { login } from './auth/login.js';

let auth = {
    signup: signup,
    login:login
};

export { auth };
