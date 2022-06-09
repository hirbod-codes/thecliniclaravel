import { ucFirstLetterAllWords } from '../../../translate.js';
import { general } from '../../general.js';

let signup = {
    steps: {
        'send-phone-number-verification-code': ucFirstLetterAllWords('Send ' + general.phonenumber.single + ' Verification Code'),
        'fill-registration-form': 'Fill Registration Form',
        'security-code': 'Security Code',
        'choose-avatar': ucFirstLetterAllWords('choose an ' + general.avatar.single),
    }
};

export { signup };
