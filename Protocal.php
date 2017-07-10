<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\fastcgi;

/**
 * FastCGI Protocal define in PHP
 *
 * @link https://fastcgi-archives.github.io/FastCGI_Specification.html
 * @link http://www.mit.edu/~yandros/doc/specs/fcgi-spec.html
 * @package panwenbin\fastcgi
 */
class Protocal
{
    /**
     * Listening socket file number
     */
    const LISTENSOCK_FILENO = 0;

    /**
     * Number of bytes in a FCGI_Header.  Future versions of the protocol
     * will not reduce this number.
     */
    const HEADER_LEN = 8;

    /**
     * Value for version component of FCGI_Header
     */
    const VERSION_1 = 1;

    /**
     * Values for type component of FCGI_Header
     */
    const TYPE_BEGIN_REQUEST = 1;
    const TYPE_ABORT_REQUEST = 2;
    const TYPE_END_REQUEST = 3;
    const TYPE_PARAMS = 4;
    const TYPE_STDIN = 5;
    const TYPE_STDOUT = 6;
    const TYPE_STDERR = 7;
    const TYPE_DATA = 8;
    const TYPE_GET_VALUES = 9;
    const TYPE_GET_VALUES_RESULT = 10;
    const TYPE_UNKNOWN_TYPE = 11;
    const TYPE_MAXTYPE = self::TYPE_UNKNOWN_TYPE;

    /**
     * Value for requestId component of FCGI_Header
     */
    const REQUEST_ID_NULL = 0;

    /**
     * Mask for flags component of FCGI_BeginRequestBody
     */
    const FLAG_KEEP_CONN = 1;

    /**
     * Values for role component of FCGI_BeginRequestBody
     */
    const ROLE_RESPONDER = 1; // normally is response
    const ROLE_AUTHORIZER = 2;
    const ROLE_FILTER = 3;

    /**
     * Values for protocolStatus component of FCGI_EndRequestBody
     */
    const STATUS_REQUEST_COMPLETE = 0;
    const STATUS_CANT_MPX_CONN = 1;
    const STATUS_OVERLOADED = 2;
    const STATUS_UNKNOWN_ROLE = 3;

    /**
     * Variable names for FCGI_GET_VALUES / FCGI_GET_VALUES_RESULT records
     */
    const FCGI_MAX_CONNS = "FCGI_MAX_CONNS";
    const FCGI_MAX_REQS = "FCGI_MAX_REQS";
    const FCGI_MPXS_CONNS = "FCGI_MPXS_CONNS";

    /**
     * Pack and unpack Formats
     */
    const PACK_HEADER = 'C2n2C2';
    const UNPACK_HEADER = 'Cversion/Ctype/nrequestId/ncontentLength/CpaddingLength/Creserved';
    const PACK_BEGIN_REQUEST_BODY = 'nC6';
    const PACK_NAME_VALUE_PAIR11 = 'CC';
    const PACK_NAME_VALUE_PAIR14 = 'CN';
    const PACK_NAME_VALUE_PAIR41 = 'NC';
    const PACK_NAME_VALUE_PAIR44 = 'NN';

    /**
     * Build FastCGI request Header
     * @param $version
     * @param $type
     * @param $requestId
     * @param $contentLength
     * @param $paddingLength
     * @param int $reserved
     * @return string
     */
    public static function packRequestHeader($version, $type, $requestId, $contentLength, $paddingLength, $reserved = 0)
    {
        return pack(self::PACK_HEADER, $version, $type, $requestId, $contentLength, $paddingLength, $reserved);
    }

    /**
     * Build FastCGI begin request Body (empty content)
     * @param int $flags is keep connection
     * @return string
     */
    public static function packBeginRequestBody($flags = 0)
    {
        return pack(self::PACK_BEGIN_REQUEST_BODY, self::ROLE_RESPONDER, $flags & self::FLAG_KEEP_CONN, 0, 0, 0, 0, 0);
    }
}