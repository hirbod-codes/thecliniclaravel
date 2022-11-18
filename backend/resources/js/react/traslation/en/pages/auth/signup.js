import { ucFirstLetterAllWords } from '../../../translate.js';
import { general } from '../../general.js';

let signup = {
    'send-phone-number-verification-code': ucFirstLetterAllWords('Send ' + general.phonenumber.single.ucFirstLetterAllWords + ' Verification Code'),
    'fill-registration-form': 'Fill Registration Form',
    'security-code': 'Security Code',
    'choose-avatar': ucFirstLetterAllWords('choose an ' + general.avatar.single.allLowerCase),
};

export { signup };
