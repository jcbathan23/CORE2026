<?php

/**
 * Hashes a password using PHP's built-in password_hash()
 * @param string $password The plain text password
 * @return string The hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifies a password against a hash
 * @param string $password The plain text password
 * @param string $hash The hashed password
 * @return bool True if the password matches, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Checks if a password needs to be rehashed
 * @param string $hash The hashed password
 * @return bool True if password needs rehashing, false otherwise
 */
function passwordNeedsRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_DEFAULT);
}
