<?php
namespace Net\Protocol;

class ResponseCode
{
    /**
     * 'Server ready - posting allowed' (RFC977)
     */
    const READY_POSTING_ALLOWED = 200;

    /**
     * 'Server ready - no posting allowed' (RFC977)
     */
    const READY_POSTING_PROHIBITED = 201;

    /**
     * 'Slave status noted' (RFC977)
     */
    const SLAVE_RECOGNIZED = 202;

    /**
     * 'Command not recognized' (RFC977)
     */
    const UNKNOWN_COMMAND = 500;

    /**
     * 'Command syntax error' (RFC977)
     */
    const SYNTAX_ERROR = 501;

    /**
     * 'Access restriction or permission denied' (RFC977)
     */
    const NOT_PERMITTED = 502;

    /**
     * 'Program fault - command not performed' (RFC977)
     */
    const NOT_SUPPORTED = 503;

    /**
     * 'Group selected' (RFC977)
     */
    const GROUP_SELECTED = 211;

    /**
     * 'No such news group' (RFC977)
     */
    const NO_SUCH_GROUP = 411;

    /**
     * 'Article retrieved - head and body follow' (RFC977)
     */
    const ARTICLE_FOLLOWS = 220;

    /**
     * 'Article retrieved - head follows' (RFC977)
     */
    const HEAD_FOLLOWS = 221; 

    /**
     * 'Article retrieved - body follows' (RFC977)
     */
    const BODY_FOLLOWS = 222;

    /**
     * 'Article retrieved - request text separately' (RFC977)
     */
    const ARTICLE_SELECTED = 223;

    /**
     * 'List of new newsgroups follows' (Draft)
     */
    const NEW_GROUPS_FOLLOW = 231;

    /**
     * 'No newsgroup has been selected' (RFC977)
     */
    const NO_GROUP_SELECTED = 412;
    
    /**
     * 'No current article has been selected' (RFC977)
     */
    const NO_ARTICLE_SELECTED = 420;

    /**
     * 'No next article in this group' (RFC977)
     */
    const NO_NEXT_ARTICLE = 421;

    /**
     * 'No previous article in this group' (RFC977)
     */
    const NO_PREVIOUS_ARTICLE = 422;
    
    /**
     * 'No such article number in this group' (RFC977)
     */
    const NO_SUCH_ARTICLE_NUMBER = 423;

    /**
     * 'No such article found' (RFC977)
     */
    const NO_SUCH_ARTICLE_ID = 430;

    /**
     * 'Send article to be transferred' (RFC977)
     */
    const TRANSFER_SEND = 335;

    /**
     * 'Article transferred ok' (RFC977)
     */
    const TRANSFER_SUCCESS = 235;

    /**
     * 'Article not wanted - do not send it' (RFC977)
     */
    const TRANSFER_UNWANTED = 435;

    /**
     * 'Transfer failed - try again later' (RFC977)
     */
    const TRANSFER_FAILURE = 436;

    /**
     * 'Article rejected - do not try again' (RFC977)
     */
    const TRANSFER_REJECTED = 437;

    /**
     * 'Send article to be posted' (RFC977)
     */
    const POSTING_SEND = 340;

    /**
     * 'Article posted ok' (RFC977)
     */
    const POSTING_SUCCESS = 240;

    /**
     * 'Posting not allowed' (RFC977)
     */
    const POSTING_PROHIBITED = 440;

    /**
     * 'Posting failed' (RFC977)
     */
    const POSTING_FAILURE = 441;

    /**
     * 'Authorization required for this command' (RFC2980)
     */
    const AUTHORIZATION_REQUIRED = 450;

    /**
     * 'Continue with authorization sequence' (RFC2980)
     */
    const AUTHORIZATION_CONTINUE = 350;

    /**
     * 'Authorization accepted' (RFC2980)
     */
    const AUTHORIZATION_ACCEPTED = 250;

    /**
     * 'Authorization rejected' (RFC2980)
     */
    const AUTHORIZATION_REJECTED = 452;

    /**
     * 'Authentication required' (RFC2980)
     */
    const AUTHENTICATION_REQUIRED = 480;

    /**
     * 'More authentication information required' (RFC2980)
     */
    const AUTHENTICATION_CONTINUE = 381;

    /**
     * 'Authentication accepted' (RFC2980)
     */
    const AUTHENTICATION_ACCEPTED = 281;

    /**
     * 'Authentication rejected' (RFC2980)
     */
    const AUTHENTICATION_REJECTED = 482;

    /**
     * 'Service discontinued' (RFC977)
     */
    const DISCONNECTING_FORCED = 400;

    /**
     * 'Closing connection - goodbye!' (RFC977)
     */
    const DISCONNECTING_REQUESTED = 205;

    /**
     * 'Information follows' (Draft)
     */
    const GROUPS_FOLLOW = 215;

    /**
     * 'Capabilities list follows' (Draft)
     */
    const CAPABILITIES_FOLLOW = 101;
}