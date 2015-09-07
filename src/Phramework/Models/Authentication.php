<?php

namespace Phramework\Models;

use Phramework\API;
use Phramework\Models\Database;

/**
 * Authentication related functions
 *
 * Implements authentication using HTTP\s BASIC AUTHENTICATION
 * This class should be extended if Database structure differs
 * @author Spafaridis Xenophon <nohponex@gmail.com>
 * @since 0
 * @package Phramework
 * @subpackage API
 * @category models
 */
class Authentication
{
    /**
     * Check user's authentication, using data provided as BASIC AUTHENTICATION HEADERS
     * @todo Implement additional methods
     * @return array|FALSE Returns false on error or the user object on success
     */
    public static function check()
    {
        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            return false;
        }

        //Validate authentication credentials
        \Phramework\Models\Validate::email($_SERVER['PHP_AUTH_USER']);

        $auth = self::authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

        return $auth;
    }

    /**
     * Autheticate a user, using user's email and password
     * @todo Implement validate
     * @param string $email
     * @param string $password
     * @return array|FALSE Returns false on error or the user object on success
     * @throws \Phramework\Exceptions\Permission
     */
    public static function authenticate($email, $password)
    {

        //Select using user's email
        $auth = Database::executeAndFetch(
            'SELECT "id", "username", "email", "password", "language_code", "usergroup", "disabled"'
            . 'FROM "user" WHERE LOWER("email") = ? LIMIT 1',
            [strtolower($email)]
        );

        //Check if user exists
        if (!$auth) {
            return false;
        }
        //Check if user is disabled
        if ($auth['disabled']) {
            throw new \Phramework\Exceptions\Permission(API::getTranslated('disabled_account_exception'));
        }

        //Verify password hash
        if (password_verify($password, $auth['password'])) {
            //Force corrent types
            $auth['id'] = intval($auth['id']);

            //Return without the password field
            return \Phramework\Models\Filter::outEntry(
                $auth,
                ['password', 'disabled', 'validated']
            );
        } else {
            //In case of incorrect password
            return false;
        }
    }
}