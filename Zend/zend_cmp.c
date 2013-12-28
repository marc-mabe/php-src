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
	const char *str_start = str;
	const char *str_end = str_start + length;
	char *str_stop;
	long lval_local;
	double dval_local;

	/* An empty string or a string beginning with a whitespace is not a valid nummber */
	/* strto* skips beginning whitespaces */
	/* This is much faster than the isspace() function */
	if (!length || *str == ' ' || *str == '\t' || *str == '\n' || *str == '\r' || *str == '\v' || *str == '\f') {
		return 0;
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

	/* parse integer numbers */
	/* TODO: return as double if out of range */
	lval_local = strtol(str, &str_stop, base);
	if (!*str_stop && !errno) {
		if (lval) {
			*lval = (long)lval_local;
			return IS_LONG;
		}
		if (dval) {
			*dval = (double)lval_local;
			return IS_DOUBLE;
		}

	/* parse real numbers (requires base of 10) */
	} else if (base == 10) {
		str_stop = NULL;
		dval_local = strtod(str, &str_stop);
		if (!*str_stop) {
			if (dval) {
				*dval = dval_local;
				return IS_DOUBLE;
			}
			/* if no dval but lval was given - check if it's possible to store as lval */
			if (lval && dval_local < LONG_MAX && dval_local > LONG_MIN && fmod(dval_local, 1) == 0) {
				*lval = (long)dval_local;
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
ZEND_API int zend_cmp_zval(zval *op1, zval *op2 TSRMLS_DC)
{
        int ival = 0;
        long lval = 0;
        double dval = 0;
        zval zend_val;
        zval *op_free;

        switch (TYPE_PAIR(Z_TYPE_P(op1), Z_TYPE_P(op2))) {
                case TYPE_PAIR(IS_NULL, IS_NULL):
                        return IS_EQUAL;

                case TYPE_PAIR(IS_BOOL, IS_BOOL):
                case TYPE_PAIR(IS_LONG, IS_LONG):
                        return Z_LVAL_P(op1) < Z_LVAL_P(op2) ? IS_SMALLER : (Z_LVAL_P(op1) > Z_LVAL_P(op2) ? IS_GREATER : IS_EQUAL);

                case TYPE_PAIR(IS_DOUBLE, IS_DOUBLE):
                        return Z_DVAL_P(op1) < Z_DVAL_P(op2) ? IS_SMALLER : (Z_DVAL_P(op1) > Z_DVAL_P(op2) ? IS_GREATER : IS_EQUAL);

                case TYPE_PAIR(IS_STRING, IS_STRING):
			return zend_cmp_str(Z_STRVAL_P(op1), Z_STRLEN_P(op1), Z_STRVAL_P(op2), Z_STRLEN_P(op2));

                case TYPE_PAIR(IS_ARRAY, IS_ARRAY):
			return zend_cmp_ht(Z_ARRVAL_P(op1), Z_ARRVAL_P(op2));

                case TYPE_PAIR(IS_RESOURCE, IS_RESOURCE):
                        return Z_RESVAL_P(op1) == Z_RESVAL_P(op2) ? IS_EQUAL : IS_NOT_EQUAL;

                // long <-> double = (double)long <-> double
                case TYPE_PAIR(IS_DOUBLE, IS_LONG):
                        dval = (double)Z_LVAL_P(op2);
                        return Z_DVAL_P(op1) < dval ? IS_SMALLER : (Z_DVAL_P(op1) > dval ? IS_GREATER : IS_EQUAL);

                case TYPE_PAIR(IS_LONG, IS_DOUBLE):
                        dval = (double)Z_LVAL_P(op1);
                        return dval < Z_DVAL_P(op2) ? IS_SMALLER : (dval > Z_DVAL_P(op2) ? IS_GREATER : IS_EQUAL);

                // null <-> long   = 0 <-> long
                // null <-> double = 0 <-> double
                case TYPE_PAIR(IS_NULL, IS_LONG):
                        return 0 < Z_LVAL_P(op2) ? IS_SMALLER : (0 > Z_LVAL_P(op2) ? IS_GREATER : IS_EQUAL);

                case TYPE_PAIR(IS_NULL, IS_DOUBLE):
                        return 0 < Z_DVAL_P(op2) ? IS_SMALLER : (0 > Z_DVAL_P(op2) ? IS_GREATER : IS_EQUAL);

                case TYPE_PAIR(IS_LONG, IS_NULL):
                        return Z_LVAL_P(op1) < 0 ? IS_SMALLER : (Z_LVAL_P(op1) > 0 ? IS_GREATER : IS_EQUAL);

                case TYPE_PAIR(IS_DOUBLE, IS_NULL):
                        return Z_DVAL_P(op1) < 0 ? IS_SMALLER : (Z_DVAL_P(op1) > 0 ? IS_GREATER : IS_EQUAL);

                // null <-> boolean = false <-> boolean
                case TYPE_PAIR(IS_NULL, IS_BOOL):
                        return Z_LVAL_P(op2) ? IS_SMALLER : IS_EQUAL;

                case TYPE_PAIR(IS_BOOL, IS_NULL):
                        return Z_LVAL_P(op1) ? IS_GREATER : IS_EQUAL;

                // null <-> string = "" <-> string
                case TYPE_PAIR(IS_NULL, IS_STRING):
                        return Z_STRLEN_P(op2) > 0 ? IS_SMALLER : IS_EQUAL;

                case TYPE_PAIR(IS_STRING, IS_NULL):
                        return Z_STRLEN_P(op1) > 0 ? IS_GREATER : IS_EQUAL;

                // string <-> long   = (long)string <-> long
                // string <-> double = (double)string <-> double
                case TYPE_PAIR(IS_STRING, IS_LONG):
                        if (strton(Z_STRVAL_P(op1), Z_STRLEN_P(op1), &lval, NULL) == IS_LONG) {
                                return lval < Z_LVAL_P(op2) ? IS_SMALLER : (lval > Z_LVAL_P(op2) ? IS_GREATER : IS_EQUAL);
                        }
                        return IS_NOT_EQUAL;

                case TYPE_PAIR(IS_LONG, IS_STRING):
                        if (strton(Z_STRVAL_P(op2), Z_STRLEN_P(op2), &lval, NULL) == IS_LONG) {
                                return Z_LVAL_P(op1) < lval ? IS_SMALLER : (Z_LVAL_P(op1) > lval ? IS_GREATER : IS_EQUAL);
                        }
                        return IS_NOT_EQUAL;

                case TYPE_PAIR(IS_STRING, IS_DOUBLE):
                        switch (strton(Z_STRVAL_P(op1), Z_STRLEN_P(op1), &lval, &dval)) {
                                case IS_LONG:
                                        dval = (double)lval;
                                case IS_DOUBLE:
                                        return dval < Z_DVAL_P(op2) ? IS_SMALLER : (dval > Z_DVAL_P(op2) ? IS_GREATER : IS_EQUAL);
                        }
                        return IS_NOT_EQUAL;

                case TYPE_PAIR(IS_DOUBLE, IS_STRING):
                        switch (strton(Z_STRVAL_P(op2), Z_STRLEN_P(op2), &lval, &dval)) {
                                case IS_LONG:
                                        dval = (double)lval;
                                case IS_DOUBLE:
                                        return Z_DVAL_P(op1) < dval ? IS_SMALLER : (Z_DVAL_P(op1) > dval ? IS_GREATER : IS_EQUAL);
                        }
                        return IS_NOT_EQUAL;

                // object comparison
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
			return (Z_ARRVAL_P(op1) == Z_ARRVAL_P(op2) ||
				zend_hash_compare(Z_ARRVAL_P(op1), Z_ARRVAL_P(op2), (compare_func_t) hash_is_identical_zval, 1 TSRMLS_CC) == IS_EQUAL);
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
