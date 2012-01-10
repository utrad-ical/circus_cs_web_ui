<?php

/**
 * WebAPI exception base class.
 * @author Yukihiro Ohno <y-ohno@j-mac.co.jp>
 */
class ApiException extends Exception
{
}

/**
 * ApiAuthException represents authentication related errors triggered from API.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class ApiAuthException extends ApiException
{
}

/**
 * ApiSystemException represents critical exception such as DB connection errors.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class ApiSystemException extends ApiException
{
}

/**
 * ApiOperationException represents non-critical exception such as
 * input parameter validation errors.
 * @author Soichiro Miki <smiki-tky@umin.ac.jp>
 */
class ApiOperationException extends ApiException
{
}