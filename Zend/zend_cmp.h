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

#ifndef ZEND_CMP_H
#define ZEND_CMP_H

#define IS_SMALLER   -1
#define IS_EQUAL     0
#define IS_GREATER   1
#define IS_NOT_EQUAL 2

#define TYPE_PAIR(t1, t2)	(((t1) << 4) | (t2))

ZEND_API int zend_cmp_zval(zval *op1, zval *op2 TSRMLS_DC);
ZEND_API int zend_cmp_str(const char *s1, uint len1, const char *s2, uint len2 TSRMLS_DC);
ZEND_API int zend_cmp_ht(HashTable *ht1, HashTable *ht2 TSRMLS_DC);

ZEND_API int zend_is_true_zval(zval *op TSRMLS_DC);
ZEND_API int zend_is_identical_zval(zval *op1, zval *op2 TSRMLS_DC);
ZEND_API int zend_is_identical_str(const char *s1, uint len1, const char *s2, uint len2 TSRMLS_DC);
ZEND_API int zend_is_identical_ht(HashTable *ht1, HashTable *ht2 TSRMLS_DC);
ZEND_API int zend_is_equal_zval(zval *op1, zval *op2 TSRMLS_DC);
ZEND_API int zend_is_smaller_zval(zval *op1, zval *op2 TSRMLS_DC);
ZEND_API int zend_is_smaller_or_equal_zval(zval *op1, zval *op2 TSRMLS_DC);

#endif

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: t
 * End:
 */
