import { ucFirstLetterAllWords, ucFirstLetterFirstWord } from '../translate.js';
import { general } from './general.js';

let generalSentences = {};

generalSentences['verify-email-address'] = {};
generalSentences['verify-email-address'].allLowerCase = 'please ' + general.verify.single.allLowerCase + ' your ' + general['email-address'].single.allLowerCase;
generalSentences['verify-email-address'].ucFirstLetterFirstWord = ucFirstLetterFirstWord(generalSentences['verify-email-address'].allLowerCase);
generalSentences['verify-email-address'].ucFirstLetterAllWords = ucFirstLetterAllWords(generalSentences['verify-email-address'].allLowerCase);

generalSentences['send-email-verification-message'] = {};
generalSentences['send-email-verification-message'].allLowerCase = 'please check out your email inbox and click on the verification link.';
generalSentences['send-email-verification-message'].ucFirstLetterFirstWord = ucFirstLetterFirstWord(generalSentences['send-email-verification-message'].allLowerCase);
generalSentences['send-email-verification-message'].ucFirstLetterAllWords = ucFirstLetterAllWords(generalSentences['send-email-verification-message'].allLowerCase);

export { generalSentences };
