<?php
/**
 * Dazlo Framework Copyright (c) 2012 D. Dante Lorenso.  All Rights Reserved.
 * This source file is subject to the new BSD license that is bundled with
 * this package in the file LICENSE.txt.  It is also available through the
 * world-wide web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 */
namespace Daz\Crypt;

class Guid
{
    /**
     * Like "uuid" function, but in a more readable format, and without hyphens.
     * @return  string  A UUID, made up of 32 hex digits.
     */
    public static function guid()
    {
        return call_user_func_array(
            'sprintf',
            array(
                // format
                '%04x%04x' . '%04x' . '%03x4' . '%04x' . '%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                // 16 bits for "time_mid"
                mt_rand(0, 65535),
                // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
                mt_rand(0, 4095),
                // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
                // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
                // 8 bits for "clk_seq_low"
                bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
                // 48 bits for "node"
                mt_rand(0, 65535),
                mt_rand(0, 65535),
                mt_rand(0, 65535)
            )
        );
    }

    /**
     * Generates a Universally Unique IDentifier, version 4.
     * RFC 4122 (http://www.ietf.org/rfc/rfc4122.txt) defines a special type of
     * Globally Unique IDentifiers (GUID), as well as several methods for
     * producing them. One such method, described in section 4.4, is based on
     * truly random or pseudo-random number generators, and is therefore
     * implementable in a language like PHP.
     * We choose to produce pseudo-random numbers with the Mersenne Twister, and
     * to always limit single generated numbers to 16 bits (ie. the decimal
     * value 65535). That is because, even on 32-bit systems, PHP's RAND_MAX
     * will often be the maximum *signed* value, with only the equivalent of 31
     * significant bits. Producing two 16-bit random numbers to make up a 32-bit
     * one is less efficient, but guarantees that all 32 bits are random.
     * The algorithm for version 4 UUIDs (ie. those based on random number
     * generators) states that all 128 bits separated into the various fields
     * (32 bits, 16 bits, 16 bits, 8 bits and 8 bits, 48 bits) should be random,
     * except : (a) the version number should be the last 4 bits in the 3rd
     * field, and (b) bits 6 and 7 of the 4th field should be 01. We try to
     * conform to that definition as efficiently as possible, generating smaller
     * values where possible, and minimizing the number of base conversions.
     * @copyright Copyright (c) CFD Labs, 2006. This function may be used freely
     * for any purpose ; it is distributed without any form of warranty
     * whatsoever.
     * @author David Holmes <dholmes@cfdsoftware.net>
     * @return  string  A UUID, made up of 32 hex digits and 4 hyphens.
     */
    public static function uuid()
    {
        // The field names refer to RFC 4122 section 4.1.2
        return sprintf(
            '%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
            mt_rand(0, 65535),
            mt_rand(0, 65535), // 32 bits for "time_low"
            mt_rand(0, 65535), // 16 bits for "time_mid"
            mt_rand(0, 4095), // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
            // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
            // 8 bits for "clk_seq_low"
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535) // 48 bits for "node"
        );
    }

}