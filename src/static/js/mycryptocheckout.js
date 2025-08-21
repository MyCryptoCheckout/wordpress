;(function (globalObject) {
  'use strict';

/*
 *      bignumber.js v9.3.1
 *      A JavaScript library for arbitrary-precision arithmetic.
 *      https://github.com/MikeMcl/bignumber.js
 *      Copyright (c) 2025 Michael Mclaughlin <M8ch88l@gmail.com>
 *      MIT Licensed.
 *
 *      BigNumber.prototype methods     |  BigNumber methods
 *                                      |
 *      absoluteValue            abs    |  clone
 *      comparedTo                      |  config               set
 *      decimalPlaces            dp     |      DECIMAL_PLACES
 *      dividedBy                div    |      ROUNDING_MODE
 *      dividedToIntegerBy       idiv   |      EXPONENTIAL_AT
 *      exponentiatedBy          pow    |      RANGE
 *      integerValue                    |      CRYPTO
 *      isEqualTo                eq     |      MODULO_MODE
 *      isFinite                        |      POW_PRECISION
 *      isGreaterThan            gt     |      FORMAT
 *      isGreaterThanOrEqualTo   gte    |      ALPHABET
 *      isInteger                       |  isBigNumber
 *      isLessThan               lt     |  maximum              max
 *      isLessThanOrEqualTo      lte    |  minimum              min
 *      isNaN                           |  random
 *      isNegative                      |  sum
 *      isPositive                      |
 *      isZero                          |
 *      minus                           |
 *      modulo                   mod    |
 *      multipliedBy             times  |
 *      negated                         |
 *      plus                            |
 *      precision                sd     |
 *      shiftedBy                       |
 *      squareRoot               sqrt   |
 *      toExponential                   |
 *      toFixed                         |
 *      toFormat                        |
 *      toFraction                      |
 *      toJSON                          |
 *      toNumber                        |
 *      toPrecision                     |
 *      toString                        |
 *      valueOf                         |
 *
 */


  var BigNumber,
    isNumeric = /^-?(?:\d+(?:\.\d*)?|\.\d+)(?:e[+-]?\d+)?$/i,
    mathceil = Math.ceil,
    mathfloor = Math.floor,

    bignumberError = '[BigNumber Error] ',
    tooManyDigits = bignumberError + 'Number primitive has more than 15 significant digits: ',

    BASE = 1e14,
    LOG_BASE = 14,
    MAX_SAFE_INTEGER = 0x1fffffffffffff,         // 2^53 - 1
    // MAX_INT32 = 0x7fffffff,                   // 2^31 - 1
    POWS_TEN = [1, 10, 100, 1e3, 1e4, 1e5, 1e6, 1e7, 1e8, 1e9, 1e10, 1e11, 1e12, 1e13],
    SQRT_BASE = 1e7,

    // EDITABLE
    // The limit on the value of DECIMAL_PLACES, TO_EXP_NEG, TO_EXP_POS, MIN_EXP, MAX_EXP, and
    // the arguments to toExponential, toFixed, toFormat, and toPrecision.
    MAX = 1E9;                                   // 0 to MAX_INT32


  /*
   * Create and return a BigNumber constructor.
   */
  function clone(configObject) {
    var div, convertBase, parseNumeric,
      P = BigNumber.prototype = { constructor: BigNumber, toString: null, valueOf: null },
      ONE = new BigNumber(1),


      //----------------------------- EDITABLE CONFIG DEFAULTS -------------------------------


      // The default values below must be integers within the inclusive ranges stated.
      // The values can also be changed at run-time using BigNumber.set.

      // The maximum number of decimal places for operations involving division.
      DECIMAL_PLACES = 20,                     // 0 to MAX

      // The rounding mode used when rounding to the above decimal places, and when using
      // toExponential, toFixed, toFormat and toPrecision, and round (default value).
      // UP         0 Away from zero.
      // DOWN       1 Towards zero.
      // CEIL       2 Towards +Infinity.
      // FLOOR      3 Towards -Infinity.
      // HALF_UP    4 Towards nearest neighbour. If equidistant, up.
      // HALF_DOWN  5 Towards nearest neighbour. If equidistant, down.
      // HALF_EVEN  6 Towards nearest neighbour. If equidistant, towards even neighbour.
      // HALF_CEIL  7 Towards nearest neighbour. If equidistant, towards +Infinity.
      // HALF_FLOOR 8 Towards nearest neighbour. If equidistant, towards -Infinity.
      ROUNDING_MODE = 4,                       // 0 to 8

      // EXPONENTIAL_AT : [TO_EXP_NEG , TO_EXP_POS]

      // The exponent value at and beneath which toString returns exponential notation.
      // Number type: -7
      TO_EXP_NEG = -7,                         // 0 to -MAX

      // The exponent value at and above which toString returns exponential notation.
      // Number type: 21
      TO_EXP_POS = 21,                         // 0 to MAX

      // RANGE : [MIN_EXP, MAX_EXP]

      // The minimum exponent value, beneath which underflow to zero occurs.
      // Number type: -324  (5e-324)
      MIN_EXP = -1e7,                          // -1 to -MAX

      // The maximum exponent value, above which overflow to Infinity occurs.
      // Number type:  308  (1.7976931348623157e+308)
      // For MAX_EXP > 1e7, e.g. new BigNumber('1e100000000').plus(1) may be slow.
      MAX_EXP = 1e7,                           // 1 to MAX

      // Whether to use cryptographically-secure random number generation, if available.
      CRYPTO = false,                          // true or false

      // The modulo mode used when calculating the modulus: a mod n.
      // The quotient (q = a / n) is calculated according to the corresponding rounding mode.
      // The remainder (r) is calculated as: r = a - n * q.
      //
      // UP        0 The remainder is positive if the dividend is negative, else is negative.
      // DOWN      1 The remainder has the same sign as the dividend.
      //             This modulo mode is commonly known as 'truncated division' and is
      //             equivalent to (a % n) in JavaScript.
      // FLOOR     3 The remainder has the same sign as the divisor (Python %).
      // HALF_EVEN 6 This modulo mode implements the IEEE 754 remainder function.
      // EUCLID    9 Euclidian division. q = sign(n) * floor(a / abs(n)).
      //             The remainder is always positive.
      //
      // The truncated division, floored division, Euclidian division and IEEE 754 remainder
      // modes are commonly used for the modulus operation.
      // Although the other rounding modes can also be used, they may not give useful results.
      MODULO_MODE = 1,                         // 0 to 9

      // The maximum number of significant digits of the result of the exponentiatedBy operation.
      // If POW_PRECISION is 0, there will be unlimited significant digits.
      POW_PRECISION = 0,                       // 0 to MAX

      // The format specification used by the BigNumber.prototype.toFormat method.
      FORMAT = {
        prefix: '',
        groupSize: 3,
        secondaryGroupSize: 0,
        groupSeparator: ',',
        decimalSeparator: '.',
        fractionGroupSize: 0,
        fractionGroupSeparator: '\xA0',        // non-breaking space
        suffix: ''
      },

      // The alphabet used for base conversion. It must be at least 2 characters long, with no '+',
      // '-', '.', whitespace, or repeated character.
      // '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ$_'
      ALPHABET = '0123456789abcdefghijklmnopqrstuvwxyz',
      alphabetHasNormalDecimalDigits = true;


    //------------------------------------------------------------------------------------------


    // CONSTRUCTOR


    /*
     * The BigNumber constructor and exported function.
     * Create and return a new instance of a BigNumber object.
     *
     * v {number|string|BigNumber} A numeric value.
     * [b] {number} The base of v. Integer, 2 to ALPHABET.length inclusive.
     */
    function BigNumber(v, b) {
      var alphabet, c, caseChanged, e, i, isNum, len, str,
        x = this;

      // Enable constructor call without `new`.
      if (!(x instanceof BigNumber)) return new BigNumber(v, b);

      if (b == null) {

        if (v && v._isBigNumber === true) {
          x.s = v.s;

          if (!v.c || v.e > MAX_EXP) {
            x.c = x.e = null;
          } else if (v.e < MIN_EXP) {
            x.c = [x.e = 0];
          } else {
            x.e = v.e;
            x.c = v.c.slice();
          }

          return;
        }

        if ((isNum = typeof v == 'number') && v * 0 == 0) {

          // Use `1 / n` to handle minus zero also.
          x.s = 1 / v < 0 ? (v = -v, -1) : 1;

          // Fast path for integers, where n < 2147483648 (2**31).
          if (v === ~~v) {
            for (e = 0, i = v; i >= 10; i /= 10, e++);

            if (e > MAX_EXP) {
              x.c = x.e = null;
            } else {
              x.e = e;
              x.c = [v];
            }

            return;
          }

          str = String(v);
        } else {

          if (!isNumeric.test(str = String(v))) return parseNumeric(x, str, isNum);

          x.s = str.charCodeAt(0) == 45 ? (str = str.slice(1), -1) : 1;
        }

        // Decimal point?
        if ((e = str.indexOf('.')) > -1) str = str.replace('.', '');

        // Exponential form?
        if ((i = str.search(/e/i)) > 0) {

          // Determine exponent.
          if (e < 0) e = i;
          e += +str.slice(i + 1);
          str = str.substring(0, i);
        } else if (e < 0) {

          // Integer.
          e = str.length;
        }

      } else {

        // '[BigNumber Error] Base {not a primitive number|not an integer|out of range}: {b}'
        intCheck(b, 2, ALPHABET.length, 'Base');

        // Allow exponential notation to be used with base 10 argument, while
        // also rounding to DECIMAL_PLACES as with other bases.
        if (b == 10 && alphabetHasNormalDecimalDigits) {
          x = new BigNumber(v);
          return round(x, DECIMAL_PLACES + x.e + 1, ROUNDING_MODE);
        }

        str = String(v);

        if (isNum = typeof v == 'number') {

          // Avoid potential interpretation of Infinity and NaN as base 44+ values.
          if (v * 0 != 0) return parseNumeric(x, str, isNum, b);

          x.s = 1 / v < 0 ? (str = str.slice(1), -1) : 1;

          // '[BigNumber Error] Number primitive has more than 15 significant digits: {n}'
          if (BigNumber.DEBUG && str.replace(/^0\.0*|\./, '').length > 15) {
            throw Error
             (tooManyDigits + v);
          }
        } else {
          x.s = str.charCodeAt(0) === 45 ? (str = str.slice(1), -1) : 1;
        }

        alphabet = ALPHABET.slice(0, b);
        e = i = 0;

        // Check that str is a valid base b number.
        // Don't use RegExp, so alphabet can contain special characters.
        for (len = str.length; i < len; i++) {
          if (alphabet.indexOf(c = str.charAt(i)) < 0) {
            if (c == '.') {

              // If '.' is not the first character and it has not be found before.
              if (i > e) {
                e = len;
                continue;
              }
            } else if (!caseChanged) {

              // Allow e.g. hexadecimal 'FF' as well as 'ff'.
              if (str == str.toUpperCase() && (str = str.toLowerCase()) ||
                  str == str.toLowerCase() && (str = str.toUpperCase())) {
                caseChanged = true;
                i = -1;
                e = 0;
                continue;
              }
            }

            return parseNumeric(x, String(v), isNum, b);
          }
        }

        // Prevent later check for length on converted number.
        isNum = false;
        str = convertBase(str, b, 10, x.s);

        // Decimal point?
        if ((e = str.indexOf('.')) > -1) str = str.replace('.', '');
        else e = str.length;
      }

      // Determine leading zeros.
      for (i = 0; str.charCodeAt(i) === 48; i++);

      // Determine trailing zeros.
      for (len = str.length; str.charCodeAt(--len) === 48;);

      if (str = str.slice(i, ++len)) {
        len -= i;

        // '[BigNumber Error] Number primitive has more than 15 significant digits: {n}'
        if (isNum && BigNumber.DEBUG &&
          len > 15 && (v > MAX_SAFE_INTEGER || v !== mathfloor(v))) {
            throw Error
             (tooManyDigits + (x.s * v));
        }

         // Overflow?
        if ((e = e - i - 1) > MAX_EXP) {

          // Infinity.
          x.c = x.e = null;

        // Underflow?
        } else if (e < MIN_EXP) {

          // Zero.
          x.c = [x.e = 0];
        } else {
          x.e = e;
          x.c = [];

          // Transform base

          // e is the base 10 exponent.
          // i is where to slice str to get the first element of the coefficient array.
          i = (e + 1) % LOG_BASE;
          if (e < 0) i += LOG_BASE;  // i < 1

          if (i < len) {
            if (i) x.c.push(+str.slice(0, i));

            for (len -= LOG_BASE; i < len;) {
              x.c.push(+str.slice(i, i += LOG_BASE));
            }

            i = LOG_BASE - (str = str.slice(i)).length;
          } else {
            i -= len;
          }

          for (; i--; str += '0');
          x.c.push(+str);
        }
      } else {

        // Zero.
        x.c = [x.e = 0];
      }
    }


    // CONSTRUCTOR PROPERTIES


    BigNumber.clone = clone;

    BigNumber.ROUND_UP = 0;
    BigNumber.ROUND_DOWN = 1;
    BigNumber.ROUND_CEIL = 2;
    BigNumber.ROUND_FLOOR = 3;
    BigNumber.ROUND_HALF_UP = 4;
    BigNumber.ROUND_HALF_DOWN = 5;
    BigNumber.ROUND_HALF_EVEN = 6;
    BigNumber.ROUND_HALF_CEIL = 7;
    BigNumber.ROUND_HALF_FLOOR = 8;
    BigNumber.EUCLID = 9;


    /*
     * Configure infrequently-changing library-wide settings.
     *
     * Accept an object with the following optional properties (if the value of a property is
     * a number, it must be an integer within the inclusive range stated):
     *
     *   DECIMAL_PLACES   {number}           0 to MAX
     *   ROUNDING_MODE    {number}           0 to 8
     *   EXPONENTIAL_AT   {number|number[]}  -MAX to MAX  or  [-MAX to 0, 0 to MAX]
     *   RANGE            {number|number[]}  -MAX to MAX (not zero)  or  [-MAX to -1, 1 to MAX]
     *   CRYPTO           {boolean}          true or false
     *   MODULO_MODE      {number}           0 to 9
     *   POW_PRECISION       {number}           0 to MAX
     *   ALPHABET         {string}           A string of two or more unique characters which does
     *                                       not contain '.'.
     *   FORMAT           {object}           An object with some of the following properties:
     *     prefix                 {string}
     *     groupSize              {number}
     *     secondaryGroupSize     {number}
     *     groupSeparator         {string}
     *     decimalSeparator       {string}
     *     fractionGroupSize      {number}
     *     fractionGroupSeparator {string}
     *     suffix                 {string}
     *
     * (The values assigned to the above FORMAT object properties are not checked for validity.)
     *
     * E.g.
     * BigNumber.config({ DECIMAL_PLACES : 20, ROUNDING_MODE : 4 })
     *
     * Ignore properties/parameters set to null or undefined, except for ALPHABET.
     *
     * Return an object with the properties current values.
     */
    BigNumber.config = BigNumber.set = function (obj) {
      var p, v;

      if (obj != null) {

        if (typeof obj == 'object') {

          // DECIMAL_PLACES {number} Integer, 0 to MAX inclusive.
          // '[BigNumber Error] DECIMAL_PLACES {not a primitive number|not an integer|out of range}: {v}'
          if (obj.hasOwnProperty(p = 'DECIMAL_PLACES')) {
            v = obj[p];
            intCheck(v, 0, MAX, p);
            DECIMAL_PLACES = v;
          }

          // ROUNDING_MODE {number} Integer, 0 to 8 inclusive.
          // '[BigNumber Error] ROUNDING_MODE {not a primitive number|not an integer|out of range}: {v}'
          if (obj.hasOwnProperty(p = 'ROUNDING_MODE')) {
            v = obj[p];
            intCheck(v, 0, 8, p);
            ROUNDING_MODE = v;
          }

          // EXPONENTIAL_AT {number|number[]}
          // Integer, -MAX to MAX inclusive or
          // [integer -MAX to 0 inclusive, 0 to MAX inclusive].
          // '[BigNumber Error] EXPONENTIAL_AT {not a primitive number|not an integer|out of range}: {v}'
          if (obj.hasOwnProperty(p = 'EXPONENTIAL_AT')) {
            v = obj[p];
            if (v && v.pop) {
              intCheck(v[0], -MAX, 0, p);
              intCheck(v[1], 0, MAX, p);
              TO_EXP_NEG = v[0];
              TO_EXP_POS = v[1];
            } else {
              intCheck(v, -MAX, MAX, p);
              TO_EXP_NEG = -(TO_EXP_POS = v < 0 ? -v : v);
            }
          }

          // RANGE {number|number[]} Non-zero integer, -MAX to MAX inclusive or
          // [integer -MAX to -1 inclusive, integer 1 to MAX inclusive].
          // '[BigNumber Error] RANGE {not a primitive number|not an integer|out of range|cannot be zero}: {v}'
          if (obj.hasOwnProperty(p = 'RANGE')) {
            v = obj[p];
            if (v && v.pop) {
              intCheck(v[0], -MAX, -1, p);
              intCheck(v[1], 1, MAX, p);
              MIN_EXP = v[0];
              MAX_EXP = v[1];
            } else {
              intCheck(v, -MAX, MAX, p);
              if (v) {
                MIN_EXP = -(MAX_EXP = v < 0 ? -v : v);
              } else {
                throw Error
                 (bignumberError + p + ' cannot be zero: ' + v);
              }
            }
          }

          // CRYPTO {boolean} true or false.
          // '[BigNumber Error] CRYPTO not true or false: {v}'
          // '[BigNumber Error] crypto unavailable'
          if (obj.hasOwnProperty(p = 'CRYPTO')) {
            v = obj[p];
            if (v === !!v) {
              if (v) {
                if (typeof crypto != 'undefined' && crypto &&
                 (crypto.getRandomValues || crypto.randomBytes)) {
                  CRYPTO = v;
                } else {
                  CRYPTO = !v;
                  throw Error
                   (bignumberError + 'crypto unavailable');
                }
              } else {
                CRYPTO = v;
              }
            } else {
              throw Error
               (bignumberError + p + ' not true or false: ' + v);
            }
          }

          // MODULO_MODE {number} Integer, 0 to 9 inclusive.
          // '[BigNumber Error] MODULO_MODE {not a primitive number|not an integer|out of range}: {v}'
          if (obj.hasOwnProperty(p = 'MODULO_MODE')) {
            v = obj[p];
            intCheck(v, 0, 9, p);
            MODULO_MODE = v;
          }

          // POW_PRECISION {number} Integer, 0 to MAX inclusive.
          // '[BigNumber Error] POW_PRECISION {not a primitive number|not an integer|out of range}: {v}'
          if (obj.hasOwnProperty(p = 'POW_PRECISION')) {
            v = obj[p];
            intCheck(v, 0, MAX, p);
            POW_PRECISION = v;
          }

          // FORMAT {object}
          // '[BigNumber Error] FORMAT not an object: {v}'
          if (obj.hasOwnProperty(p = 'FORMAT')) {
            v = obj[p];
            if (typeof v == 'object') FORMAT = v;
            else throw Error
             (bignumberError + p + ' not an object: ' + v);
          }

          // ALPHABET {string}
          // '[BigNumber Error] ALPHABET invalid: {v}'
          if (obj.hasOwnProperty(p = 'ALPHABET')) {
            v = obj[p];

            // Disallow if less than two characters,
            // or if it contains '+', '-', '.', whitespace, or a repeated character.
            if (typeof v == 'string' && !/^.?$|[+\-.\s]|(.).*\1/.test(v)) {
              alphabetHasNormalDecimalDigits = v.slice(0, 10) == '0123456789';
              ALPHABET = v;
            } else {
              throw Error
               (bignumberError + p + ' invalid: ' + v);
            }
          }

        } else {

          // '[BigNumber Error] Object expected: {v}'
          throw Error
           (bignumberError + 'Object expected: ' + obj);
        }
      }

      return {
        DECIMAL_PLACES: DECIMAL_PLACES,
        ROUNDING_MODE: ROUNDING_MODE,
        EXPONENTIAL_AT: [TO_EXP_NEG, TO_EXP_POS],
        RANGE: [MIN_EXP, MAX_EXP],
        CRYPTO: CRYPTO,
        MODULO_MODE: MODULO_MODE,
        POW_PRECISION: POW_PRECISION,
        FORMAT: FORMAT,
        ALPHABET: ALPHABET
      };
    };


    /*
     * Return true if v is a BigNumber instance, otherwise return false.
     *
     * If BigNumber.DEBUG is true, throw if a BigNumber instance is not well-formed.
     *
     * v {any}
     *
     * '[BigNumber Error] Invalid BigNumber: {v}'
     */
    BigNumber.isBigNumber = function (v) {
      if (!v || v._isBigNumber !== true) return false;
      if (!BigNumber.DEBUG) return true;

      var i, n,
        c = v.c,
        e = v.e,
        s = v.s;

      out: if ({}.toString.call(c) == '[object Array]') {

        if ((s === 1 || s === -1) && e >= -MAX && e <= MAX && e === mathfloor(e)) {

          // If the first element is zero, the BigNumber value must be zero.
          if (c[0] === 0) {
            if (e === 0 && c.length === 1) return true;
            break out;
          }

          // Calculate number of digits that c[0] should have, based on the exponent.
          i = (e + 1) % LOG_BASE;
          if (i < 1) i += LOG_BASE;

          // Calculate number of digits of c[0].
          //if (Math.ceil(Math.log(c[0] + 1) / Math.LN10) == i) {
          if (String(c[0]).length == i) {

            for (i = 0; i < c.length; i++) {
              n = c[i];
              if (n < 0 || n >= BASE || n !== mathfloor(n)) break out;
            }

            // Last element cannot be zero, unless it is the only element.
            if (n !== 0) return true;
          }
        }

      // Infinity/NaN
      } else if (c === null && e === null && (s === null || s === 1 || s === -1)) {
        return true;
      }

      throw Error
        (bignumberError + 'Invalid BigNumber: ' + v);
    };


    /*
     * Return a new BigNumber whose value is the maximum of the arguments.
     *
     * arguments {number|string|BigNumber}
     */
    BigNumber.maximum = BigNumber.max = function () {
      return maxOrMin(arguments, -1);
    };


    /*
     * Return a new BigNumber whose value is the minimum of the arguments.
     *
     * arguments {number|string|BigNumber}
     */
    BigNumber.minimum = BigNumber.min = function () {
      return maxOrMin(arguments, 1);
    };


    /*
     * Return a new BigNumber with a random value equal to or greater than 0 and less than 1,
     * and with dp, or DECIMAL_PLACES if dp is omitted, decimal places (or less if trailing
     * zeros are produced).
     *
     * [dp] {number} Decimal places. Integer, 0 to MAX inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {dp}'
     * '[BigNumber Error] crypto unavailable'
     */
    BigNumber.random = (function () {
      var pow2_53 = 0x20000000000000;

      // Return a 53 bit integer n, where 0 <= n < 9007199254740992.
      // Check if Math.random() produces more than 32 bits of randomness.
      // If it does, assume at least 53 bits are produced, otherwise assume at least 30 bits.
      // 0x40000000 is 2^30, 0x800000 is 2^23, 0x1fffff is 2^21 - 1.
      var random53bitInt = (Math.random() * pow2_53) & 0x1fffff
       ? function () { return mathfloor(Math.random() * pow2_53); }
       : function () { return ((Math.random() * 0x40000000 | 0) * 0x800000) +
         (Math.random() * 0x800000 | 0); };

      return function (dp) {
        var a, b, e, k, v,
          i = 0,
          c = [],
          rand = new BigNumber(ONE);

        if (dp == null) dp = DECIMAL_PLACES;
        else intCheck(dp, 0, MAX);

        k = mathceil(dp / LOG_BASE);

        if (CRYPTO) {

          // Browsers supporting crypto.getRandomValues.
          if (crypto.getRandomValues) {

            a = crypto.getRandomValues(new Uint32Array(k *= 2));

            for (; i < k;) {

              // 53 bits:
              // ((Math.pow(2, 32) - 1) * Math.pow(2, 21)).toString(2)
              // 11111 11111111 11111111 11111111 11100000 00000000 00000000
              // ((Math.pow(2, 32) - 1) >>> 11).toString(2)
              //                                     11111 11111111 11111111
              // 0x20000 is 2^21.
              v = a[i] * 0x20000 + (a[i + 1] >>> 11);

              // Rejection sampling:
              // 0 <= v < 9007199254740992
              // Probability that v >= 9e15, is
              // 7199254740992 / 9007199254740992 ~= 0.0008, i.e. 1 in 1251
              if (v >= 9e15) {
                b = crypto.getRandomValues(new Uint32Array(2));
                a[i] = b[0];
                a[i + 1] = b[1];
              } else {

                // 0 <= v <= 8999999999999999
                // 0 <= (v % 1e14) <= 99999999999999
                c.push(v % 1e14);
                i += 2;
              }
            }
            i = k / 2;

          // Node.js supporting crypto.randomBytes.
          } else if (crypto.randomBytes) {

            // buffer
            a = crypto.randomBytes(k *= 7);

            for (; i < k;) {

              // 0x1000000000000 is 2^48, 0x10000000000 is 2^40
              // 0x100000000 is 2^32, 0x1000000 is 2^24
              // 11111 11111111 11111111 11111111 11111111 11111111 11111111
              // 0 <= v < 9007199254740992
              v = ((a[i] & 31) * 0x1000000000000) + (a[i + 1] * 0x10000000000) +
                 (a[i + 2] * 0x100000000) + (a[i + 3] * 0x1000000) +
                 (a[i + 4] << 16) + (a[i + 5] << 8) + a[i + 6];

              if (v >= 9e15) {
                crypto.randomBytes(7).copy(a, i);
              } else {

                // 0 <= (v % 1e14) <= 99999999999999
                c.push(v % 1e14);
                i += 7;
              }
            }
            i = k / 7;
          } else {
            CRYPTO = false;
            throw Error
             (bignumberError + 'crypto unavailable');
          }
        }

        // Use Math.random.
        if (!CRYPTO) {

          for (; i < k;) {
            v = random53bitInt();
            if (v < 9e15) c[i++] = v % 1e14;
          }
        }

        k = c[--i];
        dp %= LOG_BASE;

        // Convert trailing digits to zeros according to dp.
        if (k && dp) {
          v = POWS_TEN[LOG_BASE - dp];
          c[i] = mathfloor(k / v) * v;
        }

        // Remove trailing elements which are zero.
        for (; c[i] === 0; c.pop(), i--);

        // Zero?
        if (i < 0) {
          c = [e = 0];
        } else {

          // Remove leading elements which are zero and adjust exponent accordingly.
          for (e = -1 ; c[0] === 0; c.splice(0, 1), e -= LOG_BASE);

          // Count the digits of the first element of c to determine leading zeros, and...
          for (i = 1, v = c[0]; v >= 10; v /= 10, i++);

          // adjust the exponent accordingly.
          if (i < LOG_BASE) e -= LOG_BASE - i;
        }

        rand.e = e;
        rand.c = c;
        return rand;
      };
    })();


    /*
     * Return a BigNumber whose value is the sum of the arguments.
     *
     * arguments {number|string|BigNumber}
     */
    BigNumber.sum = function () {
      var i = 1,
        args = arguments,
        sum = new BigNumber(args[0]);
      for (; i < args.length;) sum = sum.plus(args[i++]);
      return sum;
    };


    // PRIVATE FUNCTIONS


    // Called by BigNumber and BigNumber.prototype.toString.
    convertBase = (function () {
      var decimal = '0123456789';

      /*
       * Convert string of baseIn to an array of numbers of baseOut.
       * Eg. toBaseOut('255', 10, 16) returns [15, 15].
       * Eg. toBaseOut('ff', 16, 10) returns [2, 5, 5].
       */
      function toBaseOut(str, baseIn, baseOut, alphabet) {
        var j,
          arr = [0],
          arrL,
          i = 0,
          len = str.length;

        for (; i < len;) {
          for (arrL = arr.length; arrL--; arr[arrL] *= baseIn);

          arr[0] += alphabet.indexOf(str.charAt(i++));

          for (j = 0; j < arr.length; j++) {

            if (arr[j] > baseOut - 1) {
              if (arr[j + 1] == null) arr[j + 1] = 0;
              arr[j + 1] += arr[j] / baseOut | 0;
              arr[j] %= baseOut;
            }
          }
        }

        return arr.reverse();
      }

      // Convert a numeric string of baseIn to a numeric string of baseOut.
      // If the caller is toString, we are converting from base 10 to baseOut.
      // If the caller is BigNumber, we are converting from baseIn to base 10.
      return function (str, baseIn, baseOut, sign, callerIsToString) {
        var alphabet, d, e, k, r, x, xc, y,
          i = str.indexOf('.'),
          dp = DECIMAL_PLACES,
          rm = ROUNDING_MODE;

        // Non-integer.
        if (i >= 0) {
          k = POW_PRECISION;

          // Unlimited precision.
          POW_PRECISION = 0;
          str = str.replace('.', '');
          y = new BigNumber(baseIn);
          x = y.pow(str.length - i);
          POW_PRECISION = k;

          // Convert str as if an integer, then restore the fraction part by dividing the
          // result by its base raised to a power.

          y.c = toBaseOut(toFixedPoint(coeffToString(x.c), x.e, '0'),
           10, baseOut, decimal);
          y.e = y.c.length;
        }

        // Convert the number as integer.

        xc = toBaseOut(str, baseIn, baseOut, callerIsToString
         ? (alphabet = ALPHABET, decimal)
         : (alphabet = decimal, ALPHABET));

        // xc now represents str as an integer and converted to baseOut. e is the exponent.
        e = k = xc.length;

        // Remove trailing zeros.
        for (; xc[--k] == 0; xc.pop());

        // Zero?
        if (!xc[0]) return alphabet.charAt(0);

        // Does str represent an integer? If so, no need for the division.
        if (i < 0) {
          --e;
        } else {
          x.c = xc;
          x.e = e;

          // The sign is needed for correct rounding.
          x.s = sign;
          x = div(x, y, dp, rm, baseOut);
          xc = x.c;
          r = x.r;
          e = x.e;
        }

        // xc now represents str converted to baseOut.

        // The index of the rounding digit.
        d = e + dp + 1;

        // The rounding digit: the digit to the right of the digit that may be rounded up.
        i = xc[d];

        // Look at the rounding digits and mode to determine whether to round up.

        k = baseOut / 2;
        r = r || d < 0 || xc[d + 1] != null;

        r = rm < 4 ? (i != null || r) && (rm == 0 || rm == (x.s < 0 ? 3 : 2))
              : i > k || i == k &&(rm == 4 || r || rm == 6 && xc[d - 1] & 1 ||
               rm == (x.s < 0 ? 8 : 7));

        // If the index of the rounding digit is not greater than zero, or xc represents
        // zero, then the result of the base conversion is zero or, if rounding up, a value
        // such as 0.00001.
        if (d < 1 || !xc[0]) {

          // 1^-dp or 0
          str = r ? toFixedPoint(alphabet.charAt(1), -dp, alphabet.charAt(0)) : alphabet.charAt(0);
        } else {

          // Truncate xc to the required number of decimal places.
          xc.length = d;

          // Round up?
          if (r) {

            // Rounding up may mean the previous digit has to be rounded up and so on.
            for (--baseOut; ++xc[--d] > baseOut;) {
              xc[d] = 0;

              if (!d) {
                ++e;
                xc = [1].concat(xc);
              }
            }
          }

          // Determine trailing zeros.
          for (k = xc.length; !xc[--k];);

          // E.g. [4, 11, 15] becomes 4bf.
          for (i = 0, str = ''; i <= k; str += alphabet.charAt(xc[i++]));

          // Add leading zeros, decimal point and trailing zeros as required.
          str = toFixedPoint(str, e, alphabet.charAt(0));
        }

        // The caller will add the sign.
        return str;
      };
    })();


    // Perform division in the specified base. Called by div and convertBase.
    div = (function () {

      // Assume non-zero x and k.
      function multiply(x, k, base) {
        var m, temp, xlo, xhi,
          carry = 0,
          i = x.length,
          klo = k % SQRT_BASE,
          khi = k / SQRT_BASE | 0;

        for (x = x.slice(); i--;) {
          xlo = x[i] % SQRT_BASE;
          xhi = x[i] / SQRT_BASE | 0;
          m = khi * xlo + xhi * klo;
          temp = klo * xlo + ((m % SQRT_BASE) * SQRT_BASE) + carry;
          carry = (temp / base | 0) + (m / SQRT_BASE | 0) + khi * xhi;
          x[i] = temp % base;
        }

        if (carry) x = [carry].concat(x);

        return x;
      }

      function compare(a, b, aL, bL) {
        var i, cmp;

        if (aL != bL) {
          cmp = aL > bL ? 1 : -1;
        } else {

          for (i = cmp = 0; i < aL; i++) {

            if (a[i] != b[i]) {
              cmp = a[i] > b[i] ? 1 : -1;
              break;
            }
          }
        }

        return cmp;
      }

      function subtract(a, b, aL, base) {
        var i = 0;

        // Subtract b from a.
        for (; aL--;) {
          a[aL] -= i;
          i = a[aL] < b[aL] ? 1 : 0;
          a[aL] = i * base + a[aL] - b[aL];
        }

        // Remove leading zeros.
        for (; !a[0] && a.length > 1; a.splice(0, 1));
      }

      // x: dividend, y: divisor.
      return function (x, y, dp, rm, base) {
        var cmp, e, i, more, n, prod, prodL, q, qc, rem, remL, rem0, xi, xL, yc0,
          yL, yz,
          s = x.s == y.s ? 1 : -1,
          xc = x.c,
          yc = y.c;

        // Either NaN, Infinity or 0?
        if (!xc || !xc[0] || !yc || !yc[0]) {

          return new BigNumber(

           // Return NaN if either NaN, or both Infinity or 0.
           !x.s || !y.s || (xc ? yc && xc[0] == yc[0] : !yc) ? NaN :

            // Return ±0 if x is ±0 or y is ±Infinity, or return ±Infinity as y is ±0.
            xc && xc[0] == 0 || !yc ? s * 0 : s / 0
         );
        }

        q = new BigNumber(s);
        qc = q.c = [];
        e = x.e - y.e;
        s = dp + e + 1;

        if (!base) {
          base = BASE;
          e = bitFloor(x.e / LOG_BASE) - bitFloor(y.e / LOG_BASE);
          s = s / LOG_BASE | 0;
        }

        // Result exponent may be one less then the current value of e.
        // The coefficients of the BigNumbers from convertBase may have trailing zeros.
        for (i = 0; yc[i] == (xc[i] || 0); i++);

        if (yc[i] > (xc[i] || 0)) e--;

        if (s < 0) {
          qc.push(1);
          more = true;
        } else {
          xL = xc.length;
          yL = yc.length;
          i = 0;
          s += 2;

          // Normalise xc and yc so highest order digit of yc is >= base / 2.

          n = mathfloor(base / (yc[0] + 1));

          // Not necessary, but to handle odd bases where yc[0] == (base / 2) - 1.
          // if (n > 1 || n++ == 1 && yc[0] < base / 2) {
          if (n > 1) {
            yc = multiply(yc, n, base);
            xc = multiply(xc, n, base);
            yL = yc.length;
            xL = xc.length;
          }

          xi = yL;
          rem = xc.slice(0, yL);
          remL = rem.length;

          // Add zeros to make remainder as long as divisor.
          for (; remL < yL; rem[remL++] = 0);
          yz = yc.slice();
          yz = [0].concat(yz);
          yc0 = yc[0];
          if (yc[1] >= base / 2) yc0++;
          // Not necessary, but to prevent trial digit n > base, when using base 3.
          // else if (base == 3 && yc0 == 1) yc0 = 1 + 1e-15;

          do {
            n = 0;

            // Compare divisor and remainder.
            cmp = compare(yc, rem, yL, remL);

            // If divisor < remainder.
            if (cmp < 0) {

              // Calculate trial digit, n.

              rem0 = rem[0];
              if (yL != remL) rem0 = rem0 * base + (rem[1] || 0);

              // n is how many times the divisor goes into the current remainder.
              n = mathfloor(rem0 / yc0);

              //  Algorithm:
              //  product = divisor multiplied by trial digit (n).
              //  Compare product and remainder.
              //  If product is greater than remainder:
              //    Subtract divisor from product, decrement trial digit.
              //  Subtract product from remainder.
              //  If product was less than remainder at the last compare:
              //    Compare new remainder and divisor.
              //    If remainder is greater than divisor:
              //      Subtract divisor from remainder, increment trial digit.

              if (n > 1) {

                // n may be > base only when base is 3.
                if (n >= base) n = base - 1;

                // product = divisor * trial digit.
                prod = multiply(yc, n, base);
                prodL = prod.length;
                remL = rem.length;

                // Compare product and remainder.
                // If product > remainder then trial digit n too high.
                // n is 1 too high about 5% of the time, and is not known to have
                // ever been more than 1 too high.
                while (compare(prod, rem, prodL, remL) == 1) {
                  n--;

                  // Subtract divisor from product.
                  subtract(prod, yL < prodL ? yz : yc, prodL, base);
                  prodL = prod.length;
                  cmp = 1;
                }
              } else {

                // n is 0 or 1, cmp is -1.
                // If n is 0, there is no need to compare yc and rem again below,
                // so change cmp to 1 to avoid it.
                // If n is 1, leave cmp as -1, so yc and rem are compared again.
                if (n == 0) {

                  // divisor < remainder, so n must be at least 1.
                  cmp = n = 1;
                }

                // product = divisor
                prod = yc.slice();
                prodL = prod.length;
              }

              if (prodL < remL) prod = [0].concat(prod);

              // Subtract product from remainder.
              subtract(rem, prod, remL, base);
              remL = rem.length;

               // If product was < remainder.
              if (cmp == -1) {

                // Compare divisor and new remainder.
                // If divisor < new remainder, subtract divisor from remainder.
                // Trial digit n too low.
                // n is 1 too low about 5% of the time, and very rarely 2 too low.
                while (compare(yc, rem, yL, remL) < 1) {
                  n++;

                  // Subtract divisor from remainder.
                  subtract(rem, yL < remL ? yz : yc, remL, base);
                  remL = rem.length;
                }
              }
            } else if (cmp === 0) {
              n++;
              rem = [0];
            } // else cmp === 1 and n will be 0

            // Add the next digit, n, to the result array.
            qc[i++] = n;

            // Update the remainder.
            if (rem[0]) {
              rem[remL++] = xc[xi] || 0;
            } else {
              rem = [xc[xi]];
              remL = 1;
            }
          } while ((xi++ < xL || rem[0] != null) && s--);

          more = rem[0] != null;

          // Leading zero?
          if (!qc[0]) qc.splice(0, 1);
        }

        if (base == BASE) {

          // To calculate q.e, first get the number of digits of qc[0].
          for (i = 1, s = qc[0]; s >= 10; s /= 10, i++);

          round(q, dp + (q.e = i + e * LOG_BASE - 1) + 1, rm, more);

        // Caller is convertBase.
        } else {
          q.e = e;
          q.r = +more;
        }

        return q;
      };
    })();


    /*
     * Return a string representing the value of BigNumber n in fixed-point or exponential
     * notation rounded to the specified decimal places or significant digits.
     *
     * n: a BigNumber.
     * i: the index of the last digit required (i.e. the digit that may be rounded up).
     * rm: the rounding mode.
     * id: 1 (toExponential) or 2 (toPrecision).
     */
    function format(n, i, rm, id) {
      var c0, e, ne, len, str;

      if (rm == null) rm = ROUNDING_MODE;
      else intCheck(rm, 0, 8);

      if (!n.c) return n.toString();

      c0 = n.c[0];
      ne = n.e;

      if (i == null) {
        str = coeffToString(n.c);
        str = id == 1 || id == 2 && (ne <= TO_EXP_NEG || ne >= TO_EXP_POS)
         ? toExponential(str, ne)
         : toFixedPoint(str, ne, '0');
      } else {
        n = round(new BigNumber(n), i, rm);

        // n.e may have changed if the value was rounded up.
        e = n.e;

        str = coeffToString(n.c);
        len = str.length;

        // toPrecision returns exponential notation if the number of significant digits
        // specified is less than the number of digits necessary to represent the integer
        // part of the value in fixed-point notation.

        // Exponential notation.
        if (id == 1 || id == 2 && (i <= e || e <= TO_EXP_NEG)) {

          // Append zeros?
          for (; len < i; str += '0', len++);
          str = toExponential(str, e);

        // Fixed-point notation.
        } else {
          i -= ne + (id === 2 && e > ne);
          str = toFixedPoint(str, e, '0');

          // Append zeros?
          if (e + 1 > len) {
            if (--i > 0) for (str += '.'; i--; str += '0');
          } else {
            i += e - len;
            if (i > 0) {
              if (e + 1 == len) str += '.';
              for (; i--; str += '0');
            }
          }
        }
      }

      return n.s < 0 && c0 ? '-' + str : str;
    }


    // Handle BigNumber.max and BigNumber.min.
    // If any number is NaN, return NaN.
    function maxOrMin(args, n) {
      var k, y,
        i = 1,
        x = new BigNumber(args[0]);

      for (; i < args.length; i++) {
        y = new BigNumber(args[i]);
        if (!y.s || (k = compare(x, y)) === n || k === 0 && x.s === n) {
          x = y;
        }
      }

      return x;
    }


    /*
     * Strip trailing zeros, calculate base 10 exponent and check against MIN_EXP and MAX_EXP.
     * Called by minus, plus and times.
     */
    function normalise(n, c, e) {
      var i = 1,
        j = c.length;

       // Remove trailing zeros.
      for (; !c[--j]; c.pop());

      // Calculate the base 10 exponent. First get the number of digits of c[0].
      for (j = c[0]; j >= 10; j /= 10, i++);

      // Overflow?
      if ((e = i + e * LOG_BASE - 1) > MAX_EXP) {

        // Infinity.
        n.c = n.e = null;

      // Underflow?
      } else if (e < MIN_EXP) {

        // Zero.
        n.c = [n.e = 0];
      } else {
        n.e = e;
        n.c = c;
      }

      return n;
    }


    // Handle values that fail the validity test in BigNumber.
    parseNumeric = (function () {
      var basePrefix = /^(-?)0([xbo])(?=\w[\w.]*$)/i,
        dotAfter = /^([^.]+)\.$/,
        dotBefore = /^\.([^.]+)$/,
        isInfinityOrNaN = /^-?(Infinity|NaN)$/,
        whitespaceOrPlus = /^\s*\+(?=[\w.])|^\s+|\s+$/g;

      return function (x, str, isNum, b) {
        var base,
          s = isNum ? str : str.replace(whitespaceOrPlus, '');

        // No exception on ±Infinity or NaN.
        if (isInfinityOrNaN.test(s)) {
          x.s = isNaN(s) ? null : s < 0 ? -1 : 1;
        } else {
          if (!isNum) {

            // basePrefix = /^(-?)0([xbo])(?=\w[\w.]*$)/i
            s = s.replace(basePrefix, function (m, p1, p2) {
              base = (p2 = p2.toLowerCase()) == 'x' ? 16 : p2 == 'b' ? 2 : 8;
              return !b || b == base ? p1 : m;
            });

            if (b) {
              base = b;

              // E.g. '1.' to '1', '.1' to '0.1'
              s = s.replace(dotAfter, '$1').replace(dotBefore, '0.$1');
            }

            if (str != s) return new BigNumber(s, base);
          }

          // '[BigNumber Error] Not a number: {n}'
          // '[BigNumber Error] Not a base {b} number: {n}'
          if (BigNumber.DEBUG) {
            throw Error
              (bignumberError + 'Not a' + (b ? ' base ' + b : '') + ' number: ' + str);
          }

          // NaN
          x.s = null;
        }

        x.c = x.e = null;
      }
    })();


    /*
     * Round x to sd significant digits using rounding mode rm. Check for over/under-flow.
     * If r is truthy, it is known that there are more digits after the rounding digit.
     */
    function round(x, sd, rm, r) {
      var d, i, j, k, n, ni, rd,
        xc = x.c,
        pows10 = POWS_TEN;

      // if x is not Infinity or NaN...
      if (xc) {

        // rd is the rounding digit, i.e. the digit after the digit that may be rounded up.
        // n is a base 1e14 number, the value of the element of array x.c containing rd.
        // ni is the index of n within x.c.
        // d is the number of digits of n.
        // i is the index of rd within n including leading zeros.
        // j is the actual index of rd within n (if < 0, rd is a leading zero).
        out: {

          // Get the number of digits of the first element of xc.
          for (d = 1, k = xc[0]; k >= 10; k /= 10, d++);
          i = sd - d;

          // If the rounding digit is in the first element of xc...
          if (i < 0) {
            i += LOG_BASE;
            j = sd;
            n = xc[ni = 0];

            // Get the rounding digit at index j of n.
            rd = mathfloor(n / pows10[d - j - 1] % 10);
          } else {
            ni = mathceil((i + 1) / LOG_BASE);

            if (ni >= xc.length) {

              if (r) {

                // Needed by sqrt.
                for (; xc.length <= ni; xc.push(0));
                n = rd = 0;
                d = 1;
                i %= LOG_BASE;
                j = i - LOG_BASE + 1;
              } else {
                break out;
              }
            } else {
              n = k = xc[ni];

              // Get the number of digits of n.
              for (d = 1; k >= 10; k /= 10, d++);

              // Get the index of rd within n.
              i %= LOG_BASE;

              // Get the index of rd within n, adjusted for leading zeros.
              // The number of leading zeros of n is given by LOG_BASE - d.
              j = i - LOG_BASE + d;

              // Get the rounding digit at index j of n.
              rd = j < 0 ? 0 : mathfloor(n / pows10[d - j - 1] % 10);
            }
          }

          r = r || sd < 0 ||

          // Are there any non-zero digits after the rounding digit?
          // The expression  n % pows10[d - j - 1]  returns all digits of n to the right
          // of the digit at j, e.g. if n is 908714 and j is 2, the expression gives 714.
           xc[ni + 1] != null || (j < 0 ? n : n % pows10[d - j - 1]);

          r = rm < 4
           ? (rd || r) && (rm == 0 || rm == (x.s < 0 ? 3 : 2))
           : rd > 5 || rd == 5 && (rm == 4 || r || rm == 6 &&

            // Check whether the digit to the left of the rounding digit is odd.
            ((i > 0 ? j > 0 ? n / pows10[d - j] : 0 : xc[ni - 1]) % 10) & 1 ||
             rm == (x.s < 0 ? 8 : 7));

          if (sd < 1 || !xc[0]) {
            xc.length = 0;

            if (r) {

              // Convert sd to decimal places.
              sd -= x.e + 1;

              // 1, 0.1, 0.01, 0.001, 0.0001 etc.
              xc[0] = pows10[(LOG_BASE - sd % LOG_BASE) % LOG_BASE];
              x.e = -sd || 0;
            } else {

              // Zero.
              xc[0] = x.e = 0;
            }

            return x;
          }

          // Remove excess digits.
          if (i == 0) {
            xc.length = ni;
            k = 1;
            ni--;
          } else {
            xc.length = ni + 1;
            k = pows10[LOG_BASE - i];

            // E.g. 56700 becomes 56000 if 7 is the rounding digit.
            // j > 0 means i > number of leading zeros of n.
            xc[ni] = j > 0 ? mathfloor(n / pows10[d - j] % pows10[j]) * k : 0;
          }

          // Round up?
          if (r) {

            for (; ;) {

              // If the digit to be rounded up is in the first element of xc...
              if (ni == 0) {

                // i will be the length of xc[0] before k is added.
                for (i = 1, j = xc[0]; j >= 10; j /= 10, i++);
                j = xc[0] += k;
                for (k = 1; j >= 10; j /= 10, k++);

                // if i != k the length has increased.
                if (i != k) {
                  x.e++;
                  if (xc[0] == BASE) xc[0] = 1;
                }

                break;
              } else {
                xc[ni] += k;
                if (xc[ni] != BASE) break;
                xc[ni--] = 0;
                k = 1;
              }
            }
          }

          // Remove trailing zeros.
          for (i = xc.length; xc[--i] === 0; xc.pop());
        }

        // Overflow? Infinity.
        if (x.e > MAX_EXP) {
          x.c = x.e = null;

        // Underflow? Zero.
        } else if (x.e < MIN_EXP) {
          x.c = [x.e = 0];
        }
      }

      return x;
    }


    function valueOf(n) {
      var str,
        e = n.e;

      if (e === null) return n.toString();

      str = coeffToString(n.c);

      str = e <= TO_EXP_NEG || e >= TO_EXP_POS
        ? toExponential(str, e)
        : toFixedPoint(str, e, '0');

      return n.s < 0 ? '-' + str : str;
    }


    // PROTOTYPE/INSTANCE METHODS


    /*
     * Return a new BigNumber whose value is the absolute value of this BigNumber.
     */
    P.absoluteValue = P.abs = function () {
      var x = new BigNumber(this);
      if (x.s < 0) x.s = 1;
      return x;
    };


    /*
     * Return
     *   1 if the value of this BigNumber is greater than the value of BigNumber(y, b),
     *   -1 if the value of this BigNumber is less than the value of BigNumber(y, b),
     *   0 if they have the same value,
     *   or null if the value of either is NaN.
     */
    P.comparedTo = function (y, b) {
      return compare(this, new BigNumber(y, b));
    };


    /*
     * If dp is undefined or null or true or false, return the number of decimal places of the
     * value of this BigNumber, or null if the value of this BigNumber is ±Infinity or NaN.
     *
     * Otherwise, if dp is a number, return a new BigNumber whose value is the value of this
     * BigNumber rounded to a maximum of dp decimal places using rounding mode rm, or
     * ROUNDING_MODE if rm is omitted.
     *
     * [dp] {number} Decimal places: integer, 0 to MAX inclusive.
     * [rm] {number} Rounding mode. Integer, 0 to 8 inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {dp|rm}'
     */
    P.decimalPlaces = P.dp = function (dp, rm) {
      var c, n, v,
        x = this;

      if (dp != null) {
        intCheck(dp, 0, MAX);
        if (rm == null) rm = ROUNDING_MODE;
        else intCheck(rm, 0, 8);

        return round(new BigNumber(x), dp + x.e + 1, rm);
      }

      if (!(c = x.c)) return null;
      n = ((v = c.length - 1) - bitFloor(this.e / LOG_BASE)) * LOG_BASE;

      // Subtract the number of trailing zeros of the last number.
      if (v = c[v]) for (; v % 10 == 0; v /= 10, n--);
      if (n < 0) n = 0;

      return n;
    };


    /*
     *  n / 0 = I
     *  n / N = N
     *  n / I = 0
     *  0 / n = 0
     *  0 / 0 = N
     *  0 / N = N
     *  0 / I = 0
     *  N / n = N
     *  N / 0 = N
     *  N / N = N
     *  N / I = N
     *  I / n = I
     *  I / 0 = I
     *  I / N = N
     *  I / I = N
     *
     * Return a new BigNumber whose value is the value of this BigNumber divided by the value of
     * BigNumber(y, b), rounded according to DECIMAL_PLACES and ROUNDING_MODE.
     */
    P.dividedBy = P.div = function (y, b) {
      return div(this, new BigNumber(y, b), DECIMAL_PLACES, ROUNDING_MODE);
    };


    /*
     * Return a new BigNumber whose value is the integer part of dividing the value of this
     * BigNumber by the value of BigNumber(y, b).
     */
    P.dividedToIntegerBy = P.idiv = function (y, b) {
      return div(this, new BigNumber(y, b), 0, 1);
    };


    /*
     * Return a BigNumber whose value is the value of this BigNumber exponentiated by n.
     *
     * If m is present, return the result modulo m.
     * If n is negative round according to DECIMAL_PLACES and ROUNDING_MODE.
     * If POW_PRECISION is non-zero and m is not present, round to POW_PRECISION using ROUNDING_MODE.
     *
     * The modular power operation works efficiently when x, n, and m are integers, otherwise it
     * is equivalent to calculating x.exponentiatedBy(n).modulo(m) with a POW_PRECISION of 0.
     *
     * n {number|string|BigNumber} The exponent. An integer.
     * [m] {number|string|BigNumber} The modulus.
     *
     * '[BigNumber Error] Exponent not an integer: {n}'
     */
    P.exponentiatedBy = P.pow = function (n, m) {
      var half, isModExp, i, k, more, nIsBig, nIsNeg, nIsOdd, y,
        x = this;

      n = new BigNumber(n);

      // Allow NaN and ±Infinity, but not other non-integers.
      if (n.c && !n.isInteger()) {
        throw Error
          (bignumberError + 'Exponent not an integer: ' + valueOf(n));
      }

      if (m != null) m = new BigNumber(m);

      // Exponent of MAX_SAFE_INTEGER is 15.
      nIsBig = n.e > 14;

      // If x is NaN, ±Infinity, ±0 or ±1, or n is ±Infinity, NaN or ±0.
      if (!x.c || !x.c[0] || x.c[0] == 1 && !x.e && x.c.length == 1 || !n.c || !n.c[0]) {

        // The sign of the result of pow when x is negative depends on the evenness of n.
        // If +n overflows to ±Infinity, the evenness of n would be not be known.
        y = new BigNumber(Math.pow(+valueOf(x), nIsBig ? n.s * (2 - isOdd(n)) : +valueOf(n)));
        return m ? y.mod(m) : y;
      }

      nIsNeg = n.s < 0;

      if (m) {

        // x % m returns NaN if abs(m) is zero, or m is NaN.
        if (m.c ? !m.c[0] : !m.s) return new BigNumber(NaN);

        isModExp = !nIsNeg && x.isInteger() && m.isInteger();

        if (isModExp) x = x.mod(m);

      // Overflow to ±Infinity: >=2**1e10 or >=1.0000024**1e15.
      // Underflow to ±0: <=0.79**1e10 or <=0.9999975**1e15.
      } else if (n.e > 9 && (x.e > 0 || x.e < -1 || (x.e == 0
        // [1, 240000000]
        ? x.c[0] > 1 || nIsBig && x.c[1] >= 24e7
        // [80000000000000]  [99999750000000]
        : x.c[0] < 8e13 || nIsBig && x.c[0] <= 9999975e7))) {

        // If x is negative and n is odd, k = -0, else k = 0.
        k = x.s < 0 && isOdd(n) ? -0 : 0;

        // If x >= 1, k = ±Infinity.
        if (x.e > -1) k = 1 / k;

        // If n is negative return ±0, else return ±Infinity.
        return new BigNumber(nIsNeg ? 1 / k : k);

      } else if (POW_PRECISION) {

        // Truncating each coefficient array to a length of k after each multiplication
        // equates to truncating significant digits to POW_PRECISION + [28, 41],
        // i.e. there will be a minimum of 28 guard digits retained.
        k = mathceil(POW_PRECISION / LOG_BASE + 2);
      }

      if (nIsBig) {
        half = new BigNumber(0.5);
        if (nIsNeg) n.s = 1;
        nIsOdd = isOdd(n);
      } else {
        i = Math.abs(+valueOf(n));
        nIsOdd = i % 2;
      }

      y = new BigNumber(ONE);

      // Performs 54 loop iterations for n of 9007199254740991.
      for (; ;) {

        if (nIsOdd) {
          y = y.times(x);
          if (!y.c) break;

          if (k) {
            if (y.c.length > k) y.c.length = k;
          } else if (isModExp) {
            y = y.mod(m);    //y = y.minus(div(y, m, 0, MODULO_MODE).times(m));
          }
        }

        if (i) {
          i = mathfloor(i / 2);
          if (i === 0) break;
          nIsOdd = i % 2;
        } else {
          n = n.times(half);
          round(n, n.e + 1, 1);

          if (n.e > 14) {
            nIsOdd = isOdd(n);
          } else {
            i = +valueOf(n);
            if (i === 0) break;
            nIsOdd = i % 2;
          }
        }

        x = x.times(x);

        if (k) {
          if (x.c && x.c.length > k) x.c.length = k;
        } else if (isModExp) {
          x = x.mod(m);    //x = x.minus(div(x, m, 0, MODULO_MODE).times(m));
        }
      }

      if (isModExp) return y;
      if (nIsNeg) y = ONE.div(y);

      return m ? y.mod(m) : k ? round(y, POW_PRECISION, ROUNDING_MODE, more) : y;
    };


    /*
     * Return a new BigNumber whose value is the value of this BigNumber rounded to an integer
     * using rounding mode rm, or ROUNDING_MODE if rm is omitted.
     *
     * [rm] {number} Rounding mode. Integer, 0 to 8 inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {rm}'
     */
    P.integerValue = function (rm) {
      var n = new BigNumber(this);
      if (rm == null) rm = ROUNDING_MODE;
      else intCheck(rm, 0, 8);
      return round(n, n.e + 1, rm);
    };


    /*
     * Return true if the value of this BigNumber is equal to the value of BigNumber(y, b),
     * otherwise return false.
     */
    P.isEqualTo = P.eq = function (y, b) {
      return compare(this, new BigNumber(y, b)) === 0;
    };


    /*
     * Return true if the value of this BigNumber is a finite number, otherwise return false.
     */
    P.isFinite = function () {
      return !!this.c;
    };


    /*
     * Return true if the value of this BigNumber is greater than the value of BigNumber(y, b),
     * otherwise return false.
     */
    P.isGreaterThan = P.gt = function (y, b) {
      return compare(this, new BigNumber(y, b)) > 0;
    };


    /*
     * Return true if the value of this BigNumber is greater than or equal to the value of
     * BigNumber(y, b), otherwise return false.
     */
    P.isGreaterThanOrEqualTo = P.gte = function (y, b) {
      return (b = compare(this, new BigNumber(y, b))) === 1 || b === 0;

    };


    /*
     * Return true if the value of this BigNumber is an integer, otherwise return false.
     */
    P.isInteger = function () {
      return !!this.c && bitFloor(this.e / LOG_BASE) > this.c.length - 2;
    };


    /*
     * Return true if the value of this BigNumber is less than the value of BigNumber(y, b),
     * otherwise return false.
     */
    P.isLessThan = P.lt = function (y, b) {
      return compare(this, new BigNumber(y, b)) < 0;
    };


    /*
     * Return true if the value of this BigNumber is less than or equal to the value of
     * BigNumber(y, b), otherwise return false.
     */
    P.isLessThanOrEqualTo = P.lte = function (y, b) {
      return (b = compare(this, new BigNumber(y, b))) === -1 || b === 0;
    };


    /*
     * Return true if the value of this BigNumber is NaN, otherwise return false.
     */
    P.isNaN = function () {
      return !this.s;
    };


    /*
     * Return true if the value of this BigNumber is negative, otherwise return false.
     */
    P.isNegative = function () {
      return this.s < 0;
    };


    /*
     * Return true if the value of this BigNumber is positive, otherwise return false.
     */
    P.isPositive = function () {
      return this.s > 0;
    };


    /*
     * Return true if the value of this BigNumber is 0 or -0, otherwise return false.
     */
    P.isZero = function () {
      return !!this.c && this.c[0] == 0;
    };


    /*
     *  n - 0 = n
     *  n - N = N
     *  n - I = -I
     *  0 - n = -n
     *  0 - 0 = 0
     *  0 - N = N
     *  0 - I = -I
     *  N - n = N
     *  N - 0 = N
     *  N - N = N
     *  N - I = N
     *  I - n = I
     *  I - 0 = I
     *  I - N = N
     *  I - I = N
     *
     * Return a new BigNumber whose value is the value of this BigNumber minus the value of
     * BigNumber(y, b).
     */
    P.minus = function (y, b) {
      var i, j, t, xLTy,
        x = this,
        a = x.s;

      y = new BigNumber(y, b);
      b = y.s;

      // Either NaN?
      if (!a || !b) return new BigNumber(NaN);

      // Signs differ?
      if (a != b) {
        y.s = -b;
        return x.plus(y);
      }

      var xe = x.e / LOG_BASE,
        ye = y.e / LOG_BASE,
        xc = x.c,
        yc = y.c;

      if (!xe || !ye) {

        // Either Infinity?
        if (!xc || !yc) return xc ? (y.s = -b, y) : new BigNumber(yc ? x : NaN);

        // Either zero?
        if (!xc[0] || !yc[0]) {

          // Return y if y is non-zero, x if x is non-zero, or zero if both are zero.
          return yc[0] ? (y.s = -b, y) : new BigNumber(xc[0] ? x :

           // IEEE 754 (2008) 6.3: n - n = -0 when rounding to -Infinity
           ROUNDING_MODE == 3 ? -0 : 0);
        }
      }

      xe = bitFloor(xe);
      ye = bitFloor(ye);
      xc = xc.slice();

      // Determine which is the bigger number.
      if (a = xe - ye) {

        if (xLTy = a < 0) {
          a = -a;
          t = xc;
        } else {
          ye = xe;
          t = yc;
        }

        t.reverse();

        // Prepend zeros to equalise exponents.
        for (b = a; b--; t.push(0));
        t.reverse();
      } else {

        // Exponents equal. Check digit by digit.
        j = (xLTy = (a = xc.length) < (b = yc.length)) ? a : b;

        for (a = b = 0; b < j; b++) {

          if (xc[b] != yc[b]) {
            xLTy = xc[b] < yc[b];
            break;
          }
        }
      }

      // x < y? Point xc to the array of the bigger number.
      if (xLTy) {
        t = xc;
        xc = yc;
        yc = t;
        y.s = -y.s;
      }

      b = (j = yc.length) - (i = xc.length);

      // Append zeros to xc if shorter.
      // No need to add zeros to yc if shorter as subtract only needs to start at yc.length.
      if (b > 0) for (; b--; xc[i++] = 0);
      b = BASE - 1;

      // Subtract yc from xc.
      for (; j > a;) {

        if (xc[--j] < yc[j]) {
          for (i = j; i && !xc[--i]; xc[i] = b);
          --xc[i];
          xc[j] += BASE;
        }

        xc[j] -= yc[j];
      }

      // Remove leading zeros and adjust exponent accordingly.
      for (; xc[0] == 0; xc.splice(0, 1), --ye);

      // Zero?
      if (!xc[0]) {

        // Following IEEE 754 (2008) 6.3,
        // n - n = +0  but  n - n = -0  when rounding towards -Infinity.
        y.s = ROUNDING_MODE == 3 ? -1 : 1;
        y.c = [y.e = 0];
        return y;
      }

      // No need to check for Infinity as +x - +y != Infinity && -x - -y != Infinity
      // for finite x and y.
      return normalise(y, xc, ye);
    };


    /*
     *   n % 0 =  N
     *   n % N =  N
     *   n % I =  n
     *   0 % n =  0
     *  -0 % n = -0
     *   0 % 0 =  N
     *   0 % N =  N
     *   0 % I =  0
     *   N % n =  N
     *   N % 0 =  N
     *   N % N =  N
     *   N % I =  N
     *   I % n =  N
     *   I % 0 =  N
     *   I % N =  N
     *   I % I =  N
     *
     * Return a new BigNumber whose value is the value of this BigNumber modulo the value of
     * BigNumber(y, b). The result depends on the value of MODULO_MODE.
     */
    P.modulo = P.mod = function (y, b) {
      var q, s,
        x = this;

      y = new BigNumber(y, b);

      // Return NaN if x is Infinity or NaN, or y is NaN or zero.
      if (!x.c || !y.s || y.c && !y.c[0]) {
        return new BigNumber(NaN);

      // Return x if y is Infinity or x is zero.
      } else if (!y.c || x.c && !x.c[0]) {
        return new BigNumber(x);
      }

      if (MODULO_MODE == 9) {

        // Euclidian division: q = sign(y) * floor(x / abs(y))
        // r = x - qy    where  0 <= r < abs(y)
        s = y.s;
        y.s = 1;
        q = div(x, y, 0, 3);
        y.s = s;
        q.s *= s;
      } else {
        q = div(x, y, 0, MODULO_MODE);
      }

      y = x.minus(q.times(y));

      // To match JavaScript %, ensure sign of zero is sign of dividend.
      if (!y.c[0] && MODULO_MODE == 1) y.s = x.s;

      return y;
    };


    /*
     *  n * 0 = 0
     *  n * N = N
     *  n * I = I
     *  0 * n = 0
     *  0 * 0 = 0
     *  0 * N = N
     *  0 * I = N
     *  N * n = N
     *  N * 0 = N
     *  N * N = N
     *  N * I = N
     *  I * n = I
     *  I * 0 = N
     *  I * N = N
     *  I * I = I
     *
     * Return a new BigNumber whose value is the value of this BigNumber multiplied by the value
     * of BigNumber(y, b).
     */
    P.multipliedBy = P.times = function (y, b) {
      var c, e, i, j, k, m, xcL, xlo, xhi, ycL, ylo, yhi, zc,
        base, sqrtBase,
        x = this,
        xc = x.c,
        yc = (y = new BigNumber(y, b)).c;

      // Either NaN, ±Infinity or ±0?
      if (!xc || !yc || !xc[0] || !yc[0]) {

        // Return NaN if either is NaN, or one is 0 and the other is Infinity.
        if (!x.s || !y.s || xc && !xc[0] && !yc || yc && !yc[0] && !xc) {
          y.c = y.e = y.s = null;
        } else {
          y.s *= x.s;

          // Return ±Infinity if either is ±Infinity.
          if (!xc || !yc) {
            y.c = y.e = null;

          // Return ±0 if either is ±0.
          } else {
            y.c = [0];
            y.e = 0;
          }
        }

        return y;
      }

      e = bitFloor(x.e / LOG_BASE) + bitFloor(y.e / LOG_BASE);
      y.s *= x.s;
      xcL = xc.length;
      ycL = yc.length;

      // Ensure xc points to longer array and xcL to its length.
      if (xcL < ycL) {
        zc = xc;
        xc = yc;
        yc = zc;
        i = xcL;
        xcL = ycL;
        ycL = i;
      }

      // Initialise the result array with zeros.
      for (i = xcL + ycL, zc = []; i--; zc.push(0));

      base = BASE;
      sqrtBase = SQRT_BASE;

      for (i = ycL; --i >= 0;) {
        c = 0;
        ylo = yc[i] % sqrtBase;
        yhi = yc[i] / sqrtBase | 0;

        for (k = xcL, j = i + k; j > i;) {
          xlo = xc[--k] % sqrtBase;
          xhi = xc[k] / sqrtBase | 0;
          m = yhi * xlo + xhi * ylo;
          xlo = ylo * xlo + ((m % sqrtBase) * sqrtBase) + zc[j] + c;
          c = (xlo / base | 0) + (m / sqrtBase | 0) + yhi * xhi;
          zc[j--] = xlo % base;
        }

        zc[j] = c;
      }

      if (c) {
        ++e;
      } else {
        zc.splice(0, 1);
      }

      return normalise(y, zc, e);
    };


    /*
     * Return a new BigNumber whose value is the value of this BigNumber negated,
     * i.e. multiplied by -1.
     */
    P.negated = function () {
      var x = new BigNumber(this);
      x.s = -x.s || null;
      return x;
    };


    /*
     *  n + 0 = n
     *  n + N = N
     *  n + I = I
     *  0 + n = n
     *  0 + 0 = 0
     *  0 + N = N
     *  0 + I = I
     *  N + n = N
     *  N + 0 = N
     *  N + N = N
     *  N + I = N
     *  I + n = I
     *  I + 0 = I
     *  I + N = N
     *  I + I = I
     *
     * Return a new BigNumber whose value is the value of this BigNumber plus the value of
     * BigNumber(y, b).
     */
    P.plus = function (y, b) {
      var t,
        x = this,
        a = x.s;

      y = new BigNumber(y, b);
      b = y.s;

      // Either NaN?
      if (!a || !b) return new BigNumber(NaN);

      // Signs differ?
       if (a != b) {
        y.s = -b;
        return x.minus(y);
      }

      var xe = x.e / LOG_BASE,
        ye = y.e / LOG_BASE,
        xc = x.c,
        yc = y.c;

      if (!xe || !ye) {

        // Return ±Infinity if either ±Infinity.
        if (!xc || !yc) return new BigNumber(a / 0);

        // Either zero?
        // Return y if y is non-zero, x if x is non-zero, or zero if both are zero.
        if (!xc[0] || !yc[0]) return yc[0] ? y : new BigNumber(xc[0] ? x : a * 0);
      }

      xe = bitFloor(xe);
      ye = bitFloor(ye);
      xc = xc.slice();

      // Prepend zeros to equalise exponents. Faster to use reverse then do unshifts.
      if (a = xe - ye) {
        if (a > 0) {
          ye = xe;
          t = yc;
        } else {
          a = -a;
          t = xc;
        }

        t.reverse();
        for (; a--; t.push(0));
        t.reverse();
      }

      a = xc.length;
      b = yc.length;

      // Point xc to the longer array, and b to the shorter length.
      if (a - b < 0) {
        t = yc;
        yc = xc;
        xc = t;
        b = a;
      }

      // Only start adding at yc.length - 1 as the further digits of xc can be ignored.
      for (a = 0; b;) {
        a = (xc[--b] = xc[b] + yc[b] + a) / BASE | 0;
        xc[b] = BASE === xc[b] ? 0 : xc[b] % BASE;
      }

      if (a) {
        xc = [a].concat(xc);
        ++ye;
      }

      // No need to check for zero, as +x + +y != 0 && -x + -y != 0
      // ye = MAX_EXP + 1 possible
      return normalise(y, xc, ye);
    };


    /*
     * If sd is undefined or null or true or false, return the number of significant digits of
     * the value of this BigNumber, or null if the value of this BigNumber is ±Infinity or NaN.
     * If sd is true include integer-part trailing zeros in the count.
     *
     * Otherwise, if sd is a number, return a new BigNumber whose value is the value of this
     * BigNumber rounded to a maximum of sd significant digits using rounding mode rm, or
     * ROUNDING_MODE if rm is omitted.
     *
     * sd {number|boolean} number: significant digits: integer, 1 to MAX inclusive.
     *                     boolean: whether to count integer-part trailing zeros: true or false.
     * [rm] {number} Rounding mode. Integer, 0 to 8 inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {sd|rm}'
     */
    P.precision = P.sd = function (sd, rm) {
      var c, n, v,
        x = this;

      if (sd != null && sd !== !!sd) {
        intCheck(sd, 1, MAX);
        if (rm == null) rm = ROUNDING_MODE;
        else intCheck(rm, 0, 8);

        return round(new BigNumber(x), sd, rm);
      }

      if (!(c = x.c)) return null;
      v = c.length - 1;
      n = v * LOG_BASE + 1;

      if (v = c[v]) {

        // Subtract the number of trailing zeros of the last element.
        for (; v % 10 == 0; v /= 10, n--);

        // Add the number of digits of the first element.
        for (v = c[0]; v >= 10; v /= 10, n++);
      }

      if (sd && x.e + 1 > n) n = x.e + 1;

      return n;
    };


    /*
     * Return a new BigNumber whose value is the value of this BigNumber shifted by k places
     * (powers of 10). Shift to the right if n > 0, and to the left if n < 0.
     *
     * k {number} Integer, -MAX_SAFE_INTEGER to MAX_SAFE_INTEGER inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {k}'
     */
    P.shiftedBy = function (k) {
      intCheck(k, -MAX_SAFE_INTEGER, MAX_SAFE_INTEGER);
      return this.times('1e' + k);
    };


    /*
     *  sqrt(-n) =  N
     *  sqrt(N) =  N
     *  sqrt(-I) =  N
     *  sqrt(I) =  I
     *  sqrt(0) =  0
     *  sqrt(-0) = -0
     *
     * Return a new BigNumber whose value is the square root of the value of this BigNumber,
     * rounded according to DECIMAL_PLACES and ROUNDING_MODE.
     */
    P.squareRoot = P.sqrt = function () {
      var m, n, r, rep, t,
        x = this,
        c = x.c,
        s = x.s,
        e = x.e,
        dp = DECIMAL_PLACES + 4,
        half = new BigNumber('0.5');

      // Negative/NaN/Infinity/zero?
      if (s !== 1 || !c || !c[0]) {
        return new BigNumber(!s || s < 0 && (!c || c[0]) ? NaN : c ? x : 1 / 0);
      }

      // Initial estimate.
      s = Math.sqrt(+valueOf(x));

      // Math.sqrt underflow/overflow?
      // Pass x to Math.sqrt as integer, then adjust the exponent of the result.
      if (s == 0 || s == 1 / 0) {
        n = coeffToString(c);
        if ((n.length + e) % 2 == 0) n += '0';
        s = Math.sqrt(+n);
        e = bitFloor((e + 1) / 2) - (e < 0 || e % 2);

        if (s == 1 / 0) {
          n = '5e' + e;
        } else {
          n = s.toExponential();
          n = n.slice(0, n.indexOf('e') + 1) + e;
        }

        r = new BigNumber(n);
      } else {
        r = new BigNumber(s + '');
      }

      // Check for zero.
      // r could be zero if MIN_EXP is changed after the this value was created.
      // This would cause a division by zero (x/t) and hence Infinity below, which would cause
      // coeffToString to throw.
      if (r.c[0]) {
        e = r.e;
        s = e + dp;
        if (s < 3) s = 0;

        // Newton-Raphson iteration.
        for (; ;) {
          t = r;
          r = half.times(t.plus(div(x, t, dp, 1)));

          if (coeffToString(t.c).slice(0, s) === (n = coeffToString(r.c)).slice(0, s)) {

            // The exponent of r may here be one less than the final result exponent,
            // e.g 0.0009999 (e-4) --> 0.001 (e-3), so adjust s so the rounding digits
            // are indexed correctly.
            if (r.e < e) --s;
            n = n.slice(s - 3, s + 1);

            // The 4th rounding digit may be in error by -1 so if the 4 rounding digits
            // are 9999 or 4999 (i.e. approaching a rounding boundary) continue the
            // iteration.
            if (n == '9999' || !rep && n == '4999') {

              // On the first iteration only, check to see if rounding up gives the
              // exact result as the nines may infinitely repeat.
              if (!rep) {
                round(t, t.e + DECIMAL_PLACES + 2, 0);

                if (t.times(t).eq(x)) {
                  r = t;
                  break;
                }
              }

              dp += 4;
              s += 4;
              rep = 1;
            } else {

              // If rounding digits are null, 0{0,4} or 50{0,3}, check for exact
              // result. If not, then there are further digits and m will be truthy.
              if (!+n || !+n.slice(1) && n.charAt(0) == '5') {

                // Truncate to the first rounding digit.
                round(r, r.e + DECIMAL_PLACES + 2, 1);
                m = !r.times(r).eq(x);
              }

              break;
            }
          }
        }
      }

      return round(r, r.e + DECIMAL_PLACES + 1, ROUNDING_MODE, m);
    };


    /*
     * Return a string representing the value of this BigNumber in exponential notation and
     * rounded using ROUNDING_MODE to dp fixed decimal places.
     *
     * [dp] {number} Decimal places. Integer, 0 to MAX inclusive.
     * [rm] {number} Rounding mode. Integer, 0 to 8 inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {dp|rm}'
     */
    P.toExponential = function (dp, rm) {
      if (dp != null) {
        intCheck(dp, 0, MAX);
        dp++;
      }
      return format(this, dp, rm, 1);
    };


    /*
     * Return a string representing the value of this BigNumber in fixed-point notation rounding
     * to dp fixed decimal places using rounding mode rm, or ROUNDING_MODE if rm is omitted.
     *
     * Note: as with JavaScript's number type, (-0).toFixed(0) is '0',
     * but e.g. (-0.00001).toFixed(0) is '-0'.
     *
     * [dp] {number} Decimal places. Integer, 0 to MAX inclusive.
     * [rm] {number} Rounding mode. Integer, 0 to 8 inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {dp|rm}'
     */
    P.toFixed = function (dp, rm) {
      if (dp != null) {
        intCheck(dp, 0, MAX);
        dp = dp + this.e + 1;
      }
      return format(this, dp, rm);
    };


    /*
     * Return a string representing the value of this BigNumber in fixed-point notation rounded
     * using rm or ROUNDING_MODE to dp decimal places, and formatted according to the properties
     * of the format or FORMAT object (see BigNumber.set).
     *
     * The formatting object may contain some or all of the properties shown below.
     *
     * FORMAT = {
     *   prefix: '',
     *   groupSize: 3,
     *   secondaryGroupSize: 0,
     *   groupSeparator: ',',
     *   decimalSeparator: '.',
     *   fractionGroupSize: 0,
     *   fractionGroupSeparator: '\xA0',      // non-breaking space
     *   suffix: ''
     * };
     *
     * [dp] {number} Decimal places. Integer, 0 to MAX inclusive.
     * [rm] {number} Rounding mode. Integer, 0 to 8 inclusive.
     * [format] {object} Formatting options. See FORMAT pbject above.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {dp|rm}'
     * '[BigNumber Error] Argument not an object: {format}'
     */
    P.toFormat = function (dp, rm, format) {
      var str,
        x = this;

      if (format == null) {
        if (dp != null && rm && typeof rm == 'object') {
          format = rm;
          rm = null;
        } else if (dp && typeof dp == 'object') {
          format = dp;
          dp = rm = null;
        } else {
          format = FORMAT;
        }
      } else if (typeof format != 'object') {
        throw Error
          (bignumberError + 'Argument not an object: ' + format);
      }

      str = x.toFixed(dp, rm);

      if (x.c) {
        var i,
          arr = str.split('.'),
          g1 = +format.groupSize,
          g2 = +format.secondaryGroupSize,
          groupSeparator = format.groupSeparator || '',
          intPart = arr[0],
          fractionPart = arr[1],
          isNeg = x.s < 0,
          intDigits = isNeg ? intPart.slice(1) : intPart,
          len = intDigits.length;

        if (g2) {
          i = g1;
          g1 = g2;
          g2 = i;
          len -= i;
        }

        if (g1 > 0 && len > 0) {
          i = len % g1 || g1;
          intPart = intDigits.substr(0, i);
          for (; i < len; i += g1) intPart += groupSeparator + intDigits.substr(i, g1);
          if (g2 > 0) intPart += groupSeparator + intDigits.slice(i);
          if (isNeg) intPart = '-' + intPart;
        }

        str = fractionPart
         ? intPart + (format.decimalSeparator || '') + ((g2 = +format.fractionGroupSize)
          ? fractionPart.replace(new RegExp('\\d{' + g2 + '}\\B', 'g'),
           '$&' + (format.fractionGroupSeparator || ''))
          : fractionPart)
         : intPart;
      }

      return (format.prefix || '') + str + (format.suffix || '');
    };


    /*
     * Return an array of two BigNumbers representing the value of this BigNumber as a simple
     * fraction with an integer numerator and an integer denominator.
     * The denominator will be a positive non-zero value less than or equal to the specified
     * maximum denominator. If a maximum denominator is not specified, the denominator will be
     * the lowest value necessary to represent the number exactly.
     *
     * [md] {number|string|BigNumber} Integer >= 1, or Infinity. The maximum denominator.
     *
     * '[BigNumber Error] Argument {not an integer|out of range} : {md}'
     */
    P.toFraction = function (md) {
      var d, d0, d1, d2, e, exp, n, n0, n1, q, r, s,
        x = this,
        xc = x.c;

      if (md != null) {
        n = new BigNumber(md);

        // Throw if md is less than one or is not an integer, unless it is Infinity.
        if (!n.isInteger() && (n.c || n.s !== 1) || n.lt(ONE)) {
          throw Error
            (bignumberError + 'Argument ' +
              (n.isInteger() ? 'out of range: ' : 'not an integer: ') + valueOf(n));
        }
      }

      if (!xc) return new BigNumber(x);

      d = new BigNumber(ONE);
      n1 = d0 = new BigNumber(ONE);
      d1 = n0 = new BigNumber(ONE);
      s = coeffToString(xc);

      // Determine initial denominator.
      // d is a power of 10 and the minimum max denominator that specifies the value exactly.
      e = d.e = s.length - x.e - 1;
      d.c[0] = POWS_TEN[(exp = e % LOG_BASE) < 0 ? LOG_BASE + exp : exp];
      md = !md || n.comparedTo(d) > 0 ? (e > 0 ? d : n1) : n;

      exp = MAX_EXP;
      MAX_EXP = 1 / 0;
      n = new BigNumber(s);

      // n0 = d1 = 0
      n0.c[0] = 0;

      for (; ;)  {
        q = div(n, d, 0, 1);
        d2 = d0.plus(q.times(d1));
        if (d2.comparedTo(md) == 1) break;
        d0 = d1;
        d1 = d2;
        n1 = n0.plus(q.times(d2 = n1));
        n0 = d2;
        d = n.minus(q.times(d2 = d));
        n = d2;
      }

      d2 = div(md.minus(d0), d1, 0, 1);
      n0 = n0.plus(d2.times(n1));
      d0 = d0.plus(d2.times(d1));
      n0.s = n1.s = x.s;
      e = e * 2;

      // Determine which fraction is closer to x, n0/d0 or n1/d1
      r = div(n1, d1, e, ROUNDING_MODE).minus(x).abs().comparedTo(
          div(n0, d0, e, ROUNDING_MODE).minus(x).abs()) < 1 ? [n1, d1] : [n0, d0];

      MAX_EXP = exp;

      return r;
    };


    /*
     * Return the value of this BigNumber converted to a number primitive.
     */
    P.toNumber = function () {
      return +valueOf(this);
    };


    /*
     * Return a string representing the value of this BigNumber rounded to sd significant digits
     * using rounding mode rm or ROUNDING_MODE. If sd is less than the number of digits
     * necessary to represent the integer part of the value in fixed-point notation, then use
     * exponential notation.
     *
     * [sd] {number} Significant digits. Integer, 1 to MAX inclusive.
     * [rm] {number} Rounding mode. Integer, 0 to 8 inclusive.
     *
     * '[BigNumber Error] Argument {not a primitive number|not an integer|out of range}: {sd|rm}'
     */
    P.toPrecision = function (sd, rm) {
      if (sd != null) intCheck(sd, 1, MAX);
      return format(this, sd, rm, 2);
    };


    /*
     * Return a string representing the value of this BigNumber in base b, or base 10 if b is
     * omitted. If a base is specified, including base 10, round according to DECIMAL_PLACES and
     * ROUNDING_MODE. If a base is not specified, and this BigNumber has a positive exponent
     * that is equal to or greater than TO_EXP_POS, or a negative exponent equal to or less than
     * TO_EXP_NEG, return exponential notation.
     *
     * [b] {number} Integer, 2 to ALPHABET.length inclusive.
     *
     * '[BigNumber Error] Base {not a primitive number|not an integer|out of range}: {b}'
     */
    P.toString = function (b) {
      var str,
        n = this,
        s = n.s,
        e = n.e;

      // Infinity or NaN?
      if (e === null) {
        if (s) {
          str = 'Infinity';
          if (s < 0) str = '-' + str;
        } else {
          str = 'NaN';
        }
      } else {
        if (b == null) {
          str = e <= TO_EXP_NEG || e >= TO_EXP_POS
           ? toExponential(coeffToString(n.c), e)
           : toFixedPoint(coeffToString(n.c), e, '0');
        } else if (b === 10 && alphabetHasNormalDecimalDigits) {
          n = round(new BigNumber(n), DECIMAL_PLACES + e + 1, ROUNDING_MODE);
          str = toFixedPoint(coeffToString(n.c), n.e, '0');
        } else {
          intCheck(b, 2, ALPHABET.length, 'Base');
          str = convertBase(toFixedPoint(coeffToString(n.c), e, '0'), 10, b, s, true);
        }

        if (s < 0 && n.c[0]) str = '-' + str;
      }

      return str;
    };


    /*
     * Return as toString, but do not accept a base argument, and include the minus sign for
     * negative zero.
     */
    P.valueOf = P.toJSON = function () {
      return valueOf(this);
    };


    P._isBigNumber = true;

    if (configObject != null) BigNumber.set(configObject);

    return BigNumber;
  }


  // PRIVATE HELPER FUNCTIONS

  // These functions don't need access to variables,
  // e.g. DECIMAL_PLACES, in the scope of the `clone` function above.


  function bitFloor(n) {
    var i = n | 0;
    return n > 0 || n === i ? i : i - 1;
  }


  // Return a coefficient array as a string of base 10 digits.
  function coeffToString(a) {
    var s, z,
      i = 1,
      j = a.length,
      r = a[0] + '';

    for (; i < j;) {
      s = a[i++] + '';
      z = LOG_BASE - s.length;
      for (; z--; s = '0' + s);
      r += s;
    }

    // Determine trailing zeros.
    for (j = r.length; r.charCodeAt(--j) === 48;);

    return r.slice(0, j + 1 || 1);
  }


  // Compare the value of BigNumbers x and y.
  function compare(x, y) {
    var a, b,
      xc = x.c,
      yc = y.c,
      i = x.s,
      j = y.s,
      k = x.e,
      l = y.e;

    // Either NaN?
    if (!i || !j) return null;

    a = xc && !xc[0];
    b = yc && !yc[0];

    // Either zero?
    if (a || b) return a ? b ? 0 : -j : i;

    // Signs differ?
    if (i != j) return i;

    a = i < 0;
    b = k == l;

    // Either Infinity?
    if (!xc || !yc) return b ? 0 : !xc ^ a ? 1 : -1;

    // Compare exponents.
    if (!b) return k > l ^ a ? 1 : -1;

    j = (k = xc.length) < (l = yc.length) ? k : l;

    // Compare digit by digit.
    for (i = 0; i < j; i++) if (xc[i] != yc[i]) return xc[i] > yc[i] ^ a ? 1 : -1;

    // Compare lengths.
    return k == l ? 0 : k > l ^ a ? 1 : -1;
  }


  /*
   * Check that n is a primitive number, an integer, and in range, otherwise throw.
   */
  function intCheck(n, min, max, name) {
    if (n < min || n > max || n !== mathfloor(n)) {
      throw Error
       (bignumberError + (name || 'Argument') + (typeof n == 'number'
         ? n < min || n > max ? ' out of range: ' : ' not an integer: '
         : ' not a primitive number: ') + String(n));
    }
  }


  // Assumes finite n.
  function isOdd(n) {
    var k = n.c.length - 1;
    return bitFloor(n.e / LOG_BASE) == k && n.c[k] % 2 != 0;
  }


  function toExponential(str, e) {
    return (str.length > 1 ? str.charAt(0) + '.' + str.slice(1) : str) +
     (e < 0 ? 'e' : 'e+') + e;
  }


  function toFixedPoint(str, e, z) {
    var len, zs;

    // Negative exponent?
    if (e < 0) {

      // Prepend zeros.
      for (zs = z + '.'; ++e; zs += z);
      str = zs + str;

    // Positive exponent
    } else {
      len = str.length;

      // Append zeros.
      if (++e > len) {
        for (zs = z, e -= len; --e; zs += z);
        str += zs;
      } else if (e < len) {
        str = str.slice(0, e) + '.' + str.slice(e);
      }
    }

    return str;
  }


  // EXPORT


  BigNumber = clone();
  BigNumber['default'] = BigNumber.BigNumber = BigNumber;

  // AMD.
  if (typeof define == 'function' && define.amd) {
    define(function () { return BigNumber; });

  // Node.js and other environments that support module.exports.
  } else if (typeof module != 'undefined' && module.exports) {
    module.exports = BigNumber;

  // Browser.
  } else {
    if (!globalObject) {
      globalObject = typeof self != 'undefined' && self ? self : window;
    }

    globalObject.BigNumber = BigNumber;
  }
})(this);

/**
	@brief		Convert a text into a copy-pastable input.
	@since		2018-05-14 19:38:22
**/
;(function( $ )
{
    $.fn.extend(
    {
        mcc_make_clipboard : function()
        {
            return this.each( function()
            {
                var $item = $(this);

                if ( $item.hasClass( 'clipboarded' ) )
                	return;

				$item.addClass( 'clipboardable' );
				$item.addClass( 'clipboarded' );

				// How big should the input be?
				var text = $item.html();
				var length = text.length;
				// Create an input.
				var $input = $( '<input readonly="readonly">' );
				// Add a clipboard image to each input.
				$input.attr( 'size', length );
				$input.attr( 'value', text );

				// Make a clipboard input that hides above the clipboard.
				var $clipboard = $( '<span class="mcc_woocommerce_clipboard">' );

				$clipboard.click( function()
				{
					var old_value = $input.attr( 'value' );
					var new_value = old_value.replace( / .*/, '' );

					// Create an invisible input just to copy the value.
					var $temp_input = $( '<input value="' + new_value + '" />' );
					$temp_input.css( {
						'position' : 'absolute',
						'left' : '-1000000px',
						'top' : '-1000000px',
					} );
					$temp_input.appendTo( $item );
					$temp_input.attr( 'value', new_value );
					$temp_input.select();
					document.execCommand( "copy" );

					$input.attr( 'value', 'OK!' );
					setTimeout( function()
					{
						$input.attr( 'value', old_value );
						$input.select();
					}, 1500 );
				} );

				$item.html( $input );

				// Add the clipboard to the item that now contains the new input.
				$clipboard.appendTo( $item );

				// Adjust the size and position of the invisible clipboard div to match the input.
				var input_height = $input.outerHeight();
				$clipboard.css( {
					'height' : input_height,
					'width' : input_height,
					'top' : - ( $input.outerHeight() - $item.outerHeight() ) / 2,
				} );

				} ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );

/**
	@brief		Handle the new currency / wallet form.
	@since		2018-09-21 17:49:39
**/
;(function( $ )
{
    $.fn.extend(
    {
        mycryptocheckout_new_currency : function()
        {
            return this.each( function()
            {
                var $this = $(this);

                if ( $this.hasClass( 'mycryptocheckout_new_currency' ) )
                	return;
                $this.addClass( 'mycryptocheckout_new_currency' );

                // Find the currency selector.
                $this.$currency_id = $( '.currency_id', $this );

               	var $currencies = $( '.only_for_currency', $this );
               	$currencies.parentsUntil( 'tr' ).parent().hide();

                $this.$currency_id.change( function()
                {
                	// Hide all currencies.
                	$currencies.parentsUntil( 'tr' ).parent().hide();
                	// And show only the selected one.
                	var currency_id = $this.$currency_id.val();
                	var selector = '.only_for_currency.' + currency_id;
                	$( selector, $this ).parentsUntil( 'tr' ).parent().show();
                } )
                .change();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );

/**
	@brief		Convert the form fieldsets in a form2 table to ajaxy tabs.
	@since		2015-07-11 19:47:46
**/
;(function( $ )
{
    $.fn.extend(
    {
        plainview_form_auto_tabs : function()
        {
            return this.each( function()
            {
                var $this = $(this);

                if ( $this.hasClass( 'auto_tabbed' ) )
                	return;

                $this.addClass( 'auto_tabbed' );

				var $fieldsets = $( 'div.fieldset', $this );
				// At least two fieldsets for this to make sense.
				if ( $fieldsets.length < 2 )
					return;

				$this.prepend( '<div style="clear: both"></div>' );
				// Create the "tabs", which are normal Wordpress tabs.
				var $subsubsub = $( '<ul class="subsubsub">' )
					.prependTo( $this );

				$.each( $fieldsets, function( index, item )
				{
					var $item = $(item);
					var $h3 = $( 'h3.title', $item );
					var $a = $( '<a href="#">' ).html( $h3.html() );
					$h3.remove();
					var $li = $( '<li>' );
					$a.appendTo( $li );
					$li.appendTo( $subsubsub );

					// We add a separator if we are not the last li.
					if ( index < $fieldsets.length - 1 )
						$li.append( '<span class="sep">&emsp;|&emsp;</span>' );

					// When clicking on a tab, show it
					$a.click( function()
					{
						$( 'li a', $subsubsub ).removeClass( 'current' );
						$(this).addClass( 'current' );
						$fieldsets.hide();
						$item.show();
					} );

				} );

				$( 'li a', $subsubsub ).first().click();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );

/**
	@brief		Handle the new currency / wallet form.
	@since		2018-09-21 17:49:39
**/
;(function( $ )
{
    $.fn.extend(
    {
        mycryptocheckout_sort_wallets : function()
        {
            return this.each( function()
            {
                var $this = $(this);

                if ( $this.hasClass( 'sortable' ) )
                	return;
                $this.addClass( 'sortable' );

                $this.data( 'nonce', $this.parent().data( 'nonce' ) );

                // Make it sortable.
				$this.sortable( {
					'handle' : 'td:first',
					placeholder: "wallet_placeholder",
        			'update' : function( event, ui )
					{
						$this.fadeTo( 250, 0.25 );
						var wallets = [];
						// Find all of the rows.
						var $rows = $( 'tr', $this );
						$.each( $rows, function( index, row )
						{
							var $row = $( row );
							wallets[ index + 10 ] = $row.data( 'index' );
						} );

						var data = {
							'action' : 'mycryptocheckout_sort_wallets',
							'nonce' : $this.data( 'nonce' ),
							'wallets' : wallets,
						};

						// Now send the new order to the server.
						$.post( {
							'data' : data,
							'url' : ajaxurl,
							'success' : function()
							{
								$this.fadeTo( 250, 1 );
							},
						} );
					},
				} )
				;//.disableSelection();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );

jQuery( document ).ready( function( $ )
{

/*!
 * clipboard.js v2.0.11
 * https://clipboardjs.com/
 *
 * Licensed MIT © Zeno Rocha
 */
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["ClipboardJS"] = factory();
	else
		root["ClipboardJS"] = factory();
})(this, function() {
return /******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 686:
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";

// EXPORTS
__webpack_require__.d(__webpack_exports__, {
  "default": function() { return /* binding */ clipboard; }
});

// EXTERNAL MODULE: ./node_modules/tiny-emitter/index.js
var tiny_emitter = __webpack_require__(279);
var tiny_emitter_default = /*#__PURE__*/__webpack_require__.n(tiny_emitter);
// EXTERNAL MODULE: ./node_modules/good-listener/src/listen.js
var listen = __webpack_require__(370);
var listen_default = /*#__PURE__*/__webpack_require__.n(listen);
// EXTERNAL MODULE: ./node_modules/select/src/select.js
var src_select = __webpack_require__(817);
var select_default = /*#__PURE__*/__webpack_require__.n(src_select);
;// CONCATENATED MODULE: ./src/common/command.js
/**
 * Executes a given operation type.
 * @param {String} type
 * @return {Boolean}
 */
function command(type) {
  try {
    return document.execCommand(type);
  } catch (err) {
    return false;
  }
}
;// CONCATENATED MODULE: ./src/actions/cut.js


/**
 * Cut action wrapper.
 * @param {String|HTMLElement} target
 * @return {String}
 */

var ClipboardActionCut = function ClipboardActionCut(target) {
  var selectedText = select_default()(target);
  command('cut');
  return selectedText;
};

/* harmony default export */ var actions_cut = (ClipboardActionCut);
;// CONCATENATED MODULE: ./src/common/create-fake-element.js
/**
 * Creates a fake textarea element with a value.
 * @param {String} value
 * @return {HTMLElement}
 */
function createFakeElement(value) {
  var isRTL = document.documentElement.getAttribute('dir') === 'rtl';
  var fakeElement = document.createElement('textarea'); // Prevent zooming on iOS

  fakeElement.style.fontSize = '12pt'; // Reset box model

  fakeElement.style.border = '0';
  fakeElement.style.padding = '0';
  fakeElement.style.margin = '0'; // Move element out of screen horizontally

  fakeElement.style.position = 'absolute';
  fakeElement.style[isRTL ? 'right' : 'left'] = '-9999px'; // Move element to the same position vertically

  var yPosition = window.pageYOffset || document.documentElement.scrollTop;
  fakeElement.style.top = "".concat(yPosition, "px");
  fakeElement.setAttribute('readonly', '');
  fakeElement.value = value;
  return fakeElement;
}
;// CONCATENATED MODULE: ./src/actions/copy.js



/**
 * Create fake copy action wrapper using a fake element.
 * @param {String} target
 * @param {Object} options
 * @return {String}
 */

var fakeCopyAction = function fakeCopyAction(value, options) {
  var fakeElement = createFakeElement(value);
  options.container.appendChild(fakeElement);
  var selectedText = select_default()(fakeElement);
  command('copy');
  fakeElement.remove();
  return selectedText;
};
/**
 * Copy action wrapper.
 * @param {String|HTMLElement} target
 * @param {Object} options
 * @return {String}
 */


var ClipboardActionCopy = function ClipboardActionCopy(target) {
  var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
    container: document.body
  };
  var selectedText = '';

  if (typeof target === 'string') {
    selectedText = fakeCopyAction(target, options);
  } else if (target instanceof HTMLInputElement && !['text', 'search', 'url', 'tel', 'password'].includes(target === null || target === void 0 ? void 0 : target.type)) {
    // If input type doesn't support `setSelectionRange`. Simulate it. https://developer.mozilla.org/en-US/docs/Web/API/HTMLInputElement/setSelectionRange
    selectedText = fakeCopyAction(target.value, options);
  } else {
    selectedText = select_default()(target);
    command('copy');
  }

  return selectedText;
};

/* harmony default export */ var actions_copy = (ClipboardActionCopy);
;// CONCATENATED MODULE: ./src/actions/default.js
function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }



/**
 * Inner function which performs selection from either `text` or `target`
 * properties and then executes copy or cut operations.
 * @param {Object} options
 */

var ClipboardActionDefault = function ClipboardActionDefault() {
  var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  // Defines base properties passed from constructor.
  var _options$action = options.action,
      action = _options$action === void 0 ? 'copy' : _options$action,
      container = options.container,
      target = options.target,
      text = options.text; // Sets the `action` to be performed which can be either 'copy' or 'cut'.

  if (action !== 'copy' && action !== 'cut') {
    throw new Error('Invalid "action" value, use either "copy" or "cut"');
  } // Sets the `target` property using an element that will be have its content copied.


  if (target !== undefined) {
    if (target && _typeof(target) === 'object' && target.nodeType === 1) {
      if (action === 'copy' && target.hasAttribute('disabled')) {
        throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');
      }

      if (action === 'cut' && (target.hasAttribute('readonly') || target.hasAttribute('disabled'))) {
        throw new Error('Invalid "target" attribute. You can\'t cut text from elements with "readonly" or "disabled" attributes');
      }
    } else {
      throw new Error('Invalid "target" value, use a valid Element');
    }
  } // Define selection strategy based on `text` property.


  if (text) {
    return actions_copy(text, {
      container: container
    });
  } // Defines which selection strategy based on `target` property.


  if (target) {
    return action === 'cut' ? actions_cut(target) : actions_copy(target, {
      container: container
    });
  }
};

/* harmony default export */ var actions_default = (ClipboardActionDefault);
;// CONCATENATED MODULE: ./src/clipboard.js
function clipboard_typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { clipboard_typeof = function _typeof(obj) { return typeof obj; }; } else { clipboard_typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return clipboard_typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (clipboard_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Date.prototype.toString.call(Reflect.construct(Date, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }






/**
 * Helper function to retrieve attribute value.
 * @param {String} suffix
 * @param {Element} element
 */

function getAttributeValue(suffix, element) {
  var attribute = "data-clipboard-".concat(suffix);

  if (!element.hasAttribute(attribute)) {
    return;
  }

  return element.getAttribute(attribute);
}
/**
 * Base class which takes one or more elements, adds event listeners to them,
 * and instantiates a new `ClipboardAction` on each click.
 */


var Clipboard = /*#__PURE__*/function (_Emitter) {
  _inherits(Clipboard, _Emitter);

  var _super = _createSuper(Clipboard);

  /**
   * @param {String|HTMLElement|HTMLCollection|NodeList} trigger
   * @param {Object} options
   */
  function Clipboard(trigger, options) {
    var _this;

    _classCallCheck(this, Clipboard);

    _this = _super.call(this);

    _this.resolveOptions(options);

    _this.listenClick(trigger);

    return _this;
  }
  /**
   * Defines if attributes would be resolved using internal setter functions
   * or custom functions that were passed in the constructor.
   * @param {Object} options
   */


  _createClass(Clipboard, [{
    key: "resolveOptions",
    value: function resolveOptions() {
      var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
      this.action = typeof options.action === 'function' ? options.action : this.defaultAction;
      this.target = typeof options.target === 'function' ? options.target : this.defaultTarget;
      this.text = typeof options.text === 'function' ? options.text : this.defaultText;
      this.container = clipboard_typeof(options.container) === 'object' ? options.container : document.body;
    }
    /**
     * Adds a click event listener to the passed trigger.
     * @param {String|HTMLElement|HTMLCollection|NodeList} trigger
     */

  }, {
    key: "listenClick",
    value: function listenClick(trigger) {
      var _this2 = this;

      this.listener = listen_default()(trigger, 'click', function (e) {
        return _this2.onClick(e);
      });
    }
    /**
     * Defines a new `ClipboardAction` on each click event.
     * @param {Event} e
     */

  }, {
    key: "onClick",
    value: function onClick(e) {
      var trigger = e.delegateTarget || e.currentTarget;
      var action = this.action(trigger) || 'copy';
      var text = actions_default({
        action: action,
        container: this.container,
        target: this.target(trigger),
        text: this.text(trigger)
      }); // Fires an event based on the copy operation result.

      this.emit(text ? 'success' : 'error', {
        action: action,
        text: text,
        trigger: trigger,
        clearSelection: function clearSelection() {
          if (trigger) {
            trigger.focus();
          }

          window.getSelection().removeAllRanges();
        }
      });
    }
    /**
     * Default `action` lookup function.
     * @param {Element} trigger
     */

  }, {
    key: "defaultAction",
    value: function defaultAction(trigger) {
      return getAttributeValue('action', trigger);
    }
    /**
     * Default `target` lookup function.
     * @param {Element} trigger
     */

  }, {
    key: "defaultTarget",
    value: function defaultTarget(trigger) {
      var selector = getAttributeValue('target', trigger);

      if (selector) {
        return document.querySelector(selector);
      }
    }
    /**
     * Allow fire programmatically a copy action
     * @param {String|HTMLElement} target
     * @param {Object} options
     * @returns Text copied.
     */

  }, {
    key: "defaultText",

    /**
     * Default `text` lookup function.
     * @param {Element} trigger
     */
    value: function defaultText(trigger) {
      return getAttributeValue('text', trigger);
    }
    /**
     * Destroy lifecycle.
     */

  }, {
    key: "destroy",
    value: function destroy() {
      this.listener.destroy();
    }
  }], [{
    key: "copy",
    value: function copy(target) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {
        container: document.body
      };
      return actions_copy(target, options);
    }
    /**
     * Allow fire programmatically a cut action
     * @param {String|HTMLElement} target
     * @returns Text cutted.
     */

  }, {
    key: "cut",
    value: function cut(target) {
      return actions_cut(target);
    }
    /**
     * Returns the support of the given action, or all actions if no action is
     * given.
     * @param {String} [action]
     */

  }, {
    key: "isSupported",
    value: function isSupported() {
      var action = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : ['copy', 'cut'];
      var actions = typeof action === 'string' ? [action] : action;
      var support = !!document.queryCommandSupported;
      actions.forEach(function (action) {
        support = support && !!document.queryCommandSupported(action);
      });
      return support;
    }
  }]);

  return Clipboard;
}((tiny_emitter_default()));

/* harmony default export */ var clipboard = (Clipboard);

/***/ }),

/***/ 828:
/***/ (function(module) {

var DOCUMENT_NODE_TYPE = 9;

/**
 * A polyfill for Element.matches()
 */
if (typeof Element !== 'undefined' && !Element.prototype.matches) {
    var proto = Element.prototype;

    proto.matches = proto.matchesSelector ||
                    proto.mozMatchesSelector ||
                    proto.msMatchesSelector ||
                    proto.oMatchesSelector ||
                    proto.webkitMatchesSelector;
}

/**
 * Finds the closest parent that matches a selector.
 *
 * @param {Element} element
 * @param {String} selector
 * @return {Function}
 */
function closest (element, selector) {
    while (element && element.nodeType !== DOCUMENT_NODE_TYPE) {
        if (typeof element.matches === 'function' &&
            element.matches(selector)) {
          return element;
        }
        element = element.parentNode;
    }
}

module.exports = closest;


/***/ }),

/***/ 438:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var closest = __webpack_require__(828);

/**
 * Delegates event to a selector.
 *
 * @param {Element} element
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @param {Boolean} useCapture
 * @return {Object}
 */
function _delegate(element, selector, type, callback, useCapture) {
    var listenerFn = listener.apply(this, arguments);

    element.addEventListener(type, listenerFn, useCapture);

    return {
        destroy: function() {
            element.removeEventListener(type, listenerFn, useCapture);
        }
    }
}

/**
 * Delegates event to a selector.
 *
 * @param {Element|String|Array} [elements]
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @param {Boolean} useCapture
 * @return {Object}
 */
function delegate(elements, selector, type, callback, useCapture) {
    // Handle the regular Element usage
    if (typeof elements.addEventListener === 'function') {
        return _delegate.apply(null, arguments);
    }

    // Handle Element-less usage, it defaults to global delegation
    if (typeof type === 'function') {
        // Use `document` as the first parameter, then apply arguments
        // This is a short way to .unshift `arguments` without running into deoptimizations
        return _delegate.bind(null, document).apply(null, arguments);
    }

    // Handle Selector-based usage
    if (typeof elements === 'string') {
        elements = document.querySelectorAll(elements);
    }

    // Handle Array-like based usage
    return Array.prototype.map.call(elements, function (element) {
        return _delegate(element, selector, type, callback, useCapture);
    });
}

/**
 * Finds closest match and invokes callback.
 *
 * @param {Element} element
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @return {Function}
 */
function listener(element, selector, type, callback) {
    return function(e) {
        e.delegateTarget = closest(e.target, selector);

        if (e.delegateTarget) {
            callback.call(element, e);
        }
    }
}

module.exports = delegate;


/***/ }),

/***/ 879:
/***/ (function(__unused_webpack_module, exports) {

/**
 * Check if argument is a HTML element.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.node = function(value) {
    return value !== undefined
        && value instanceof HTMLElement
        && value.nodeType === 1;
};

/**
 * Check if argument is a list of HTML elements.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.nodeList = function(value) {
    var type = Object.prototype.toString.call(value);

    return value !== undefined
        && (type === '[object NodeList]' || type === '[object HTMLCollection]')
        && ('length' in value)
        && (value.length === 0 || exports.node(value[0]));
};

/**
 * Check if argument is a string.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.string = function(value) {
    return typeof value === 'string'
        || value instanceof String;
};

/**
 * Check if argument is a function.
 *
 * @param {Object} value
 * @return {Boolean}
 */
exports.fn = function(value) {
    var type = Object.prototype.toString.call(value);

    return type === '[object Function]';
};


/***/ }),

/***/ 370:
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

var is = __webpack_require__(879);
var delegate = __webpack_require__(438);

/**
 * Validates all params and calls the right
 * listener function based on its target type.
 *
 * @param {String|HTMLElement|HTMLCollection|NodeList} target
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listen(target, type, callback) {
    if (!target && !type && !callback) {
        throw new Error('Missing required arguments');
    }

    if (!is.string(type)) {
        throw new TypeError('Second argument must be a String');
    }

    if (!is.fn(callback)) {
        throw new TypeError('Third argument must be a Function');
    }

    if (is.node(target)) {
        return listenNode(target, type, callback);
    }
    else if (is.nodeList(target)) {
        return listenNodeList(target, type, callback);
    }
    else if (is.string(target)) {
        return listenSelector(target, type, callback);
    }
    else {
        throw new TypeError('First argument must be a String, HTMLElement, HTMLCollection, or NodeList');
    }
}

/**
 * Adds an event listener to a HTML element
 * and returns a remove listener function.
 *
 * @param {HTMLElement} node
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listenNode(node, type, callback) {
    node.addEventListener(type, callback);

    return {
        destroy: function() {
            node.removeEventListener(type, callback);
        }
    }
}

/**
 * Add an event listener to a list of HTML elements
 * and returns a remove listener function.
 *
 * @param {NodeList|HTMLCollection} nodeList
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listenNodeList(nodeList, type, callback) {
    Array.prototype.forEach.call(nodeList, function(node) {
        node.addEventListener(type, callback);
    });

    return {
        destroy: function() {
            Array.prototype.forEach.call(nodeList, function(node) {
                node.removeEventListener(type, callback);
            });
        }
    }
}

/**
 * Add an event listener to a selector
 * and returns a remove listener function.
 *
 * @param {String} selector
 * @param {String} type
 * @param {Function} callback
 * @return {Object}
 */
function listenSelector(selector, type, callback) {
    return delegate(document.body, selector, type, callback);
}

module.exports = listen;


/***/ }),

/***/ 817:
/***/ (function(module) {

function select(element) {
    var selectedText;

    if (element.nodeName === 'SELECT') {
        element.focus();

        selectedText = element.value;
    }
    else if (element.nodeName === 'INPUT' || element.nodeName === 'TEXTAREA') {
        var isReadOnly = element.hasAttribute('readonly');

        if (!isReadOnly) {
            element.setAttribute('readonly', '');
        }

        element.select();
        element.setSelectionRange(0, element.value.length);

        if (!isReadOnly) {
            element.removeAttribute('readonly');
        }

        selectedText = element.value;
    }
    else {
        if (element.hasAttribute('contenteditable')) {
            element.focus();
        }

        var selection = window.getSelection();
        var range = document.createRange();

        range.selectNodeContents(element);
        selection.removeAllRanges();
        selection.addRange(range);

        selectedText = selection.toString();
    }

    return selectedText;
}

module.exports = select;


/***/ }),

/***/ 279:
/***/ (function(module) {

function E () {
  // Keep this empty so it's easier to inherit from
  // (via https://github.com/lipsmack from https://github.com/scottcorgan/tiny-emitter/issues/3)
}

E.prototype = {
  on: function (name, callback, ctx) {
    var e = this.e || (this.e = {});

    (e[name] || (e[name] = [])).push({
      fn: callback,
      ctx: ctx
    });

    return this;
  },

  once: function (name, callback, ctx) {
    var self = this;
    function listener () {
      self.off(name, listener);
      callback.apply(ctx, arguments);
    };

    listener._ = callback
    return this.on(name, listener, ctx);
  },

  emit: function (name) {
    var data = [].slice.call(arguments, 1);
    var evtArr = ((this.e || (this.e = {}))[name] || []).slice();
    var i = 0;
    var len = evtArr.length;

    for (i; i < len; i++) {
      evtArr[i].fn.apply(evtArr[i].ctx, data);
    }

    return this;
  },

  off: function (name, callback) {
    var e = this.e || (this.e = {});
    var evts = e[name];
    var liveEvents = [];

    if (evts && callback) {
      for (var i = 0, len = evts.length; i < len; i++) {
        if (evts[i].fn !== callback && evts[i].fn._ !== callback)
          liveEvents.push(evts[i]);
      }
    }

    // Remove event from queue to prevent memory leak
    // Suggested by https://github.com/lazd
    // Ref: https://github.com/scottcorgan/tiny-emitter/commit/c6ebfaa9bc973b33d110a84a307742b7cf94c953#commitcomment-5024910

    (liveEvents.length)
      ? e[name] = liveEvents
      : delete e[name];

    return this;
  }
};

module.exports = E;
module.exports.TinyEmitter = E;


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		if(__webpack_module_cache__[moduleId]) {
/******/ 			return __webpack_module_cache__[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	!function() {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = function(module) {
/******/ 			var getter = module && module.__esModule ?
/******/ 				function() { return module['default']; } :
/******/ 				function() { return module; };
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	// module exports must be returned from runtime so entry inlining is disabled
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(686);
/******/ })()
.default;
});
var QRCode=function(t){"use strict";var r,e=function(){return"function"==typeof Promise&&Promise.prototype&&Promise.prototype.then},n=[0,26,44,70,100,134,172,196,242,292,346,404,466,532,581,655,733,815,901,991,1085,1156,1258,1364,1474,1588,1706,1828,1921,2051,2185,2323,2465,2611,2761,2876,3034,3196,3362,3532,3706],o=function(t){if(!t)throw new Error('"version" cannot be null or undefined');if(t<1||t>40)throw new Error('"version" should be in range from 1 to 40');return 4*t+17},a=function(t){return n[t]},i=function(t){for(var r=0;0!==t;)r++,t>>>=1;return r},u=function(t){if("function"!=typeof t)throw new Error('"toSJISFunc" is not a valid function.');r=t},s=function(){return void 0!==r},f=function(t){return r(t)};function h(t,r){return t(r={exports:{}},r.exports),r.exports}var c=h((function(t,r){r.L={bit:1},r.M={bit:0},r.Q={bit:3},r.H={bit:2},r.isValid=function(t){return t&&void 0!==t.bit&&t.bit>=0&&t.bit<4},r.from=function(t,e){if(r.isValid(t))return t;try{return function(t){if("string"!=typeof t)throw new Error("Param is not a string");switch(t.toLowerCase()){case"l":case"low":return r.L;case"m":case"medium":return r.M;case"q":case"quartile":return r.Q;case"h":case"high":return r.H;default:throw new Error("Unknown EC Level: "+t)}}(t)}catch(t){return e}}}));function g(){this.buffer=[],this.length=0}c.L,c.M,c.Q,c.H,c.isValid,g.prototype={get:function(t){var r=Math.floor(t/8);return 1==(this.buffer[r]>>>7-t%8&1)},put:function(t,r){for(var e=0;e<r;e++)this.putBit(1==(t>>>r-e-1&1))},getLengthInBits:function(){return this.length},putBit:function(t){var r=Math.floor(this.length/8);this.buffer.length<=r&&this.buffer.push(0),t&&(this.buffer[r]|=128>>>this.length%8),this.length++}};var d=g;function l(t){if(!t||t<1)throw new Error("BitMatrix size must be defined and greater than 0");this.size=t,this.data=new Uint8Array(t*t),this.reservedBit=new Uint8Array(t*t)}l.prototype.set=function(t,r,e,n){var o=t*this.size+r;this.data[o]=e,n&&(this.reservedBit[o]=!0)},l.prototype.get=function(t,r){return this.data[t*this.size+r]},l.prototype.xor=function(t,r,e){this.data[t*this.size+r]^=e},l.prototype.isReserved=function(t,r){return this.reservedBit[t*this.size+r]};var v=l,p=h((function(t,r){var e=o;r.getRowColCoords=function(t){if(1===t)return[];for(var r=Math.floor(t/7)+2,n=e(t),o=145===n?26:2*Math.ceil((n-13)/(2*r-2)),a=[n-7],i=1;i<r-1;i++)a[i]=a[i-1]-o;return a.push(6),a.reverse()},r.getPositions=function(t){for(var e=[],n=r.getRowColCoords(t),o=n.length,a=0;a<o;a++)for(var i=0;i<o;i++)0===a&&0===i||0===a&&i===o-1||a===o-1&&0===i||e.push([n[a],n[i]]);return e}}));p.getRowColCoords,p.getPositions;var w=o,m=function(t){var r=w(t);return[[0,0],[r-7,0],[0,r-7]]},E=h((function(t,r){r.Patterns={PATTERN000:0,PATTERN001:1,PATTERN010:2,PATTERN011:3,PATTERN100:4,PATTERN101:5,PATTERN110:6,PATTERN111:7};var e=3,n=3,o=40,a=10;function i(t,e,n){switch(t){case r.Patterns.PATTERN000:return(e+n)%2==0;case r.Patterns.PATTERN001:return e%2==0;case r.Patterns.PATTERN010:return n%3==0;case r.Patterns.PATTERN011:return(e+n)%3==0;case r.Patterns.PATTERN100:return(Math.floor(e/2)+Math.floor(n/3))%2==0;case r.Patterns.PATTERN101:return e*n%2+e*n%3==0;case r.Patterns.PATTERN110:return(e*n%2+e*n%3)%2==0;case r.Patterns.PATTERN111:return(e*n%3+(e+n)%2)%2==0;default:throw new Error("bad maskPattern:"+t)}}r.isValid=function(t){return null!=t&&""!==t&&!isNaN(t)&&t>=0&&t<=7},r.from=function(t){return r.isValid(t)?parseInt(t,10):void 0},r.getPenaltyN1=function(t){for(var r=t.size,n=0,o=0,a=0,i=null,u=null,s=0;s<r;s++){o=a=0,i=u=null;for(var f=0;f<r;f++){var h=t.get(s,f);h===i?o++:(o>=5&&(n+=e+(o-5)),i=h,o=1),(h=t.get(f,s))===u?a++:(a>=5&&(n+=e+(a-5)),u=h,a=1)}o>=5&&(n+=e+(o-5)),a>=5&&(n+=e+(a-5))}return n},r.getPenaltyN2=function(t){for(var r=t.size,e=0,o=0;o<r-1;o++)for(var a=0;a<r-1;a++){var i=t.get(o,a)+t.get(o,a+1)+t.get(o+1,a)+t.get(o+1,a+1);4!==i&&0!==i||e++}return e*n},r.getPenaltyN3=function(t){for(var r=t.size,e=0,n=0,a=0,i=0;i<r;i++){n=a=0;for(var u=0;u<r;u++)n=n<<1&2047|t.get(i,u),u>=10&&(1488===n||93===n)&&e++,a=a<<1&2047|t.get(u,i),u>=10&&(1488===a||93===a)&&e++}return e*o},r.getPenaltyN4=function(t){for(var r=0,e=t.data.length,n=0;n<e;n++)r+=t.data[n];return Math.abs(Math.ceil(100*r/e/5)-10)*a},r.applyMask=function(t,r){for(var e=r.size,n=0;n<e;n++)for(var o=0;o<e;o++)r.isReserved(o,n)||r.xor(o,n,i(t,o,n))},r.getBestMask=function(t,e){for(var n=Object.keys(r.Patterns).length,o=0,a=1/0,i=0;i<n;i++){e(i),r.applyMask(i,t);var u=r.getPenaltyN1(t)+r.getPenaltyN2(t)+r.getPenaltyN3(t)+r.getPenaltyN4(t);r.applyMask(i,t),u<a&&(a=u,o=i)}return o}}));E.Patterns,E.isValid,E.getPenaltyN1,E.getPenaltyN2,E.getPenaltyN3,E.getPenaltyN4,E.applyMask,E.getBestMask;var y=[1,1,1,1,1,1,1,1,1,1,2,2,1,2,2,4,1,2,4,4,2,4,4,4,2,4,6,5,2,4,6,6,2,5,8,8,4,5,8,8,4,5,8,11,4,8,10,11,4,9,12,16,4,9,16,16,6,10,12,18,6,10,17,16,6,11,16,19,6,13,18,21,7,14,21,25,8,16,20,25,8,17,23,25,9,17,23,34,9,18,25,30,10,20,27,32,12,21,29,35,12,23,34,37,12,25,34,40,13,26,35,42,14,28,38,45,15,29,40,48,16,31,43,51,17,33,45,54,18,35,48,57,19,37,51,60,19,38,53,63,20,40,56,66,21,43,59,70,22,45,62,74,24,47,65,77,25,49,68,81],A=[7,10,13,17,10,16,22,28,15,26,36,44,20,36,52,64,26,48,72,88,36,64,96,112,40,72,108,130,48,88,132,156,60,110,160,192,72,130,192,224,80,150,224,264,96,176,260,308,104,198,288,352,120,216,320,384,132,240,360,432,144,280,408,480,168,308,448,532,180,338,504,588,196,364,546,650,224,416,600,700,224,442,644,750,252,476,690,816,270,504,750,900,300,560,810,960,312,588,870,1050,336,644,952,1110,360,700,1020,1200,390,728,1050,1260,420,784,1140,1350,450,812,1200,1440,480,868,1290,1530,510,924,1350,1620,540,980,1440,1710,570,1036,1530,1800,570,1064,1590,1890,600,1120,1680,1980,630,1204,1770,2100,660,1260,1860,2220,720,1316,1950,2310,750,1372,2040,2430],I=function(t,r){switch(r){case c.L:return y[4*(t-1)+0];case c.M:return y[4*(t-1)+1];case c.Q:return y[4*(t-1)+2];case c.H:return y[4*(t-1)+3];default:return}},M=function(t,r){switch(r){case c.L:return A[4*(t-1)+0];case c.M:return A[4*(t-1)+1];case c.Q:return A[4*(t-1)+2];case c.H:return A[4*(t-1)+3];default:return}},N=new Uint8Array(512),B=new Uint8Array(256);!function(){for(var t=1,r=0;r<255;r++)N[r]=t,B[t]=r,256&(t<<=1)&&(t^=285);for(var e=255;e<512;e++)N[e]=N[e-255]}();var C=function(t){return N[t]},P=function(t,r){return 0===t||0===r?0:N[B[t]+B[r]]},R=h((function(t,r){r.mul=function(t,r){for(var e=new Uint8Array(t.length+r.length-1),n=0;n<t.length;n++)for(var o=0;o<r.length;o++)e[n+o]^=P(t[n],r[o]);return e},r.mod=function(t,r){for(var e=new Uint8Array(t);e.length-r.length>=0;){for(var n=e[0],o=0;o<r.length;o++)e[o]^=P(r[o],n);for(var a=0;a<e.length&&0===e[a];)a++;e=e.slice(a)}return e},r.generateECPolynomial=function(t){for(var e=new Uint8Array([1]),n=0;n<t;n++)e=r.mul(e,new Uint8Array([1,C(n)]));return e}}));function T(t){this.genPoly=void 0,this.degree=t,this.degree&&this.initialize(this.degree)}R.mul,R.mod,R.generateECPolynomial,T.prototype.initialize=function(t){this.degree=t,this.genPoly=R.generateECPolynomial(this.degree)},T.prototype.encode=function(t){if(!this.genPoly)throw new Error("Encoder not initialized");var r=new Uint8Array(t.length+this.degree);r.set(t);var e=R.mod(r,this.genPoly),n=this.degree-e.length;if(n>0){var o=new Uint8Array(this.degree);return o.set(e,n),o}return e};var L=T,b=function(t){return!isNaN(t)&&t>=1&&t<=40},U="(?:[u3000-u303F]|[u3040-u309F]|[u30A0-u30FF]|[uFF00-uFFEF]|[u4E00-u9FAF]|[u2605-u2606]|[u2190-u2195]|u203B|[u2010u2015u2018u2019u2025u2026u201Cu201Du2225u2260]|[u0391-u0451]|[u00A7u00A8u00B1u00B4u00D7u00F7])+",x="(?:(?![A-Z0-9 $%*+\\-./:]|"+(U=U.replace(/u/g,"\\u"))+")(?:.|[\r\n]))+",k=new RegExp(U,"g"),F=new RegExp("[^A-Z0-9 $%*+\\-./:]+","g"),S=new RegExp(x,"g"),D=new RegExp("[0-9]+","g"),Y=new RegExp("[A-Z $%*+\\-./:]+","g"),_=new RegExp("^"+U+"$"),z=new RegExp("^[0-9]+$"),H=new RegExp("^[A-Z0-9 $%*+\\-./:]+$"),J={KANJI:k,BYTE_KANJI:F,BYTE:S,NUMERIC:D,ALPHANUMERIC:Y,testKanji:function(t){return _.test(t)},testNumeric:function(t){return z.test(t)},testAlphanumeric:function(t){return H.test(t)}},K=h((function(t,r){r.NUMERIC={id:"Numeric",bit:1,ccBits:[10,12,14]},r.ALPHANUMERIC={id:"Alphanumeric",bit:2,ccBits:[9,11,13]},r.BYTE={id:"Byte",bit:4,ccBits:[8,16,16]},r.KANJI={id:"Kanji",bit:8,ccBits:[8,10,12]},r.MIXED={bit:-1},r.getCharCountIndicator=function(t,r){if(!t.ccBits)throw new Error("Invalid mode: "+t);if(!b(r))throw new Error("Invalid version: "+r);return r>=1&&r<10?t.ccBits[0]:r<27?t.ccBits[1]:t.ccBits[2]},r.getBestModeForData=function(t){return J.testNumeric(t)?r.NUMERIC:J.testAlphanumeric(t)?r.ALPHANUMERIC:J.testKanji(t)?r.KANJI:r.BYTE},r.toString=function(t){if(t&&t.id)return t.id;throw new Error("Invalid mode")},r.isValid=function(t){return t&&t.bit&&t.ccBits},r.from=function(t,e){if(r.isValid(t))return t;try{return function(t){if("string"!=typeof t)throw new Error("Param is not a string");switch(t.toLowerCase()){case"numeric":return r.NUMERIC;case"alphanumeric":return r.ALPHANUMERIC;case"kanji":return r.KANJI;case"byte":return r.BYTE;default:throw new Error("Unknown mode: "+t)}}(t)}catch(t){return e}}}));K.NUMERIC,K.ALPHANUMERIC,K.BYTE,K.KANJI,K.MIXED,K.getCharCountIndicator,K.getBestModeForData,K.isValid;var O=h((function(t,r){var e=i(7973);function n(t,r){return K.getCharCountIndicator(t,r)+4}function o(t,r){var e=0;return t.forEach((function(t){var o=n(t.mode,r);e+=o+t.getBitsLength()})),e}r.from=function(t,r){return b(t)?parseInt(t,10):r},r.getCapacity=function(t,r,e){if(!b(t))throw new Error("Invalid QR Code version");void 0===e&&(e=K.BYTE);var o=8*(a(t)-M(t,r));if(e===K.MIXED)return o;var i=o-n(e,t);switch(e){case K.NUMERIC:return Math.floor(i/10*3);case K.ALPHANUMERIC:return Math.floor(i/11*2);case K.KANJI:return Math.floor(i/13);case K.BYTE:default:return Math.floor(i/8)}},r.getBestVersionForData=function(t,e){var n,a=c.from(e,c.M);if(Array.isArray(t)){if(t.length>1)return function(t,e){for(var n=1;n<=40;n++){if(o(t,n)<=r.getCapacity(n,e,K.MIXED))return n}}(t,a);if(0===t.length)return 1;n=t[0]}else n=t;return function(t,e,n){for(var o=1;o<=40;o++)if(e<=r.getCapacity(o,n,t))return o}(n.mode,n.getLength(),a)},r.getEncodedBits=function(t){if(!b(t)||t<7)throw new Error("Invalid QR Code version");for(var r=t<<12;i(r)-e>=0;)r^=7973<<i(r)-e;return t<<12|r}}));O.getCapacity,O.getBestVersionForData,O.getEncodedBits;var Q=i(1335),V=function(t,r){for(var e=t.bit<<3|r,n=e<<10;i(n)-Q>=0;)n^=1335<<i(n)-Q;return 21522^(e<<10|n)};function q(t){this.mode=K.NUMERIC,this.data=t.toString()}q.getBitsLength=function(t){return 10*Math.floor(t/3)+(t%3?t%3*3+1:0)},q.prototype.getLength=function(){return this.data.length},q.prototype.getBitsLength=function(){return q.getBitsLength(this.data.length)},q.prototype.write=function(t){var r,e,n;for(r=0;r+3<=this.data.length;r+=3)e=this.data.substr(r,3),n=parseInt(e,10),t.put(n,10);var o=this.data.length-r;o>0&&(e=this.data.substr(r),n=parseInt(e,10),t.put(n,3*o+1))};var j=q,$=["0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z"," ","$","%","*","+","-",".","/",":"];function X(t){this.mode=K.ALPHANUMERIC,this.data=t}X.getBitsLength=function(t){return 11*Math.floor(t/2)+t%2*6},X.prototype.getLength=function(){return this.data.length},X.prototype.getBitsLength=function(){return X.getBitsLength(this.data.length)},X.prototype.write=function(t){var r;for(r=0;r+2<=this.data.length;r+=2){var e=45*$.indexOf(this.data[r]);e+=$.indexOf(this.data[r+1]),t.put(e,11)}this.data.length%2&&t.put($.indexOf(this.data[r]),6)};var Z=X;function W(t){this.mode=K.BYTE,"string"==typeof t&&(t=function(t){for(var r=[],e=t.length,n=0;n<e;n++){var o=t.charCodeAt(n);if(o>=55296&&o<=56319&&e>n+1){var a=t.charCodeAt(n+1);a>=56320&&a<=57343&&(o=1024*(o-55296)+a-56320+65536,n+=1)}o<128?r.push(o):o<2048?(r.push(o>>6|192),r.push(63&o|128)):o<55296||o>=57344&&o<65536?(r.push(o>>12|224),r.push(o>>6&63|128),r.push(63&o|128)):o>=65536&&o<=1114111?(r.push(o>>18|240),r.push(o>>12&63|128),r.push(o>>6&63|128),r.push(63&o|128)):r.push(239,191,189)}return new Uint8Array(r).buffer}(t)),this.data=new Uint8Array(t)}W.getBitsLength=function(t){return 8*t},W.prototype.getLength=function(){return this.data.length},W.prototype.getBitsLength=function(){return W.getBitsLength(this.data.length)},W.prototype.write=function(t){for(var r=0,e=this.data.length;r<e;r++)t.put(this.data[r],8)};var G=W;function tt(t){this.mode=K.KANJI,this.data=t}tt.getBitsLength=function(t){return 13*t},tt.prototype.getLength=function(){return this.data.length},tt.prototype.getBitsLength=function(){return tt.getBitsLength(this.data.length)},tt.prototype.write=function(t){var r;for(r=0;r<this.data.length;r++){var e=f(this.data[r]);if(e>=33088&&e<=40956)e-=33088;else{if(!(e>=57408&&e<=60351))throw new Error("Invalid SJIS character: "+this.data[r]+"\nMake sure your charset is UTF-8");e-=49472}e=192*(e>>>8&255)+(255&e),t.put(e,13)}};var rt=tt,et=h((function(t){var r={single_source_shortest_paths:function(t,e,n){var o={},a={};a[e]=0;var i,u,s,f,h,c,g,d=r.PriorityQueue.make();for(d.push(e,0);!d.empty();)for(s in u=(i=d.pop()).value,f=i.cost,h=t[u]||{})h.hasOwnProperty(s)&&(c=f+h[s],g=a[s],(void 0===a[s]||g>c)&&(a[s]=c,d.push(s,c),o[s]=u));if(void 0!==n&&void 0===a[n]){var l=["Could not find a path from ",e," to ",n,"."].join("");throw new Error(l)}return o},extract_shortest_path_from_predecessor_list:function(t,r){for(var e=[],n=r;n;)e.push(n),n=t[n];return e.reverse(),e},find_path:function(t,e,n){var o=r.single_source_shortest_paths(t,e,n);return r.extract_shortest_path_from_predecessor_list(o,n)},PriorityQueue:{make:function(t){var e,n=r.PriorityQueue,o={};for(e in t=t||{},n)n.hasOwnProperty(e)&&(o[e]=n[e]);return o.queue=[],o.sorter=t.sorter||n.default_sorter,o},default_sorter:function(t,r){return t.cost-r.cost},push:function(t,r){var e={value:t,cost:r};this.queue.push(e),this.queue.sort(this.sorter)},pop:function(){return this.queue.shift()},empty:function(){return 0===this.queue.length}}};t.exports=r})),nt=h((function(t,r){function e(t){return unescape(encodeURIComponent(t)).length}function n(t,r,e){for(var n,o=[];null!==(n=t.exec(e));)o.push({data:n[0],index:n.index,mode:r,length:n[0].length});return o}function o(t){var r,e,o=n(J.NUMERIC,K.NUMERIC,t),a=n(J.ALPHANUMERIC,K.ALPHANUMERIC,t);return s()?(r=n(J.BYTE,K.BYTE,t),e=n(J.KANJI,K.KANJI,t)):(r=n(J.BYTE_KANJI,K.BYTE,t),e=[]),o.concat(a,r,e).sort((function(t,r){return t.index-r.index})).map((function(t){return{data:t.data,mode:t.mode,length:t.length}}))}function a(t,r){switch(r){case K.NUMERIC:return j.getBitsLength(t);case K.ALPHANUMERIC:return Z.getBitsLength(t);case K.KANJI:return rt.getBitsLength(t);case K.BYTE:return G.getBitsLength(t)}}function i(t,r){var e,n=K.getBestModeForData(t);if((e=K.from(r,n))!==K.BYTE&&e.bit<n.bit)throw new Error('"'+t+'" cannot be encoded with mode '+K.toString(e)+".\n Suggested mode is: "+K.toString(n));switch(e!==K.KANJI||s()||(e=K.BYTE),e){case K.NUMERIC:return new j(t);case K.ALPHANUMERIC:return new Z(t);case K.KANJI:return new rt(t);case K.BYTE:return new G(t)}}r.fromArray=function(t){return t.reduce((function(t,r){return"string"==typeof r?t.push(i(r,null)):r.data&&t.push(i(r.data,r.mode)),t}),[])},r.fromString=function(t,n){for(var i=function(t,r){for(var e={},n={start:{}},o=["start"],i=0;i<t.length;i++){for(var u=t[i],s=[],f=0;f<u.length;f++){var h=u[f],c=""+i+f;s.push(c),e[c]={node:h,lastCount:0},n[c]={};for(var g=0;g<o.length;g++){var d=o[g];e[d]&&e[d].node.mode===h.mode?(n[d][c]=a(e[d].lastCount+h.length,h.mode)-a(e[d].lastCount,h.mode),e[d].lastCount+=h.length):(e[d]&&(e[d].lastCount=h.length),n[d][c]=a(h.length,h.mode)+4+K.getCharCountIndicator(h.mode,r))}}o=s}for(var l=0;l<o.length;l++)n[o[l]].end=0;return{map:n,table:e}}(function(t){for(var r=[],n=0;n<t.length;n++){var o=t[n];switch(o.mode){case K.NUMERIC:r.push([o,{data:o.data,mode:K.ALPHANUMERIC,length:o.length},{data:o.data,mode:K.BYTE,length:o.length}]);break;case K.ALPHANUMERIC:r.push([o,{data:o.data,mode:K.BYTE,length:o.length}]);break;case K.KANJI:r.push([o,{data:o.data,mode:K.BYTE,length:e(o.data)}]);break;case K.BYTE:r.push([{data:o.data,mode:K.BYTE,length:e(o.data)}])}}return r}(o(t)),n),u=et.find_path(i.map,"start","end"),s=[],f=1;f<u.length-1;f++)s.push(i.table[u[f]].node);return r.fromArray(function(t){return t.reduce((function(t,r){var e=t.length-1>=0?t[t.length-1]:null;return e&&e.mode===r.mode?(t[t.length-1].data+=r.data,t):(t.push(r),t)}),[])}(s))},r.rawSplit=function(t){return r.fromArray(o(t))}}));function ot(t,r,e){var n,o,a=t.size,i=V(r,e);for(n=0;n<15;n++)o=1==(i>>n&1),n<6?t.set(n,8,o,!0):n<8?t.set(n+1,8,o,!0):t.set(a-15+n,8,o,!0),n<8?t.set(8,a-n-1,o,!0):n<9?t.set(8,15-n-1+1,o,!0):t.set(8,15-n-1,o,!0);t.set(a-8,8,1,!0)}function at(t,r,e){var n=new d;e.forEach((function(r){n.put(r.mode.bit,4),n.put(r.getLength(),K.getCharCountIndicator(r.mode,t)),r.write(n)}));var o=8*(a(t)-M(t,r));for(n.getLengthInBits()+4<=o&&n.put(0,4);n.getLengthInBits()%8!=0;)n.putBit(0);for(var i=(o-n.getLengthInBits())/8,u=0;u<i;u++)n.put(u%2?17:236,8);return function(t,r,e){for(var n=a(r),o=M(r,e),i=n-o,u=I(r,e),s=u-n%u,f=Math.floor(n/u),h=Math.floor(i/u),c=h+1,g=f-h,d=new L(g),l=0,v=new Array(u),p=new Array(u),w=0,m=new Uint8Array(t.buffer),E=0;E<u;E++){var y=E<s?h:c;v[E]=m.slice(l,l+y),p[E]=d.encode(v[E]),l+=y,w=Math.max(w,y)}var A,N,B=new Uint8Array(n),C=0;for(A=0;A<w;A++)for(N=0;N<u;N++)A<v[N].length&&(B[C++]=v[N][A]);for(A=0;A<g;A++)for(N=0;N<u;N++)B[C++]=p[N][A];return B}(n,t,r)}function it(t,r,e,n){var a;if(Array.isArray(t))a=nt.fromArray(t);else{if("string"!=typeof t)throw new Error("Invalid data");var i=r;if(!i){var u=nt.rawSplit(t);i=O.getBestVersionForData(u,e)}a=nt.fromString(t,i||40)}var s=O.getBestVersionForData(a,e);if(!s)throw new Error("The amount of data is too big to be stored in a QR Code");if(r){if(r<s)throw new Error("\nThe chosen QR Code version cannot contain this amount of data.\nMinimum version required to store current data is: "+s+".\n")}else r=s;var f=at(r,e,a),h=o(r),c=new v(h);return function(t,r){for(var e=t.size,n=m(r),o=0;o<n.length;o++)for(var a=n[o][0],i=n[o][1],u=-1;u<=7;u++)if(!(a+u<=-1||e<=a+u))for(var s=-1;s<=7;s++)i+s<=-1||e<=i+s||(u>=0&&u<=6&&(0===s||6===s)||s>=0&&s<=6&&(0===u||6===u)||u>=2&&u<=4&&s>=2&&s<=4?t.set(a+u,i+s,!0,!0):t.set(a+u,i+s,!1,!0))}(c,r),function(t){for(var r=t.size,e=8;e<r-8;e++){var n=e%2==0;t.set(e,6,n,!0),t.set(6,e,n,!0)}}(c),function(t,r){for(var e=p.getPositions(r),n=0;n<e.length;n++)for(var o=e[n][0],a=e[n][1],i=-2;i<=2;i++)for(var u=-2;u<=2;u++)-2===i||2===i||-2===u||2===u||0===i&&0===u?t.set(o+i,a+u,!0,!0):t.set(o+i,a+u,!1,!0)}(c,r),ot(c,e,0),r>=7&&function(t,r){for(var e,n,o,a=t.size,i=O.getEncodedBits(r),u=0;u<18;u++)e=Math.floor(u/3),n=u%3+a-8-3,o=1==(i>>u&1),t.set(e,n,o,!0),t.set(n,e,o,!0)}(c,r),function(t,r){for(var e=t.size,n=-1,o=e-1,a=7,i=0,u=e-1;u>0;u-=2)for(6===u&&u--;;){for(var s=0;s<2;s++)if(!t.isReserved(o,u-s)){var f=!1;i<r.length&&(f=1==(r[i]>>>a&1)),t.set(o,u-s,f),-1===--a&&(i++,a=7)}if((o+=n)<0||e<=o){o-=n,n=-n;break}}}(c,f),isNaN(n)&&(n=E.getBestMask(c,ot.bind(null,c,e))),E.applyMask(n,c),ot(c,e,n),{modules:c,version:r,errorCorrectionLevel:e,maskPattern:n,segments:a}}nt.fromArray,nt.fromString,nt.rawSplit;var ut=function(t,r){if(void 0===t||""===t)throw new Error("No input text");var e,n,o=c.M;return void 0!==r&&(o=c.from(r.errorCorrectionLevel,c.M),e=O.from(r.version),n=E.from(r.maskPattern),r.toSJISFunc&&u(r.toSJISFunc)),it(t,e,o,n)},st=h((function(t,r){function e(t){if("number"==typeof t&&(t=t.toString()),"string"!=typeof t)throw new Error("Color should be defined as hex string");var r=t.slice().replace("#","").split("");if(r.length<3||5===r.length||r.length>8)throw new Error("Invalid hex color: "+t);3!==r.length&&4!==r.length||(r=Array.prototype.concat.apply([],r.map((function(t){return[t,t]})))),6===r.length&&r.push("F","F");var e=parseInt(r.join(""),16);return{r:e>>24&255,g:e>>16&255,b:e>>8&255,a:255&e,hex:"#"+r.slice(0,6).join("")}}r.getOptions=function(t){t||(t={}),t.color||(t.color={});var r=void 0===t.margin||null===t.margin||t.margin<0?4:t.margin,n=t.width&&t.width>=21?t.width:void 0,o=t.scale||4;return{width:n,scale:n?4:o,margin:r,color:{dark:e(t.color.dark||"#000000ff"),light:e(t.color.light||"#ffffffff")},type:t.type,rendererOpts:t.rendererOpts||{}}},r.getScale=function(t,r){return r.width&&r.width>=t+2*r.margin?r.width/(t+2*r.margin):r.scale},r.getImageWidth=function(t,e){var n=r.getScale(t,e);return Math.floor((t+2*e.margin)*n)},r.qrToImageData=function(t,e,n){for(var o=e.modules.size,a=e.modules.data,i=r.getScale(o,n),u=Math.floor((o+2*n.margin)*i),s=n.margin*i,f=[n.color.light,n.color.dark],h=0;h<u;h++)for(var c=0;c<u;c++){var g=4*(h*u+c),d=n.color.light;if(h>=s&&c>=s&&h<u-s&&c<u-s)d=f[a[Math.floor((h-s)/i)*o+Math.floor((c-s)/i)]?1:0];t[g++]=d.r,t[g++]=d.g,t[g++]=d.b,t[g]=d.a}}}));st.getOptions,st.getScale,st.getImageWidth,st.qrToImageData;var ft=h((function(t,r){r.render=function(t,r,e){var n=e,o=r;void 0!==n||r&&r.getContext||(n=r,r=void 0),r||(o=function(){try{return document.createElement("canvas")}catch(t){throw new Error("You need to specify a canvas element")}}()),n=st.getOptions(n);var a=st.getImageWidth(t.modules.size,n),i=o.getContext("2d"),u=i.createImageData(a,a);return st.qrToImageData(u.data,t,n),function(t,r,e){t.clearRect(0,0,r.width,r.height),r.style||(r.style={}),r.height=e,r.width=e,r.style.height=e+"px",r.style.width=e+"px"}(i,o,a),i.putImageData(u,0,0),o},r.renderToDataURL=function(t,e,n){var o=n;void 0!==o||e&&e.getContext||(o=e,e=void 0),o||(o={});var a=r.render(t,e,o),i=o.type||"image/png",u=o.rendererOpts||{};return a.toDataURL(i,u.quality)}}));function ht(t,r){var e=t.a/255,n=r+'="'+t.hex+'"';return e<1?n+" "+r+'-opacity="'+e.toFixed(2).slice(1)+'"':n}function ct(t,r,e){var n=t+r;return void 0!==e&&(n+=" "+e),n}ft.render,ft.renderToDataURL;var gt=function(t,r,e){var n=st.getOptions(r),o=t.modules.size,a=t.modules.data,i=o+2*n.margin,u=n.color.light.a?"<path "+ht(n.color.light,"fill")+' d="M0 0h'+i+"v"+i+'H0z"/>':"",s="<path "+ht(n.color.dark,"stroke")+' d="'+function(t,r,e){for(var n="",o=0,a=!1,i=0,u=0;u<t.length;u++){var s=Math.floor(u%r),f=Math.floor(u/r);s||a||(a=!0),t[u]?(i++,u>0&&s>0&&t[u-1]||(n+=a?ct("M",s+e,.5+f+e):ct("m",o,0),o=0,a=!1),s+1<r&&t[u+1]||(n+=ct("h",i),i=0)):o++}return n}(a,o,n.margin)+'"/>',f='viewBox="0 0 '+i+" "+i+'"',h='<svg xmlns="http://www.w3.org/2000/svg" '+(n.width?'width="'+n.width+'" height="'+n.width+'" ':"")+f+' shape-rendering="crispEdges">'+u+s+"</svg>\n";return"function"==typeof e&&e(null,h),h};function dt(t,r,n,o,a){var i=[].slice.call(arguments,1),u=i.length,s="function"==typeof i[u-1];if(!s&&!e())throw new Error("Callback required as last argument");if(!s){if(u<1)throw new Error("Too few arguments provided");return 1===u?(n=r,r=o=void 0):2!==u||r.getContext||(o=n,n=r,r=void 0),new Promise((function(e,a){try{var i=ut(n,o);e(t(i,r,o))}catch(t){a(t)}}))}if(u<2)throw new Error("Too few arguments provided");2===u?(a=n,n=r,r=o=void 0):3===u&&(r.getContext&&void 0===a?(a=o,o=void 0):(a=o,o=n,n=r,r=void 0));try{var f=ut(n,o);a(null,t(f,r,o))}catch(t){a(t)}}var lt=ut,vt=dt.bind(null,ft.render),pt=dt.bind(null,ft.renderToDataURL),wt=dt.bind(null,(function(t,r,e){return gt(t,e)})),mt={create:lt,toCanvas:vt,toDataURL:pt,toString:wt};return t.create=lt,t.default=mt,t.toCanvas=vt,t.toDataURL=pt,t.toString=wt,Object.defineProperty(t,"__esModule",{value:!0}),t}({});

// 50.checkout
var mycryptocheckout_checkout_javascript = function( data )
{
	var $$ = this;
	$$.data = data;
	$$.$div = $( '.mcc.online_payment_instructions' );
	$$.$online_pay_box = $( '.mcc_online_pay_box', $$.$div );
	$$.$payment_buttons = $( '<div class="payment_buttons">' );
	$$.mycryptocheckout_checkout_data = false;

	/**
		@brief		Show the browser link button.
		@since		2018-12-14 22:59:03
	**/
	$$.show_browser_link = true;

	/**
		@brief		Check to see whether the order was paid, and cleanup in that case.
		@since		2018-05-02 21:02:30
	**/
	$$.check_for_payment = function()
	{
		var url = document.location;

		$.ajax( {
			'type' : 'get',
			'url' : url,
		} )
		.done( function( page )
		{
			var $page = $( page );
			var $mycryptocheckout_checkout_data = $( '#mycryptocheckout_checkout_data', $page );
			if ( $mycryptocheckout_checkout_data.length < 1 )
			{
				// Something went wrong.
				document.location = url;
				return;
			}

			var mycryptocheckout_checkout_data = $$.extract_data( $mycryptocheckout_checkout_data );
			if ( mycryptocheckout_checkout_data[ 'paid' ] === undefined )
				return;

			if ( mycryptocheckout_checkout_data[ 'paid' ] === false )
			{
				document.location = url;
				return;
			}

			// Stop the countdown and show the paid div.
			clearInterval( $$.payment_timer.timeout_interval );
			$( '.paid', $$.payment_timer ).show();
			$( '.timer', $$.payment_timer ).hide();
		} );
	}

	/**
		@brief		Extract and convert the checkout data into a json object.
		@since		2018-08-27 20:54:33
	**/
	$$.extract_data = function( $div )
	{
		var data = $div.data( 'mycryptocheckout_checkout_data' );
		data = atob( data );
		data = jQuery.parseJSON( data );
		return data;
	}

	/**
		@brief		Generate a eip681 wallet link.
		@since		2022-06-29 20:27:32
	**/
	$$.generate_eip681 = function()
	{
		if ( typeof $$.mycryptocheckout_checkout_data.supports.eip681 === 'undefined' ) {
			return '';
		}
		var r = $$.mycryptocheckout_checkout_data.supports.eip681.address;
		var amount = $$.mycryptocheckout_checkout_data.amount;
	
		// Decimals
		var decimals = $$.mycryptocheckout_checkout_data.supports.metamask_mobile_decimals || 18;
		var decimalFactor = new BigNumber(10).pow(decimals);
	
		// Convert amount to the smallest unit based on decimals
		var amountInSmallestUnit = new BigNumber(amount).multipliedBy(decimalFactor);
	
		// Format the amount using exponential notation correctly
		var formattedNumber = amountInSmallestUnit.toExponential().replace('+', '').replace('e0', '');
	
		if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id !== 'undefined' && typeof $$.mycryptocheckout_checkout_data.currency.contract === 'undefined') {
			// If metamask_id is defined
			r = r.replace('[MCC_TO]', $$.mycryptocheckout_checkout_data.to + '@' + $$.mycryptocheckout_checkout_data.supports.metamask_id);
		} else {
			r = r.replace( '[MCC_TO]', $$.mycryptocheckout_checkout_data.to );
		}
	
		r = r.replace( '[MCC_AMOUNT]', formattedNumber );
	
		if ( typeof $$.mycryptocheckout_checkout_data.currency.contract !== 'undefined' ) {
			r = r.replace('[MCC_CONTRACT]', $$.mycryptocheckout_checkout_data.currency.contract);
		}
		return r;
	}

	$$.init = function()
	{
		if ( $$.$div.length < 1 )
			return;
		$$.$div.addClass( 'mycryptocheckout' );
		$$.mycryptocheckout_checkout_data = $$.extract_data( $( '#mycryptocheckout_checkout_data' ) );
		console.debug( 'MyCryptoCheckout: Checkout data', $$.mycryptocheckout_checkout_data );
		$$.maybe_ens_address();
		$$.clipboard_inputs();
		$$.maybe_hide_woocommerce_order_overview();
		$$.maybe_upgrade_divs();
		$$.maybe_generate_qr_code();
		$$.maybe_generate_payment_timer();
		$$.$payment_buttons.appendTo( $$.$online_pay_box );
		$$.maybe_metamask();
		$$.maybe_metamask_mobile_link();
		$$.maybe_waves_link();
		$$.maybe_browser_link();
		$$.maybe_trustwallet_link();
	}

	/**
		@brief		Convert the text inputs to nice, clickable clipboard input things.
		@since		2018-04-25 16:13:10
	**/
	$$.clipboard_inputs = function()
	{
		// On the purchase confirmation page, convert the amount and address to a copyable input.
		$( '.to_input', $$.$div ).mcc_make_clipboard();
	}

	/**
		@brief		Add a payment link for the browser.
		@since		2018-12-09 12:08:06
	**/
	$$.maybe_browser_link = function()
	{
		if( typeof $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet != 'undefined' )
			$$.show_browser_link = $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet;
		if ( ! $$.show_browser_link )
			return;
		// Extract the currency name from the qr code, if possible.
		var currency_name = $$.mycryptocheckout_checkout_data.currency_id;
		if ( $$.data.qr_codes !== undefined )
			if ( $$.data.qr_codes[ $$.data.currency_id ] !== undefined )
				currency_name = $$.data.qr_codes[ $$.data.currency_id ].replace( /:.*/, '' );
		if( typeof $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet_url != 'undefined' )
			var html = $$.mycryptocheckout_checkout_data.supports.wp_plugin_open_in_wallet_url;
		else
		{
			var open_in_wallet_url = $$.generate_eip681();
			if ( open_in_wallet_url == '' )
				open_in_wallet_url = 'MCC_CURRENCY:MCC_TO?amount=MCC_AMOUNT';
			var html = '<a href="' + open_in_wallet_url + '"><div class="open_wallet_payment" role="img" aria-label="Open in wallet"></div></a>';
		}
		html = $$.replace_keywords( html );
		html = html.replace( 'MCC_CURRENCY', currency_name );
		var $div = $( '<div>' );
		$div.html( html );
		$div.appendTo( $$.$payment_buttons );
	}

	/**
		@brief		Add the alternate ENS address if it exists.
		@since		2020-01-05 22:52:27
	**/
	$$.maybe_ens_address = function()
	{
		if ( $$.data.ens_address === undefined )
			return;

		// Create a new To, which is the same as the old.
		var $p = $( 'p', $$.$div ).first();
		var $to = $( '.to', $p );
		$p.append( '<br>' );
		$p.append( 'To ' );
		$to.clone().appendTo( $p );

		// Change the first to ens.
		$( '.to', $p ).first()
			.removeClass( 'to' )
			.addClass( 'ens_address' );

		// And put the ENS address in the span.
		$( '.ens_address .to_input' ).html( $$.data.ens_address );
	}

	/**
		@brief		Generate the QR code on checkout.
		@since		2018-04-25 16:11:05
	**/
	$$.maybe_generate_qr_code = function()
	{
		var $qr_code = $( '.mcc_qr_code', $$.$div );

		if ( $$.data.qr_code_html === undefined )
			return $qr_code.remove();		// Kill any existing qr code.

		var $html = $( $$.data.qr_code_html );

		// If it does not exist, add it.
		if ( $qr_code.length < 1 )
		{
			// Add the HTML.
			$qr_code = $html;
			$qr_code.appendTo( $$.$online_pay_box );
		}
		else
		{
			// If it does exist, replace it.
			$qr_code.html( $html.html() );
		}

		var qr_code_text = $$.generate_eip681();
		if ( qr_code_text == '' )
		{
			qr_code_text = $$.data.to;

			if ( $$.data.qr_codes !== undefined )
			{
				if ( $$.data.qr_codes[ $$.data.currency_id ] !== undefined )
				{
					qr_code_text = $$.data.qr_codes[ $$.data.currency_id ];
				}
			}
		}

		// Replace the values.
		qr_code_text = qr_code_text
			.replace( '[MCC_TO]', $$.data.to )
			.replace( '[MCC_AMOUNT]', $$.data.amount )
			;

		console.debug( 'Generating QR code', qr_code_text );
		QRCode.toDataURL( qr_code_text )
			.then( url =>
				{
					var $img = $( '<img>' )
						.prop( 'data-src', url )
						.prop( 'src', url )
						.prop( 'title', qr_code_text );
					$img.appendTo( $qr_code );
				})
		  .catch( err =>
		  {
			console.error( 'Error generating QR code', err );
		  });
	}

	/**
		@brief		Generate the payment timer.
		@since		2018-05-01 22:18:19
	**/
	$$.maybe_generate_payment_timer = function()
	{
		$$.payment_timer = $( $$.data.payment_timer_html );
		if ( $$.payment_timer === undefined )
			return;
		$$.payment_timer.appendTo( $$.$online_pay_box );

		var timeout = $$.data.timeout_hours * 60 * 60;
		$$.payment_timer.timeout_time = parseInt( $$.data.created_at ) + timeout;

		$$.payment_timer.$hours_minutes = $( '.hours_minutes', $$.payment_timer );

		// Fetch the page once a minute to see if it has been paid.
		$$.payment_timer.status_interval = setInterval( function()
		{
			$$.check_for_payment();
		}, 1000 * 15 );
		$$.check_for_payment();

		// Update the timer every second.
		$$.payment_timer.timeout_interval = setInterval( function()
		{
			$$.update_payment_timer();
		}, 1000 );
		$$.update_payment_timer();
	}

	/**
		@brief		Maybe hide the WC order overview in order to get the payment details higher.
		@since		2018-04-25 16:10:44
	**/
	$$.maybe_hide_woocommerce_order_overview = function()
	{
		if ( $$.data.hide_woocommerce_order_overview === undefined )
			return;
		$( '.woocommerce-order-overview' ).hide();
	}

	/**
			@brief          Maybe generate a metamask payment link.
			@since          2018-08-27 20:42:19
	**/
	$$.maybe_metamask = async function() {
		if ($$.$online_pay_box.length < 1)
			return;
	
		// web3 must be supported and metamask enabled.
		if (typeof window.ethereum === 'undefined' || !ethereum.isMetaMask)
			return;
	
		// The data must support metamask.
		if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id === 'undefined')
			return;
	
		$$.show_browser_link = false;
	
		$$.$metamask = $('<div class="metamask_payment" role="img" aria-label="metamask wallet"></div>');
		$$.$metamask.appendTo($$.$payment_buttons);
	
		try {
			let providers = await Web3.requestEIP6963Providers();
			let metamaskProvider = null;
			for (const [key, value] of providers) {
				console.log(value);
	
				// List of providers and get selected provider's UUID EIP6963ProviderDetail.provider
				if (value.info.name === 'MetaMask') {
					   window.web3 = new Web3(value.provider);
					metamaskProvider = value.provider;
	
					break;
				}
			}
	
			if (metamaskProvider === null) {
				console.error('MetaMask is not available.');
				return;
			}
	
			var contractInstance = false;
			if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_abi !== 'undefined') {
				contractInstance = new web3.eth.Contract(JSON.parse($$.mycryptocheckout_checkout_data.supports.metamask_abi), $$.mycryptocheckout_checkout_data.currency.contract);
			}
	
			if (contractInstance === false && typeof $$.mycryptocheckout_checkout_data.supports.metamask_currency === 'undefined')
				return;
	
			$$.$metamask.click(async function() {
			   try {
	
					// const accounts = await window.web3.eth.getAccounts();
					const accounts = await metamaskProvider.request({ method: 'eth_requestAccounts' });
	
					if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id != 'undefined') {
						const chainIdNumber = $$.mycryptocheckout_checkout_data.supports.metamask_id;
						const desiredChainId = '0x' + parseInt(chainIdNumber).toString(16);
		
						try {
							await metamaskProvider.request({
								method: 'wallet_switchEthereumChain',
								params: [{ chainId: desiredChainId }],
							});
						} catch (error) {
							if (error.code === 4902) {
								console.error('The network is not available in MetaMask.');
							} else {
								console.error('Failed to switch the network:', error);
								return;
							}
						}
					}
		
					var send_parameters = {
						'from': accounts[0],
					};
		
					// var use_eip1559 = (typeof $$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0].maxPriorityFeePerGas !== 'undefined');
					var gas_set = false;
		
					// Supports EIP 1559 and is not BSC
					if ($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0] != null && $$.mycryptocheckout_checkout_data.supports.metamask_id !== 56) {
						console.debug("Using EIP1559");
		
						const maxPriorityFeePerGasWei = web3.utils.toWei(
							parseFloat($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0].maxPriorityFeePerGas).toFixed(9),
							'gwei'
						);
						const maxFeePerGasWei = web3.utils.toWei(
							parseFloat($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].speeds[0].maxFeePerGas).toFixed(9),
							'gwei'
						);
		
						// console.debug("maxPriorityFeePerGasWei:", maxPriorityFeePerGasWei);
						// console.debug("maxFeePerGasWei:", maxFeePerGasWei);
		
						send_parameters['maxPriorityFeePerGas'] = maxPriorityFeePerGasWei;
						send_parameters['maxFeePerGas'] = maxFeePerGasWei;
		
						send_parameters['gasLimit'] = web3.utils.toHex(Math.ceil($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].avgGas));
						// console.debug("gasLimit:", send_parameters['gasLimit']);
		
						gas_set = true;
					}
		
					if (!gas_set) {
						if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_gas !== 'undefined') {
							console.debug('Setting general metamask gas.');
							var metamask_gas = $$.mycryptocheckout_checkout_data.supports.metamask_gas;
							send_parameters['gasPrice'] = web3.utils.toWei(metamask_gas.price + '', 'gwei');
							// console.debug("gasPrice:", send_parameters['gasPrice']);
		
							
							send_parameters['gasLimit'] = web3.utils.toHex(Math.ceil($$.mycryptocheckout_checkout_data.supports.metamask_gas["1559"].avgGas));
							// console.debug("gasLimit:", send_parameters['gas']);
		
							gas_set = true;
						}
					}	
	
					if (contractInstance === false) {
						send_parameters['to'] = $$.mycryptocheckout_checkout_data.to;
						send_parameters['gasLimit'] = web3.utils.toHex(40000);
					
						try {
							// Step 1: Convert amount to Wei (string)
							var amountInWeiString = web3.utils.toWei(
								$$.mycryptocheckout_checkout_data.amount,
								$$.mycryptocheckout_checkout_data.supports.metamask_currency
							);
					
							// Step 2: Assign the amount string directly to send_parameters
							send_parameters['value'] = amountInWeiString;

							// Remove manual gas fee settings to let MetaMask handle it
							// 8-21-25 MetaMask needs this now
        					// delete send_parameters['maxPriorityFeePerGas'];
        					// delete send_parameters['maxFeePerGas'];
					
							console.debug('Mainnet send parameters', send_parameters);
					
							// Proceed with sending the transaction
							web3.eth.sendTransaction(send_parameters)
								.then((transactionHash) => {
									console.debug('ETH successfully sent via MetaMask.', transactionHash);
								})
								.catch((err) => {
									console.error('Error sending ETH via MetaMask', err);
					
									if ((err.error && err.error.code === -32000) ||
										(err.message && err.message.includes("insufficient funds")) ||
										(err.data && err.data.code === -32000)) {
										alert("Insufficient funds for the transaction. Please check your balance.");
									}
								});
						} catch (error) {
							console.error('An error occurred during the transaction preparation:', error);
						}
					}
					else
					{
						var amount = $$.mycryptocheckout_checkout_data.amount;
						// If there is a divider, use it.
						if ( typeof $$.mycryptocheckout_checkout_data.currency.divider !== 'undefined' ) {
							amount *= $$.mycryptocheckout_checkout_data.currency.divider;
						} else {
							if ( typeof $$.mycryptocheckout_checkout_data.supports.metamask_currency !== 'undefined') {
								amount = web3.utils.toWei( amount + "", $$.mycryptocheckout_checkout_data.supports.metamask_currency );
							} else {
								amount = web3.utils.toWei( amount + "", 'ether' );
							}
						}
	
						// .transfer loves plain strings.
						amount = amount + "";
	
						console.debug( "Token parameters", send_parameters );
	
						contractInstance.methods
							.transfer( $$.mycryptocheckout_checkout_data.to, amount )
							.send( send_parameters );
					}
	
				} catch (error) {
					console.error('An error occurred during the MetaMask operation:', error);
					if (error.code === 4001) {
						// User denied transaction signature
						console.debug('User denied transaction signature.');
					}
				}
			});
		} catch (error) {
			console.error('Failed to load providers using EIP-6963:', error);
		}
	}

	/**
		@brief		Show a MetaMask mobile payment link.
		@since		2024-06-10 17:25:03
	**/
	$$.maybe_metamask_mobile_link = function() {
		if ($$.$online_pay_box.length < 1)
			return;

		// only show if metamask is supported.
		if (typeof $$.mycryptocheckout_checkout_data.supports.metamask_id === 'undefined')
			return;

		// only show if web3 is not in window.
		if (typeof window.ethereum !== 'undefined')
			return;

		$$.show_browser_link = false;

		// Chain ID
		var chainId = $$.mycryptocheckout_checkout_data.supports.metamask_id;

		// To address
		var toAddress = $$.mycryptocheckout_checkout_data.to;

		// Amount
		var amount = new BigNumber($$.mycryptocheckout_checkout_data.amount);

		// Decimals
		var decimals = $$.mycryptocheckout_checkout_data.supports.metamask_mobile_decimals || 18;
		var decimalFactor = new BigNumber(10).pow(decimals);

		// Convert amount to the smallest unit based on decimals
		var amountInSmallestUnit = amount.multipliedBy(decimalFactor);

		// Convert to exponential notation, and replace 'e+' with 'e'
		var formattedNumber = amountInSmallestUnit.toExponential().replace("e+", "e");

		// Create URL
		var url = '';
		if ($$.mycryptocheckout_checkout_data.currency.contract) {
			var contract = $$.mycryptocheckout_checkout_data.currency.contract;
			// Note: token link
			url = `https://metamask.app.link/send/${contract}@${chainId}/transfer?address=${toAddress}&uint256=${formattedNumber}`;
		} else {
			url = `https://metamask.app.link/send/${toAddress}@${chainId}?value=${formattedNumber}`;
		}

		// Append the MetaMask link
		var $metamaskLink = $(`<a href="${url}"><div class="metamask_payment" role="img" aria-label="MetaMask wallet"></div></a>`);
		$metamaskLink.appendTo($$.$payment_buttons);
	}

	/**
		@brief		Show a trustwallet payment link.
		@since		2022-06-20 17:25:03
	**/
	$$.maybe_trustwallet_link = function()
	{
		if( typeof $$.mycryptocheckout_checkout_data.supports.trustwallet_chain == 'undefined' )
			return;

		var contract = '';
		if ( typeof $$.mycryptocheckout_checkout_data.currency.contract != 'undefined' )
			contract = '_t' + $$.mycryptocheckout_checkout_data.currency.contract;

		var trustwallet_chain = $$.mycryptocheckout_checkout_data.supports.trustwallet_chain;

		var html = '<a class="trustwallet_link" href="trust://send?asset=' + trustwallet_chain + contract + '&address=MCC_TO&amount=MCC_AMOUNT"><div class="trustwallet_link" role="img" aria-label="Trust wallet"></div></a>';
		html = $$.replace_keywords( html );
		var $div = $( '<div>' );
		$div.html( html );
		$div.appendTo( $$.$payment_buttons );
	}

	/**
		@brief		Maybe add some extra divs to bring old instructions up to date.
		@since		2018-04-25 22:03:08
	**/
	$$.maybe_upgrade_divs = function()
	{
		if ( $$.$online_pay_box.length > 0 )
			return;

		// Create the new div and put it after the h2.
		$$.$online_pay_box = $( '<div>' ).addClass( 'mcc_online_pay_box' );
		var $h2 = $( 'h2', $$.$div );
		$$.$online_pay_box.insertAfter( $h2 );

		// Move the P in there.
		$( 'p', $$.$div ).appendTo( $$.$online_pay_box );

		// If there is a QR div, put it in there also.
		$( '.mcc_qr_code', $$.$div ).appendTo( $$.$online_pay_box );

		// Instructions div is now upgraded to version 2.05.
	}

	/**
		@brief		Maybe add a waves payment link.
		@since		2018-12-14 17:50:20
	**/
	$$.maybe_waves_link = function()
	{
		var add_waves = false;
		var currency = 'WAVES';
		if ( typeof ( $$.mycryptocheckout_checkout_data.waves ) !== 'undefined' )
		{
			add_waves = true;
			console.debug( 'MyCryptoCheckout: Waves link', $$.mycryptocheckout_checkout_data );
			currency = $$.mycryptocheckout_checkout_data.token_id;
		}
		if ( $$.data.currency_id == 'WAVES' )
			add_waves = true;
		if ( ! add_waves )
			return;

		$$.show_browser_link = false;

		//var url = 'https://waves.exchange/#send/' + currency + '?recipient=MCC_TO&amount=MCC_AMOUNT&referrer=' + encodeURIComponent( window.location ) + '&strict';
		var url = 'https://waves.exchange/sign-in#send/' + currency + '?recipient=MCC_TO&amount=MCC_AMOUNT&strict';
		url = $$.replace_keywords( url );
		var html = '<a class="waves_payment" target="_blank" href="' + url + '"><div class="waves_payment" role="img" aria-label="Waves wallet"></div></a>';
		var $div = $( '<div>' );
		$div.html( html );
		$div.appendTo( $$.$payment_buttons );
	}

	/**
		@brief		Replace the MCC keywords in this string.
		@details	Replaces TO, AMOUNT, etc.
		@since		2018-12-14 17:54:59
	**/
	$$.replace_keywords = function( string )
	{
		string = string.replace( 'MCC_AMOUNT', $$.mycryptocheckout_checkout_data.amount );
		string = string.replace( 'MCC_TO', $$.mycryptocheckout_checkout_data.to );
		return string;
	}

	/**
		@brief		Update the payment timer countdown div.
		@since		2018-05-03 07:12:24
	**/
	$$.update_payment_timer = function()
	{
		var current_time = Math.round( ( new Date() ).getTime() / 1000 );
		var seconds_left = $$.payment_timer.timeout_time - current_time;

		if ( seconds_left < 1 )
		{
			clearInterval( $$.payment_timer.timeout_interval );
			$$.check_for_payment();
		}

		// Convert to hours.
		var hours = Math.floor( seconds_left / 60 / 60 );
		if ( hours < 10 )
			hours = '0' + hours;

		var minutes = ( seconds_left - ( hours * 3600 ) ) / 60;
		minutes = Math.floor( minutes );
		if ( minutes < 10 )
			minutes = '0' + minutes;

		var seconds = ( seconds_left - ( hours * 3600 ) ) % 60;
		if ( seconds < 10 )
			seconds = '0' + seconds;

		var text = '';
		if ( hours > 0 )
			text += hours + ':';
		text += minutes + ':' + seconds;
		$$.payment_timer.$hours_minutes.html( text );
	}

	$$.init();
}

var mycryptocheckout_convert_data = function( key, callback )
{
	var $data = $( '#' + key );
	if ( $data.length < 1 )
		return;
	// Extract the data
	var data = $data.data( key );
	// Convert from base64
	data = atob( data );
	// And parse into an object.
	data = jQuery.parseJSON( data );
	// And give to the callback.
	return callback( data );
}

/**
	@brief		Handle the donations div.
	@since		2018-05-12 21:24:33
**/
;(function( $ )
{
    $.fn.extend(
    {
        mycryptocheckout_donations_javascript: function()
        {
            return this.each( function()
            {
                var $$ = $(this);
                /**
                	@brief		The data object for this div.
                	@since		2018-05-12 21:59:10
                **/
                $$.div_data = null;

                $$.address = $( '.address', $$ );
                $$.currency_selector = $( '.currency_selector select', $$ );

                /**
                	@brief		Extract and convert the data json.
                	@since		2018-05-12 21:57:55
                **/
                $$.extract_data = function()
                {
                	var data = $$.data( 'mycryptocheckout_donations_data' );
					// Convert from base64
					data = atob( data );
					// And parse into an object.
					$$.div_data = jQuery.parseJSON( data );
					console.log( 'MyCryptoCheckout: Donations div data', $$.div_data );
                }

                /**
                	@brief		Init this donations div.
                	@since		2018-05-12 21:57:37
                **/
                $$.init = function()
                {
                	$$.extract_data();
                	$$.init_icons();
                	$$.init_currency_selector();
                	// Set the first available currency.
                	var primary_currency = $$.div_data[ 'primary_currency' ];
                	$$.set_currency_id( primary_currency );
                	$$.addClass( 'alignment_' + $$.div_data[ 'alignment' ] );
                }

                /**
                	@brief		Init the currency selector, if any.
                	@since		2018-05-12 22:02:52
                **/
                $$.init_currency_selector = function()
                {
                	if ( $$.currency_selector.length < 1 )
                		return;
                	// And the settings must say that we use the selector.
                	if ( $$.div_data[ 'show_currencies_as_select' ] != '1' )
                		return;
                	// Put all of the currencies in the selector.
                	$.each( $$.div_data[ 'currencies' ], function( index, currency )
                	{
                		var $option = $( '<option>' );
                		$option.html( currency.currency_name );
                		$option.attr( 'value', currency.currency_id );
                		$option.appendTo( $$.currency_selector );
                	} );
                	$$.currency_selector.change( function()
                	{
                		// Get the currency ID.
                		var currency_id = $$.currency_selector.val();
                		var currency = $$.div_data.currencies[ currency_id ];
                		var address = currency[ 'address' ];
                		// Show the address for this currency in the address field.
                		$$.set_currency_id( currency_id );
                	} ).change();
                	$( '.currency_selector', $$ ).show();
                }

                $$.init_icons = function()
                {
                	$$.icons = $( '.currency_icons', $$ );
                	// The div must exist.
                	if ( $$.icons.length < 1 )
                		return;
                	// And the settings must say that we use icons.
                	if ( $$.div_data[ 'show_currencies_as_icons' ] != '1' )
                		return;
                	$.each( $$.div_data[ 'currencies' ], function( index, currency )
                	{
                		var $icon = $( '<img>' );
                		$icon.addClass( 'mcc_donation_icon' );
                		$icon.attr( 'src', currency.icon );
                		$icon.appendTo( $$.icons );

                		// Make the icon clickable.
                		$icon.click( function()
                		{
							$$.set_currency_id( currency.currency_id );
                		} );
                	} );
                	$$.icons.show();
                }

                /**
                	@brief		Show a qr code with this address.
                	@since		2018-05-12 22:11:28
                **/
                $$.qr_code = function( currency_id )
                {
                	if ( $$.div_data[ 'qr_code_enabled' ] != '1' )
            			return;
            		var $qr_code = $( '.qr_code', $$ );
            		// Set the div size.
            		var width = $$.div_data[ 'qr_code_max_width' ];
            		$qr_code.css( {
            			'height' : 'auto',
            			'max-width' : width,
            		} );
            		$qr_code.html( '' );

            		var address = $$.div_data[ 'currencies' ][ currency_id ][ 'address' ];
            		if ( $$.div_data[ 'currencies' ][ currency_id ][ 'qr_code_text' ] !== undefined )
            		{
            			var qr_code_text = $$.div_data[ 'currencies' ][ currency_id ][ 'qr_code_text' ];
						qr_code_text = qr_code_text
							.replace( '[MCC_TO]', address )
							.replace( '[MCC_AMOUNT]', 0 )
							;
						address = qr_code_text;
            		}
					QRCode.toDataURL( qr_code_text )
						.then( url =>
							{
								var $img = $( '<img>' )
									.prop( 'data-src', url )
									.prop( 'src', url )
									.prop( 'title', qr_code_text );
								$img.appendTo( $qr_code );
							})
					  .catch( err =>
					  {
						console.error( 'Error generating QR code', err );
					  });
            		$qr_code.show();
                }

                /**
                	@brief		Convenience method to set the address everywhere.
                	@since		2018-05-12 22:43:48
                **/
                $$.set_currency_id = function( currency_id )
                {
                	var address = $$.div_data[ 'currencies' ][ currency_id ][ 'address' ];
                	$$.currency_selector.val( currency_id );
					$$.show_address( address );
                	$$.show_currency_name( $$.div_data[ 'currencies' ][ currency_id ][ 'currency_name' ] );
					$$.qr_code( currency_id );
                }

                /**
                	@brief		Show the address.
                	@since		2018-05-12 22:44:05
                **/
                $$.show_address = function( address )
                {
                	if ( $$.div_data[ 'show_address' ] != '1' )
            			return;
            		$$.address.html( address ).show();
            		$$.address.removeClass( 'clipboarded' );
            		$( '.mycryptocheckout .to_input' ).mcc_make_clipboard();
                }

                /**
                	@brief		Show the name of the currency.
                	@since		2018-05-14 23:09:54
                **/
                $$.show_currency_name = function( currency_name )
                {
                	if ( $$.div_data[ 'show_currency_as_text' ] != '1' )
            			return;
                	$( '.selected_currency', $$ ).html( currency_name ).show();
                }

                $$.init();
            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );

mycryptocheckout_convert_data( 'mycryptocheckout_checkout_data', function( data )
{
	mycryptocheckout_checkout_javascript( data );
} );
$( 'form.plainview_form_auto_tabs' ).plainview_form_auto_tabs();
$( '.mcc_donations' ).mycryptocheckout_donations_javascript();

$( 'form#currencies' ).mycryptocheckout_new_currency();

/**
	@brief		Make the wallets sortable.
	@since		2018-10-17 17:38:58
**/
$( 'table.currencies tbody' ).mycryptocheckout_sort_wallets();
} );
