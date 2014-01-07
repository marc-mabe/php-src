/*
   +----------------------------------------------------------------------+
   | Zend Engine                                                          |
   +----------------------------------------------------------------------+
   | Copyright (c) 1998-2013 Zend Technologies Ltd. (http://www.zend.com) |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.00 of the Zend license,     |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.zend.com/license/2_00.txt.                                |
   | If you did not receive a copy of the Zend license and are unable to  |
   | obtain it through the world-wide-web, please send a note to          |
   | license@zend.com so we can mail you a copy immediately.              |
   +----------------------------------------------------------------------+
   | Authors: Marc Bennewitz <marc-bennewitz@arcor.de>                    |
   +----------------------------------------------------------------------+
*/

/* $Id$ */

#include <ctype.h>

#include "zend.h"
#include "zend_operators.h"
#include "zend_cmp.h"
#include "zend_variables.h"
#include "zend_globals.h"
#include "zend_list.h"
#include "zend_API.h"

static inline zend_uchar strton(const char *str, int length, long *lval, double *dval)
{
	errno = 0;
	int base = 10;
        int negative = 0;
	const char *str_start = str;
	const char *str_end = str_start + length;
	char *str_stop;
	long lval_local;
	double dval_local;

	/* don't allow empty strings */
	if (!length) {
		return 0;
	}

        /* allow sign for all variants */
        /* strtol/strtod only accepts sign for decimals */
        if (*str == '+') {
            length--;
            if (!length) {
                return 0;
            }
            str++;
        } else if (*str == '-') {
            length--;
            if (!length) {
                return 0;
            }
            str++;
            negative = 1;
        }

	/* hex numbers */
	if (length > 2 && *str == '0' && (str[1] == 'x' || str[1] == 'X')) {
		base = 16;
		str += 2;
	/* octal number */
	} else if (length > 1 && *str == '0') {
		base = 8;
		str += 1;
        }

        /* prevent duplicated sign */
        /* strtol/strtod skips beginning whitespaces */
        /* (this is much faster than the isspace() function) */
        if (*str == '+' || *str == '-' || *str == ' ' || *str == '\t' || *str == '\n' || *str == '\r' || *str == '\v' || *str == '\f') {
            return 0;
        }

	/* parse integer numbers */
	/* TODO: return as double if out of range */
	lval_local = strtol(str, &str_stop, base);
	if (!*str_stop && !errno) {
		if (lval) {
			*lval = negative ? (long)-lval_local : (long)lval_local;
			return IS_LONG;
		}
		if (dval) {
			*dval = negative ? -(double)lval_local : (double)lval_local;
			return IS_DOUBLE;
		}

	/* parse real numbers (requires base of 10) */
	} else if (base == 10) {
		str_stop = NULL;
		dval_local = strtod(str, &str_stop);
		if (!*str_stop) {
			if (dval) {
				*dval = negative ? -dval_local : dval_local;
				return IS_DOUBLE;
			}
			/* if no dval but lval was given - check if it's possible to store as lval */
			if (lval && dval_local < LONG_MAX && dval_local > LONG_MIN && fmod(dval_local, 1) == 0) {
				*lval = negative ? -(long)dval_local : (long)dval_local;
				return IS_LONG;
			}
		}
	}

	return FAILURE;
}

/* {{{ */
static inline void zend_free_obj_get_result(zval *op TSRMLS_DC)
{
        if (Z_REFCOUNT_P(op) == 0) {
                GC_REMOVE_ZVAL_FROM_BUFFER(op);
                zval_dtor(op);
                FREE_ZVAL(op);
        } else {
                zval_ptr_dtor(&op);
        }
}
/* }}} */

/* {{{ */
static int hash_cmp_zval(const zval **z1, const zval **z2)
{
	TSRMLS_FETCH();
	return zend_cmp_zval((zval *) *z1, (zval *) *z2 TSRMLS_CC);
}
/* }}} */

/* {{{ */
ZEND_API int zend_is_true_zval(zval *op TSRMLS_DC)
{
	switch (Z_TYPE_P(op)) {
		case IS_NULL:
			return 1;
		case IS_BOOL:
		case IS_LONG:
		case IS_RESOURCE:
			return Z_LVAL_P(op) ? 1 : 0;
		case IS_DOUBLE:
			return Z_DVAL_P(op) ? 1 : 0;
		case IS_STRING:
			return Z_STRLEN_P(op) > 0;
		case IS_ARRAY:
			return zend_hash_num_elements(Z_ARRVAL_P(op)) > 0;
		case IS_OBJECT: {
			int result = 1;
			zval *tmp;

			if (IS_ZEND_STD_OBJECT(*op)) {
				if (Z_OBJ_HT_P(op)->cast_object) {
					if (Z_OBJ_HT_P(op)->cast_object(op, tmp, IS_BOOL TSRMLS_CC) == SUCCESS) {
						result = Z_LVAL_P(tmp);
						break;
					}
				} else if (Z_OBJ_HT_P(op)->get) {
					tmp = Z_OBJ_HT_P(op)->get(op TSRMLS_CC);
					if (Z_TYPE_P(tmp) != IS_OBJECT) {
						/* for safety - avoid loop */
						convert_to_boolean(tmp);
						result = Z_LVAL_P(tmp);
						zval_ptr_dtor(&tmp);
						break;
					}
				}
			}
			return result;
		}
        }
	return 0;
}
/* }}} */

/* {{{ */
ZEND_API int zend_cmp_zval(zval *op1, zval *op2 TSRMLS_DC)
{
        int ival = 0;
        long lval = 0;
        double dval = 0;
        zval zend_val;
        zval *op_free;

	if (Z_TYPE_P(op1) == Z_TYPE_P(op2)) {
		switch (Z_TYPE_P(op1)) {
			case IS_NULL:
				return IS_EQUAL;
			case IS_BOOL:
			case IS_LONG:
				if (Z_LVAL_P(op1) < Z_LVAL_P(op2)) {
					return IS_SMALLER;
				} else if (Z_LVAL_P(op1) > Z_LVAL_P(op2)) {
					return IS_GREATER;
				}
				return IS_EQUAL;
			case IS_DOUBLE:
				if (Z_DVAL_P(op1) < Z_DVAL_P(op2)) {
					return IS_SMALLER;
				} else if (Z_DVAL_P(op1) > Z_DVAL_P(op2)) {
					return IS_GREATER;
				} else if (EXPECTED(Z_DVAL_P(op1) == Z_DVAL_P(op2))
					|| (isnan(Z_DVAL_P(op1)) && isnan(Z_DVAL_P(op2)))
				) {
					return IS_EQUAL;
				}
				return IS_NOT_EQUAL;
			case IS_STRING:
				return zend_cmp_str(Z_STRVAL_P(op1), Z_STRLEN_P(op1), Z_STRVAL_P(op2), Z_STRLEN_P(op2));
			case IS_ARRAY:
				return zend_cmp_ht(Z_ARRVAL_P(op1), Z_ARRVAL_P(op2));
			case IS_RESOURCE:
				return Z_RESVAL_P(op1) == Z_RESVAL_P(op2) ? IS_EQUAL : IS_NOT_EQUAL;
		}
	}

	switch (TYPE_PAIR(Z_TYPE_P(op1), Z_TYPE_P(op2))) {
                /* NULL <-> bool = FALSE <-> bool */
                case TYPE_PAIR(IS_NULL, IS_BOOL):
                        return Z_LVAL_P(op2) ? IS_SMALLER : IS_EQUAL;
                case TYPE_PAIR(IS_BOOL, IS_NULL):
                        return Z_LVAL_P(op1) ? IS_GREATER : IS_EQUAL;

		/* NULL <-> long = 0 <-> long */
		case TYPE_PAIR(IS_NULL, IS_LONG):
                        return Z_LVAL_P(op2) == 0 ? IS_EQUAL : (Z_LVAL_P(op2) > 0 ? IS_SMALLER : IS_GREATER);
		case TYPE_PAIR(IS_LONG, IS_NULL):
			return Z_LVAL_P(op1) == 0 ? IS_EQUAL : (Z_LVAL_P(op1) > 0 ? IS_GREATER : IS_SMALLER);

		/* NULL <-> double = 0 <-> double */
		case TYPE_PAIR(IS_NULL, IS_DOUBLE):
			if (0 < Z_DVAL_P(op2)) {
				return IS_SMALLER;
			} else if (0 > Z_DVAL_P(op2)) {
				return IS_GREATER;
			} else if (EXPECTED(0 == Z_DVAL_P(op2))) {
				return IS_EQUAL;
			}
			return IS_NOT_EQUAL;
		case TYPE_PAIR(IS_DOUBLE, IS_NULL):
			if (Z_DVAL_P(op1) < 0) {
				return IS_SMALLER;
			} else if (Z_DVAL_P(op1) > 0) {
				return IS_GREATER;
			} else if (Z_DVAL_P(op1) == 0) {
				return IS_EQUAL;
			}
			return IS_NOT_EQUAL;

		/* null <-> string = "" <-> string */
		case TYPE_PAIR(IS_NULL, IS_STRING):
			return 0 < Z_STRLEN_P(op2) ? IS_SMALLER : IS_EQUAL;
		case TYPE_PAIR(IS_STRING, IS_NULL):
			return Z_STRLEN_P(op1) > 0 ? IS_GREATER : IS_EQUAL;

		/* null <-> array = false <-> (bool)count(array) */
		case TYPE_PAIR(IS_NULL, IS_ARRAY):
			return zend_hash_num_elements(Z_ARRVAL_P(op2)) ? IS_SMALLER : IS_EQUAL;
		case TYPE_PAIR(IS_ARRAY, IS_NULL):
			return zend_hash_num_elements(Z_ARRVAL_P(op1)) ? IS_GREATER : IS_EQUAL;

                /* bool <-> long = bool <-> (bool)long */
                case TYPE_PAIR(IS_BOOL, IS_LONG):
                case TYPE_PAIR(IS_LONG, IS_BOOL):
                        if ((Z_LVAL_P(op1) && Z_LVAL_P(op2)) || (!Z_LVAL_P(op1) && !Z_LVAL_P(op2))) {
                                return IS_EQUAL;
                        }
                        return IS_NOT_EQUAL;

                /* bool <-> double = bool <-> (bool)double */
                case TYPE_PAIR(IS_BOOL, IS_DOUBLE):
                        if ((Z_LVAL_P(op1) && Z_DVAL_P(op2) && !isnan(Z_DVAL_P(op2))) || (!Z_LVAL_P(op1) && (!Z_DVAL_P(op2) || isnan(Z_DVAL_P(op2))))) {
                                return IS_EQUAL;
                        }
                        return IS_NOT_EQUAL;
                case TYPE_PAIR(IS_DOUBLE, IS_BOOL):
                        if ((Z_DVAL_P(op1) && Z_LVAL_P(op2) && !isnan(Z_DVAL_P(op1))) || (!Z_DVAL_P(op1) && (!Z_LVAL_P(op2) || isnan(Z_DVAL_P(op1))))) {
                                return IS_EQUAL;
                        }
                        return IS_NOT_EQUAL;

                /* bool <-> string = bool <-> (bool)strlen(string) */
                case TYPE_PAIR(IS_BOOL, IS_STRING):
			if (Z_LVAL_P(op1)) {
				return Z_STRLEN_P(op2) ? IS_EQUAL : IS_GREATER;
			}
			return Z_STRLEN_P(op2) ? IS_SMALLER : IS_EQUAL;
                case TYPE_PAIR(IS_STRING, IS_BOOL):
			if (Z_STRLEN_P(op1)) {
				return Z_LVAL_P(op2) ? IS_EQUAL : IS_GREATER;
			}
			return Z_LVAL_P(op2) ? IS_SMALLER : IS_EQUAL;;

                /* bool <-> array = bool <-> (bool)count(array) */
                case TYPE_PAIR(IS_BOOL, IS_ARRAY):
                        lval = zend_hash_num_elements(Z_ARRVAL_P(op2));
                        return (Z_LVAL_P(op1) && lval) || (!Z_LVAL_P(op1) && !lval) ? IS_EQUAL : IS_NOT_EQUAL;
                case TYPE_PAIR(IS_ARRAY, IS_BOOL):
                        lval = zend_hash_num_elements(Z_ARRVAL_P(op1));
                        return (lval && Z_LVAL_P(op2)) || (!lval && !Z_LVAL_P(op2)) ? IS_EQUAL : IS_NOT_EQUAL;


                /* long <-> double = (double)long <-> double */
                case TYPE_PAIR(IS_DOUBLE, IS_LONG):
                        dval = (double)Z_LVAL_P(op2);
                        if (Z_DVAL_P(op1) < dval) {
                                return IS_SMALLER;
                        } else if (Z_DVAL_P(op1) > dval) {
                                return IS_GREATER;
                        } else if (Z_DVAL_P(op1) == dval) {
                                return IS_EQUAL;
                        }
                        return IS_NOT_EQUAL;
                case TYPE_PAIR(IS_LONG, IS_DOUBLE):
                        dval = (double)Z_LVAL_P(op1);
                        if (dval < Z_DVAL_P(op2)) {
                                return IS_SMALLER;
                        } else if (dval > Z_DVAL_P(op2)) {
                                return IS_GREATER;
                        } else if (dval == Z_DVAL_P(op2)) {
                                return IS_EQUAL;
                        }
                        return IS_NOT_EQUAL;

		/* long <-> string = long <-> (long)string */
		case TYPE_PAIR(IS_STRING, IS_LONG):
			if (strton(Z_STRVAL_P(op1), Z_STRLEN_P(op1), &lval, NULL) == IS_LONG) {
				if (lval < Z_LVAL_P(op2)) {
					return IS_SMALLER;
				} else if (lval > Z_LVAL_P(op2)) {
					return IS_GREATER;
				}
				return IS_EQUAL;
			}
			return IS_NOT_EQUAL;
		case TYPE_PAIR(IS_LONG, IS_STRING):
			if (strton(Z_STRVAL_P(op2), Z_STRLEN_P(op2), &lval, NULL) == IS_LONG) {
				if (Z_LVAL_P(op1) < lval) {
					return IS_SMALLER;
				} else if (Z_LVAL_P(op1) > lval) {
					return IS_GREATER;
				}
				return IS_EQUAL;
			}
			return IS_NOT_EQUAL;

                /* long <-> array = long <-> count(array) */
                case TYPE_PAIR(IS_LONG, IS_ARRAY):
                        lval = zend_hash_num_elements(Z_ARRVAL_P(op2));
                        return Z_LVAL_P(op1) < lval ? IS_SMALLER : (Z_LVAL_P(op1) > lval ? IS_GREATER : IS_EQUAL);
                case TYPE_PAIR(IS_ARRAY, IS_LONG):
                        lval = zend_hash_num_elements(Z_ARRVAL_P(op1));
                        return lval < Z_LVAL_P(op2) ? IS_SMALLER : (lval > Z_LVAL_P(op2) ? IS_GREATER : IS_EQUAL);

		/* double <-> string = double <-> (double)string */
		case TYPE_PAIR(IS_STRING, IS_DOUBLE):
			if (strton(Z_STRVAL_P(op1), Z_STRLEN_P(op1), NULL, &dval) == IS_DOUBLE) {
				if (dval < Z_DVAL_P(op2)) {
					return IS_SMALLER;
				} else if (dval > Z_DVAL_P(op2)) {
					return IS_GREATER;
				} else if (EXPECTED(dval == Z_DVAL_P(op2))) {
					return IS_EQUAL;
				}
				return IS_NOT_EQUAL;
			}
			return isnan(Z_DVAL_P(op2)) ? IS_EQUAL : IS_NOT_EQUAL;
		case TYPE_PAIR(IS_DOUBLE, IS_STRING):
			if (strton(Z_STRVAL_P(op2), Z_STRLEN_P(op2), NULL, &dval) == IS_DOUBLE) {
				if (Z_DVAL_P(op1) < dval) {
					return IS_SMALLER;
				} else if (Z_DVAL_P(op1) > dval) {
					return IS_GREATER;
				} else if (EXPECTED(Z_DVAL_P(op1) == dval)) {
					return IS_EQUAL;
				}
				return IS_NOT_EQUAL;
			}
			return isnan(Z_DVAL_P(op1)) ? IS_EQUAL : IS_NOT_EQUAL;

                /* double <-> array = double <-> count(array) */
                case TYPE_PAIR(IS_DOUBLE, IS_ARRAY):
                        dval = (double)zend_hash_num_elements(Z_ARRVAL_P(op2));
                        return Z_DVAL_P(op1) < dval ? IS_SMALLER : (Z_DVAL_P(op1) > dval ? IS_GREATER : IS_EQUAL);
                case TYPE_PAIR(IS_ARRAY, IS_DOUBLE):
                        dval = (double)zend_hash_num_elements(Z_ARRVAL_P(op1));
                        return dval < Z_DVAL_P(op2) ? IS_SMALLER : (dval > Z_DVAL_P(op2) ? IS_GREATER : IS_EQUAL);

		/* object comparison */
		default:
			if (Z_TYPE_P(op1) == IS_OBJECT && Z_OBJ_HANDLER_P(op1, compare)) {
				Z_OBJ_HANDLER_P(op1, compare)(&zend_val, op1, op2 TSRMLS_CC);
				return Z_LVAL(zend_val);
			} else if (Z_TYPE_P(op2) == IS_OBJECT && Z_OBJ_HANDLER_P(op2, compare)) {
				Z_OBJ_HANDLER_P(op2, compare)(&zend_val, op1, op2 TSRMLS_CC);
				return Z_LVAL(zend_val);
			}
			if (Z_TYPE_P(op1) == IS_OBJECT && Z_TYPE_P(op2) == IS_OBJECT) {
				if (Z_OBJ_HANDLE_P(op1) == Z_OBJ_HANDLE_P(op2)) {
					/* object handles are identical, apparently this is the same object */
					return IS_EQUAL;
				}
				if (Z_OBJ_HANDLER_P(op1, compare_objects) == Z_OBJ_HANDLER_P(op2, compare_objects)) {
					return Z_OBJ_HANDLER_P(op1, compare_objects)(op1, op2 TSRMLS_CC);
				}
			}
			if (Z_TYPE_P(op1) == IS_OBJECT) {
				if (Z_OBJ_HT_P(op1)->get) {
					op_free = Z_OBJ_HT_P(op1)->get(op1 TSRMLS_CC);
					ival = zend_cmp_zval(op_free, op2 TSRMLS_CC);
					zend_free_obj_get_result(op_free TSRMLS_CC);
					return ival;
				} else if (Z_TYPE_P(op2) != IS_OBJECT && Z_OBJ_HT_P(op1)->cast_object) {
					ALLOC_INIT_ZVAL(op_free);
					if (Z_OBJ_HT_P(op1)->cast_object(op1, op_free, Z_TYPE_P(op2) TSRMLS_CC) == FAILURE) {
						zend_free_obj_get_result(op_free TSRMLS_CC);
						return IS_NOT_EQUAL;
					}
					ival = zend_cmp_zval(op_free, op2 TSRMLS_CC);
					zend_free_obj_get_result(op_free TSRMLS_CC);
					return ival;
				}
			}
			if (Z_TYPE_P(op2) == IS_OBJECT) {
				if (Z_OBJ_HT_P(op2)->get) {
					op_free = Z_OBJ_HT_P(op2)->get(op2 TSRMLS_CC);
					ival = zend_cmp_zval(op1, op_free TSRMLS_CC);
					zend_free_obj_get_result(op_free TSRMLS_CC);
					return ival;
				} else if (Z_TYPE_P(op1) != IS_OBJECT && Z_OBJ_HT_P(op2)->cast_object) {
					ALLOC_INIT_ZVAL(op_free);
					if (Z_OBJ_HT_P(op2)->cast_object(op2, op_free, Z_TYPE_P(op1) TSRMLS_CC) == FAILURE) {
						zend_free_obj_get_result(op_free TSRMLS_CC);
						return IS_NOT_EQUAL;
					}
					ival = zend_cmp_zval(op1, op_free TSRMLS_CC);
					zend_free_obj_get_result(op_free TSRMLS_CC);
					return ival;
				}
		}
	}

	return IS_NOT_EQUAL;
}
/* }}} */

/* {{{ */
ZEND_API int zend_cmp_str(const char *s1, uint len1, const char *s2, uint len2 TSRMLS_DC)
{
	int retval;

	if (s1 == s2) {
		return IS_EQUAL;
	}

	retval = memcmp(s1, s2, MIN(len1, len2));
	if (retval == IS_EQUAL) {
		return len1 < len2 ? -1 : (len1 > len2 ? IS_GREATER : IS_EQUAL);
	}
	return retval;
}
/* }}} */

/* {{{ */
ZEND_API int zend_cmp_ht(HashTable *ht1, HashTable *ht2 TSRMLS_DC)
{
	if (ht1 == ht2) {
		return IS_EQUAL;
	}

	return zend_hash_compare(ht1, ht2, (compare_func_t) hash_cmp_zval, 1 TSRMLS_CC);
}
/* }}} */

/* {{{ */
static int hash_is_identical_zval(const zval **z1, const zval **z2)
{
	TSRMLS_FETCH();
	return zend_is_identical_zval((zval *) *z1, (zval *) *z2 TSRMLS_CC);
}
/* }}} */

/* {{{ */
ZEND_API int zend_is_identical_zval(zval *op1, zval *op2 TSRMLS_DC)
{
	if (UNEXPECTED(Z_TYPE_P(op1) != Z_TYPE_P(op2))) {
		return 0;
	}

	switch (Z_TYPE_P(op1)) {
		case IS_NULL:
			return 1;
		case IS_BOOL:
		case IS_LONG:
		case IS_RESOURCE:
			return Z_LVAL_P(op1) == Z_LVAL_P(op2) ? 1 : 0;
		case IS_DOUBLE:
			return Z_DVAL_P(op1) == Z_DVAL_P(op2) ? 1 : 0;
		case IS_STRING:
			return zend_is_identical_str(Z_STRVAL_P(op1), Z_STRLEN_P(op1), Z_STRVAL_P(op2), Z_STRLEN_P(op2));
		case IS_ARRAY:
			if (Z_ARRVAL_P(op1) == Z_ARRVAL_P(op2)) {
				return 1;
			}
			return zend_hash_compare(Z_ARRVAL_P(op1), Z_ARRVAL_P(op2), (compare_func_t) hash_is_identical_zval, 1 TSRMLS_CC) == IS_EQUAL;
		case IS_OBJECT:
			if (Z_OBJ_HT_P(op1) == Z_OBJ_HT_P(op2)) {
				return (Z_OBJ_HANDLE_P(op1) == Z_OBJ_HANDLE_P(op2));
			}
			return 0;
	}

	return 0;
}
/* }}} */

/* {{{ */
ZEND_API int zend_is_identical_str(const char *s1, uint len1, const char *s2, uint len2 TSRMLS_DC)
{
	if (s1 == s2) {
		return 1;
	}

	if (len1 == len2 && !memcmp(s1, s2, len1)) {
		return 1;
	}

	return 0;
}
/* }}} */

/* {{{ */
ZEND_API int zend_is_identical_ht(HashTable *ht1, HashTable *ht2 TSRMLS_DC)
{
	if (ht1 == ht2) {
		return 1;
	}

        return !zend_hash_compare(ht1, ht2, (compare_func_t) hash_is_identical_zval, 1 TSRMLS_CC);
}
/* }}} */

/* {{{ */
ZEND_API int zend_is_equal_zval(zval *op1, zval *op2 TSRMLS_DC)
{
	return zend_cmp_zval(op1, op2 TSRMLS_CC) == IS_EQUAL;
}
/* }}} */

/* }}} */
ZEND_API int zend_is_smaller_zval(zval *op1, zval *op2 TSRMLS_DC)
{
	return zend_cmp_zval(op1, op2) == IS_SMALLER;
}
/* }}} */

/* }}} */
ZEND_API int zend_is_smaller_or_equal_zval(zval *op1, zval *op2 TSRMLS_DC)
{
        return zend_cmp_zval(op1, op2) <= IS_EQUAL;
}
/* }}} */
